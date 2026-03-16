<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_medecin extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        is_medecin();
    }

    public function index()
    {
        $user_id = $this->session->userdata('user_id');
        // Récupérer les infos de l'utilisateur
        $data['user'] = $this->Model->readOne('users', ['id' => $user_id]);
        // Récupérer les infos du médecin (table medecins)
        $data['medecin'] = $this->Model->readOne('medecins', ['user_id' => $user_id]);
        
        $this->load->view('Settings_medecin_View', $data);
    }

    public function update_info()
    {
        $user_id = $this->session->userdata('user_id');
        
        $this->form_validation->set_rules('nom', 'Nom', 'required');
        $this->form_validation->set_rules('prenom', 'Prénom', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'required');
        $this->form_validation->set_rules('specialite', 'Spécialité', 'required');
        $this->form_validation->set_rules('numero_licence', 'Numéro de licence', 'required');
        $this->form_validation->set_rules('annees_experience', 'Années d\'expérience', 'numeric');
        $this->form_validation->set_rules('langues_parlees', 'Langues parlées', 'trim');
        $this->form_validation->set_rules('honoraires_consultation', 'Honoraires', 'numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('info'));
            return;
        }

        // Vérifier si l'email existe déjà pour un autre utilisateur
        $existing = $this->Model->readOne('users', ['email' => $this->input->post('email'), 'id !=' => $user_id]);
        if ($existing) {
            $this->session->set_flashdata('error', 'Cet email est déjà utilisé par un autre compte.');
            redirect(base_url('info'));
            return;
        }

        // Mise à jour table users
        $user_data = [
            'nom' => $this->input->post('nom'),
            'prenom' => $this->input->post('prenom'),
            'email' => $this->input->post('email'),
            'telephone' => $this->input->post('telephone'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Si upload de photo
        if (!empty($_FILES['photo']['name'])) {
            $photo = $this->upload_image($_FILES['photo']['tmp_name'], $_FILES['photo']['name']);
            if ($photo !== NULL) {
                // Supprimer l'ancienne photo si elle n'est pas la default
                $old = $this->Model->readOne('users', ['id' => $user_id]);
                if ($old && $old['photo'] != 'default-avatar.png' && file_exists(FCPATH . 'attachments/Users/' . $old['photo'])) {
                    unlink(FCPATH . 'attachments/Users/' . $old['photo']);
                }
                $user_data['photo'] = $photo;
            } else {
                $this->session->set_flashdata('error', 'Format de photo non valide.');
                redirect(base_url('info'));
                return;
            }
        }

        $this->Model->update('users', ['id' => $user_id], $user_data);

        // Mise à jour table medecins
        $medecin_data = [
            'specialite' => $this->input->post('specialite'),
            'numero_licence' => $this->input->post('numero_licence'),
            'annees_experience' => $this->input->post('annees_experience'),
            'langues_parlees' => $this->input->post('langues_parlees'),
            'honoraires_consultation' => $this->input->post('honoraires_consultation'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->Model->update('medecins', ['user_id' => $user_id], $medecin_data);

        // Mettre à jour la session
        $this->session->set_userdata([
            'username' => $user_data['prenom'] . ' ' . $user_data['nom'],
            'email' => $user_data['email'],
            'photo' => $user_data['photo'] ?? $old['photo'] ?? 'default-avatar.png'
        ]);

        $this->session->set_flashdata('success', 'Informations mises à jour avec succès.');
        redirect(base_url('info'));
    }

    public function change_password()
    {
        $user_id = $this->session->userdata('user_id');

        $this->form_validation->set_rules('current_password', 'Mot de passe actuel', 'required');
        $this->form_validation->set_rules('new_password', 'Nouveau mot de passe', 'required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirmation', 'required|matches[new_password]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('info'));
            return;
        }

        $user = $this->Model->readOne('users', ['id' => $user_id]);
        if (!password_verify($this->input->post('current_password'), $user['password'])) {
            $this->session->set_flashdata('error', 'Le mot de passe actuel est incorrect.');
            redirect(base_url('info'));
            return;
        }

        $new_hash = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
        $this->Model->update('users', ['id' => $user_id], ['password' => $new_hash]);

        $this->session->set_flashdata('success', 'Mot de passe changé avec succès.');
        redirect(base_url('info'));
    }

    private function upload_image($tmp_name, $name)
    {
        $ref_folder = FCPATH . 'attachments/Users/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        if (move_uploaded_file($tmp_name, $ref_folder . $fichier . "." . $file_extension)) {
            return $fichier . "." . $file_extension;
        }
        return NULL;
    }
}