<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Equipe extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer les membres de l'équipe
        $data['membres'] = $this->Model->read('equipe', [], 'ordre_affichage', 'ASC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('Equipe_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_admin = $this->input->post('est_admin');
        
        $status = ($est_admin == 1) ? 0 : 1;
        $rsp = $this->Model->update('equipe', ['id_membre' => $id], ['est_admin' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut administrateur mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Equipe'));    
    }

    function MembreDetail($membreDetail){
        $id = $membreDetail;
        $data['detail'] = $this->Model->readOne('equipe', ['id_membre' => $id[0]]);
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('MembreDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('nom', 'Nom', 'required');
        $this->form_validation->set_rules('prenom', 'Prénom', 'required');
        $this->form_validation->set_rules('poste', 'Poste', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Equipe'));
            return;
        }

        $nom = $this->input->post('nom');
        $prenom = $this->input->post('prenom');
        $poste = $this->input->post('poste');
        $biographie = $this->input->post('biographie');
        $email = $this->input->post('email');
        $linkedin = $this->input->post('linkedin');
        $ordre_affichage = $this->input->post('ordre_affichage') ?: 0;
        $est_admin = $this->input->post('est_admin') ? 1 : 0;
        $specialite = $this->input->post('specialite');
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        // Upload photo si fournie
        $photo_url = NULL;
        if (!empty($_FILES['photo']['name'])) {
            $photo_url = $this->upload_image($_FILES['photo']['tmp_name'], $_FILES['photo']['name']);
            if ($photo_url === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Equipe'));
                return;
            }
        } elseif (!empty($this->input->post('photo_url'))) {
            $photo_url = $this->input->post('photo_url');
        }

        $data = array(
            'nom' => $nom,
            'prenom' => $prenom,
            'poste' => $poste,
            'biographie' => $biographie,
            'photo_url' => $photo_url,
            'email' => $email,
            'linkedin' => $linkedin,
            'ordre_affichage' => $ordre_affichage,
            'est_admin' => $est_admin,
            'specialite' => $specialite,
            'id_page_associee' => $id_page_associee
        );
        
        $rsp = $this->Model->create('equipe', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Membre de l\'équipe créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Equipe'));
    }

    function Update(){
        $id = $this->input->post('id_membre');
        
        // Validation
        $this->form_validation->set_rules('nom', 'Nom', 'required');
        $this->form_validation->set_rules('prenom', 'Prénom', 'required');
        $this->form_validation->set_rules('poste', 'Poste', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Equipe'));
            return;
        }

        $nom = $this->input->post('nom');
        $prenom = $this->input->post('prenom');
        $poste = $this->input->post('poste');
        $biographie = $this->input->post('biographie');
        $email = $this->input->post('email');
        $linkedin = $this->input->post('linkedin');
        $ordre_affichage = $this->input->post('ordre_affichage') ?: 0;
        $est_admin = $this->input->post('est_admin') ? 1 : 0;
        $specialite = $this->input->post('specialite');
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'nom' => $nom,
            'prenom' => $prenom,
            'poste' => $poste,
            'biographie' => $biographie,
            'email' => $email,
            'linkedin' => $linkedin,
            'ordre_affichage' => $ordre_affichage,
            'est_admin' => $est_admin,
            'specialite' => $specialite,
            'id_page_associee' => $id_page_associee
        );

        // Gestion de la photo
        if (!empty($_FILES['photo']['name'])) {
            $new_photo = $this->upload_image($_FILES['photo']['tmp_name'], $_FILES['photo']['name']);
            if ($new_photo === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Equipe'));
                return;
            }
            
            // Supprimer l'ancienne photo si existe
            $membre = $this->Model->readOne('equipe', ['id_membre' => $id]);
            if ($membre && !empty($membre['photo_url']) && file_exists(FCPATH . 'attachments/Equipe/' . basename($membre['photo_url']))) {
                unlink(FCPATH . 'attachments/Equipe/' . basename($membre['photo_url']));
            }
            
            $data['photo_url'] = $new_photo;
        } elseif (!empty($this->input->post('photo_url'))) {
            $data['photo_url'] = $this->input->post('photo_url');
        }

        $rsp = $this->Model->update('equipe', ['id_membre' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Membre de l\'équipe mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Equipe'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer le membre pour supprimer sa photo
        $membre = $this->Model->readOne('equipe', ['id_membre' => $id]);
        
        $rsp = $this->Model->delete('equipe', ['id_membre' => $id]);

        if ($rsp) {
            // Supprimer la photo physique si existe
            if ($membre && !empty($membre['photo_url']) && file_exists(FCPATH . 'attachments/Equipe/' . basename($membre['photo_url']))) {
                unlink(FCPATH . 'attachments/Equipe/' . basename($membre['photo_url']));
            }
            $this->session->set_flashdata('success', 'Membre de l\'équipe supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Equipe'));
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Equipe/';
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
        return '/attachments/Equipe/' . $fichier . "." . $file_extension;
    }
}