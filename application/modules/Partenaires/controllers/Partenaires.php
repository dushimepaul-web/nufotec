<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partenaires extends MY_Controller {

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
        $data['partenaires'] = $this->Model->read('partenaires', ['deleted_at' => NULL], 'id_partenaire', 'DESC');
        $this->load->view('Partenaires_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        
        $status = ($est_actif == 1) ? 0 : 1;
        $rsp = $this->Model->update('partenaires', ['id_partenaire' => $id], ['est_actif' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Partenaires'));    
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('nom', 'Nom', 'required');
        $this->form_validation->set_rules('type_partenaire', 'Type de partenaire', 'required');
        $this->form_validation->set_rules('niveau_partenariat', 'Niveau de partenariat', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Partenaires'));
            return;
        }

        $nom = $this->input->post('nom');
        $type_partenaire = $this->input->post('type_partenaire');
        $description = $this->input->post('description') ?: NULL;
        $pays = $this->input->post('pays') ?: NULL;
        $site_web = $this->input->post('site_web') ?: NULL;
        $niveau_partenariat = $this->input->post('niveau_partenariat');
        $date_debut = $this->input->post('date_debut') ?: NULL;
        $est_actif = $this->input->post('est_actif') ? 1 : 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        // Upload logo si fourni
        $logo_url = NULL;
        if (!empty($_FILES['logo']['name'])) {
            $logo_url = $this->upload_image($_FILES['logo']['tmp_name'], $_FILES['logo']['name'], 'partenaires');
            if ($logo_url === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Partenaires'));
                return;
            }
        }

        $data = array(
            'nom' => $nom,
            'type_partenaire' => $type_partenaire,
            'description' => $description,
            'pays' => $pays,
            'site_web' => $site_web,
            'logo_url' => $logo_url,
            'niveau_partenariat' => $niveau_partenariat,
            'date_debut' => $date_debut,
            'est_actif' => $est_actif,
            'id_page_associee' => $id_page_associee,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('partenaires', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Partenaire créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création du partenaire.');
        }
        redirect(base_url('Partenaires'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('nom', 'Nom', 'required');
        $this->form_validation->set_rules('type_partenaire', 'Type de partenaire', 'required');
        $this->form_validation->set_rules('niveau_partenariat', 'Niveau de partenariat', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Partenaires'));
            return;
        }

        $nom = $this->input->post('nom');
        $type_partenaire = $this->input->post('type_partenaire');
        $description = $this->input->post('description') ?: NULL;
        $pays = $this->input->post('pays') ?: NULL;
        $site_web = $this->input->post('site_web') ?: NULL;
        $niveau_partenariat = $this->input->post('niveau_partenariat');
        $date_debut = $this->input->post('date_debut') ?: NULL;
        $est_actif = $this->input->post('est_actif') ? 1 : 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'nom' => $nom,
            'type_partenaire' => $type_partenaire,
            'description' => $description,
            'pays' => $pays,
            'site_web' => $site_web,
            'niveau_partenariat' => $niveau_partenariat,
            'date_debut' => $date_debut,
            'est_actif' => $est_actif,
            'id_page_associee' => $id_page_associee,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Upload nouveau logo si fourni
        if (!empty($_FILES['logo']['name'])) {
            $new_logo = $this->upload_image($_FILES['logo']['tmp_name'], $_FILES['logo']['name'], 'partenaires');
            if ($new_logo === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Partenaires'));
                return;
            }
            
            // Supprimer l'ancien logo si existe
            $partenaire = $this->Model->readOne('partenaires', ['id_partenaire' => $id]);
            if ($partenaire && !empty($partenaire['logo_url']) && file_exists(FCPATH . $partenaire['logo_url'])) {
                unlink(FCPATH . $partenaire['logo_url']);
            }
            
            $data['logo_url'] = $new_logo;
        }

        $rsp = $this->Model->update('partenaires', ['id_partenaire' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Partenaire mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Partenaires'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer le partenaire pour supprimer son logo
        $partenaire = $this->Model->readOne('partenaires', ['id_partenaire' => $id]);
        
        // Soft delete
        $rsp = $this->Model->update('partenaires', ['id_partenaire' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            // Supprimer le logo physique si existe
            if ($partenaire && !empty($partenaire['logo_url']) && file_exists(FCPATH . $partenaire['logo_url'])) {
                unlink(FCPATH . $partenaire['logo_url']);
            }
            $this->session->set_flashdata('success', 'Partenaire supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Partenaires'));
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ, $folder = 'partenaires')
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
