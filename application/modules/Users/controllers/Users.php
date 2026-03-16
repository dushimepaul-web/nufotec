<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer uniquement les utilisateurs non supprimés (soft delete)
        $data['users'] = $this->Model->read('users', ['deleted_at' => NULL], 'id','DESC');
        $data['roles'] = $this->Model->read('roles', null, 'id');
        $this->load->view('Users_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        $status = ($is_active == 1) ? 0 : 1;
        $rsp = $this->Model->update('users', ['id' => $id], ['is_active' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('users'));    
    }

    function UserDetail($userDetail){
        $id = explode('_', $userDetail);
        $data['detail'] = $this->Model->readOne('users', ['id' => $id[0]]);
        $data['roles'] = $this->Model->read('roles', null, 'id');
        $this->load->view('UserDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Mot de passe', 'required|min_length[8]');
        $this->form_validation->set_rules('nom', 'Nom', 'required');
        $this->form_validation->set_rules('prenom', 'Prénom', 'required');
        $this->form_validation->set_rules('role_id', 'Rôle', 'required');
        $this->form_validation->set_rules('type_utilisateur', 'Type d\'utilisateur', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('users'));
            return;
        }

        $uuid = $this->generate_uuid();
        $email = $this->input->post('email');
        $password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        $nom = $this->input->post('nom');
        $prenom = $this->input->post('prenom');
        $telephone = $this->input->post('telephone');
        $date_naissance = $this->input->post('date_naissance') ?: NULL;
        $genre = $this->input->post('genre') ?: NULL;
        $role_id = $this->input->post('role_id');
        $type_utilisateur = $this->input->post('type_utilisateur');
        $nom_entreprise = $this->input->post('nom_entreprise') ?: NULL;
        $secteur_activite = $this->input->post('secteur_activite') ?: NULL;
        $numero_registre_commerce = $this->input->post('numero_registre_commerce') ?: NULL;
        $interet_investissement = $this->input->post('interet_investissement') ?: NULL;
        $est_verifie = $this->input->post('est_verifie') ? 1 : 0;

        // Upload photo si fournie
        $photo = 'default-avatar.png';
        if (!empty($_FILES['photo']['name'])) {
            $photo = $this->upload_image($_FILES['photo']['tmp_name'], $_FILES['photo']['name']);
            if ($photo === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('users'));
                return;
            }
        }

        $data = array(
            'uuid' => $uuid,
            'email' => $email,
            'password' => $password,
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'date_naissance' => $date_naissance,
            'genre' => $genre,
            'photo' => $photo,
            'role_id' => $role_id,
            'type_utilisateur' => $type_utilisateur,
            'nom_entreprise' => $nom_entreprise,
            'secteur_activite' => $secteur_activite,
            'numero_registre_commerce' => $numero_registre_commerce,
            'interet_investissement' => $interet_investissement,
            'est_verifie' => $est_verifie,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('users', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Utilisateur créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de l\'utilisateur.');
        }
        redirect(base_url('users'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('nom', 'Nom', 'required');
        $this->form_validation->set_rules('prenom', 'Prénom', 'required');
        $this->form_validation->set_rules('role_id', 'Rôle', 'required');
        $this->form_validation->set_rules('type_utilisateur', 'Type d\'utilisateur', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('users'));
            return;
        }

        $email = $this->input->post('email');
        $nom = $this->input->post('nom');
        $prenom = $this->input->post('prenom');
        $telephone = $this->input->post('telephone');
        $date_naissance = $this->input->post('date_naissance') ?: NULL;
        $genre = $this->input->post('genre') ?: NULL;
        $role_id = $this->input->post('role_id');
        $type_utilisateur = $this->input->post('type_utilisateur');
        $nom_entreprise = $this->input->post('nom_entreprise') ?: NULL;
        $secteur_activite = $this->input->post('secteur_activite') ?: NULL;
        $numero_registre_commerce = $this->input->post('numero_registre_commerce') ?: NULL;
        $interet_investissement = $this->input->post('interet_investissement') ?: NULL;
        $is_active = $this->input->post('is_active') ? 1 : 0;
        $est_verifie = $this->input->post('est_verifie') ? 1 : 0;

        $data = array(
            'email' => $email,
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'date_naissance' => $date_naissance,
            'genre' => $genre,
            'role_id' => $role_id,
            'type_utilisateur' => $type_utilisateur,
            'nom_entreprise' => $nom_entreprise,
            'secteur_activite' => $secteur_activite,
            'numero_registre_commerce' => $numero_registre_commerce,
            'interet_investissement' => $interet_investissement,
            'is_active' => $is_active,
            'est_verifie' => $est_verifie,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Gestion email vérifié
        if ($this->input->post('email_verified')) {
            $data['email_verified_at'] = date('Y-m-d H:i:s');
        } else {
            $data['email_verified_at'] = NULL;
        }

        // Mise à jour du mot de passe si fourni
        if (!empty($this->input->post('password'))) {
            $data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        // Upload nouvelle photo si fournie
        if (!empty($_FILES['photo']['name'])) {
            $new_photo = $this->upload_image($_FILES['photo']['tmp_name'], $_FILES['photo']['name']);
            if ($new_photo === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('users'));
                return;
            }
            
            // Supprimer l'ancienne photo si ce n'est pas l'avatar par défaut
            $user = $this->Model->readOne('users', ['id' => $id]);
            if ($user && $user['photo'] != 'default-avatar.png' && file_exists(FCPATH . 'attachments/Users/' . $user['photo'])) {
                unlink(FCPATH . 'attachments/Users/' . $user['photo']);
            }
            
            $data['photo'] = $new_photo;
        }

        $rsp = $this->Model->update('users', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Utilisateur mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('users'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer l'utilisateur pour supprimer sa photo
        $user = $this->Model->readOne('users', ['id' => $id]);
        
        // Soft delete
        $rsp = $this->Model->update('users', ['id' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            // Supprimer la photo physique si ce n'est pas l'avatar par défaut
            if ($user && $user['photo'] != 'default-avatar.png' && file_exists(FCPATH . 'attachments/Users/' . $user['photo'])) {
                unlink(FCPATH . 'attachments/Users/' . $user['photo']);
            }
            $this->session->set_flashdata('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('users'));
    }

    // Génération UUID v4
    private function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Users/';
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








