<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil_user extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->helper(['url', 'form', 'security']);
        
        // Vérifier si l'utilisateur est connecté
        if (!$this->session->userdata('logged_in')) {
            redirect('Admin');
        }
    }

    /**
     * Mise à jour du profil utilisateur
     */
    public function update_profile() {
        // Vérifier la méthode HTTP
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Méthode non autorisée', 405);
        }

        // Récupérer l'ID utilisateur de la session
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            $this->session->set_flashdata('error', 'Session expirée. Veuillez vous reconnecter.');
            redirect('Admin');
        }

        // Récupérer les données actuelles de l'utilisateur
        $current_user = $this->User_model->get_user_by_id($user_id);
        if (!$current_user) {
            $this->session->set_flashdata('error', 'Utilisateur non trouvé.');
            redirect('Dashboard');
        }

        // Règles de validation
        $this->form_validation->set_rules('prenom', 'Prénom', 'required|trim|min_length[2]|max_length[50]');
        $this->form_validation->set_rules('nom', 'Nom', 'required|trim|min_length[2]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[100]');
        $this->form_validation->set_rules('username', 'Nom d\'utilisateur', 'trim|min_length[3]|max_length[50]');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'trim|max_length[20]');
        
        // Validation conditionnelle pour les champs entreprise
        if (in_array($current_user['type_utilisateur'], ['entreprise', 'investisseur', 'broker'])) {
            $this->form_validation->set_rules('nom_entreprise', 'Nom de l\'entreprise', 'trim|max_length[100]');
            $this->form_validation->set_rules('secteur_activite', 'Secteur d\'activité', 'trim|max_length[100]');
            $this->form_validation->set_rules('numero_registre_commerce', 'Numéro de registre', 'trim|max_length[50]');
            
            if ($current_user['type_utilisateur'] == 'investisseur') {
                $this->form_validation->set_rules('interet_investissement', 'Intérêt d\'investissement', 'trim|numeric');
            }
        }

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER'] ?? 'Dashboard');
        }

        // Préparer les données à mettre à jour
        $update_data = [
            'prenom' => $this->input->post('prenom', TRUE),
            'nom' => $this->input->post('nom', TRUE),
            'email' => $this->input->post('email', TRUE),
            'telephone' => $this->input->post('telephone', TRUE),
            'genre' => $this->input->post('genre', TRUE) ?: NULL,
            'date_naissance' => $this->input->post('date_naissance', TRUE) ?: NULL,
        ];

        // Champs spécifiques selon le type d'utilisateur
        if (in_array($current_user['type_utilisateur'], ['entreprise', 'investisseur', 'broker'])) {
            $update_data['nom_entreprise'] = $this->input->post('nom_entreprise', TRUE) ?: NULL;
            $update_data['secteur_activite'] = $this->input->post('secteur_activite', TRUE) ?: NULL;
            $update_data['numero_registre_commerce'] = $this->input->post('numero_registre_commerce', TRUE) ?: NULL;
            
            if ($current_user['type_utilisateur'] == 'investisseur') {
                $update_data['interet_investissement'] = $this->input->post('interet_investissement', TRUE) ?: NULL;
            }
        }

        // Gestion de la photo de profil
        if (!empty($_FILES['photo']['name'])) {
            $upload_result = $this->_upload_photo($user_id);
            if ($upload_result['success']) {
                $update_data['photo'] = $upload_result['file_name'];
                
                // Supprimer l'ancienne photo si différente de default-avatar.png
                if (!empty($current_user['photo']) && $current_user['photo'] != 'default-avatar.png') {
                    $old_photo_path = FCPATH . 'attachments/users/' . $current_user['photo'];
                    if (file_exists($old_photo_path)) {
                        unlink($old_photo_path);
                    }
                }
            } else {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect($_SERVER['HTTP_REFERER'] ?? 'Dashboard');
            }
        }

        // Gestion du changement de mot de passe
        $current_password = $this->input->post('current_password');
        $new_password = $this->input->post('new_password');
        $confirm_password = $this->input->post('confirm_password');

        if (!empty($new_password)) {
            // Vérifier le mot de passe actuel
            if (empty($current_password)) {
                $this->session->set_flashdata('error', 'Veuillez saisir votre mot de passe actuel.');
                redirect($_SERVER['HTTP_REFERER'] ?? 'Dashboard');
            }

            if (!password_verify($current_password, $current_user['password'])) {
                $this->session->set_flashdata('error', 'Mot de passe actuel incorrect.');
                redirect($_SERVER['HTTP_REFERER'] ?? 'Dashboard');
            }

            if ($new_password !== $confirm_password) {
                $this->session->set_flashdata('error', 'Les nouveaux mots de passe ne correspondent pas.');
                redirect($_SERVER['HTTP_REFERER'] ?? 'Dashboard');
            }

            if (strlen($new_password) < 8) {
                $this->session->set_flashdata('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
                redirect($_SERVER['HTTP_REFERER'] ?? 'Dashboard');
            }

            // Hasher le nouveau mot de passe
            $update_data['password'] = password_hash($new_password, PASSWORD_BCRYPT);
        }

        // Mise à jour en base de données
        $result = $this->User_model->update_user($user_id, $update_data);

        if ($result) {
            // Mettre à jour les données de session
            $this->_refresh_session_data($user_id);
            
            $this->session->set_flashdata('success', 'Votre profil a été mis à jour avec succès.');
            
            // Redirection avec paramètre pour afficher le toast
            redirect($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] . '?profile_updated=1' : 'Admin?profile_updated=1');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du profil.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'Dashboard');
        }
    }

    /**
     * Upload de la photo de profil
     */
    private function _upload_photo($user_id) {
        $config['upload_path'] = FCPATH . 'attachments/users/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
        $config['max_size'] = 2048; // 2MB
        $config['file_name'] = date('YmdHis') . '_' . uniqid() . '_' . $user_id;
        $config['overwrite'] = FALSE;
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('photo')) {
            return [
                'success' => FALSE,
                'message' => $this->upload->display_errors('', '')
            ];
        }

        $upload_data = $this->upload->data();
        
        // Optionnel : Redimensionner l'image
        $this->_resize_image($upload_data['full_path']);
        
        return [
            'success' => TRUE,
            'file_name' => $upload_data['file_name']
        ];
    }

    /**
     * Redimensionner l'image uploadée
     */
    private function _resize_image($path) {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $path;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 400;
        $config['height'] = 400;
        $config['quality'] = '90%';

        $this->load->library('image_lib', $config);
        $this->image_lib->resize();
        $this->image_lib->clear();
    }

    /**
     * Rafraîchir les données de session après mise à jour
     */
    private function _refresh_session_data($user_id) {
        $user = $this->User_model->get_user_by_id($user_id);
        
        if ($user) {
            // Récupérer le nom du rôle
            $role = $this->User_model->get_role_by_id($user['role_id']);
            
            $session_data = [
                'user_id'          => $user['id'],
                'uuid'             => $user['uuid'],
                'email'            => $user['email'],
                'nom'              => $user['nom'],
                'prenom'           => $user['prenom'],
                'username'         => $user['username'] ?? ($user['prenom'] . ' ' . $user['nom']),
                'photo'            => $user['photo'],
                'type_utilisateur' => $user['type_utilisateur'],
                'role'             => $role['nom'] ?? 'Utilisateur',
                'role_id'          => (int)$user['role_id'],
                'role_slug'        => $role['slug'] ?? 'user',
                'logged_in'        => TRUE,
                'last_regenerate'  => time()
            ];
            
            $this->session->set_userdata($session_data);
        }
    }

   
}