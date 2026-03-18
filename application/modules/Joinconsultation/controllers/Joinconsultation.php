<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Joinconsultation extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('JoinConsultation_model');
        $this->load->library('form_validation');
        $this->load->helper('url'); // Assurez-vous que le helper URL est chargé
    }

    // URL : /Joinconsultation/index?room=ABC123&user=12
    public function index() {
        $room_id = $this->input->get('room');
        $user_id = $this->input->get('user');

        if (empty($room_id) || empty($user_id)) {
            show_404();
        }

        // Vérifier que la consultation existe et que l'utilisateur est bien participant
        $consultation = $this->JoinConsultation_model->get_by_room_and_user($room_id, $user_id);
        if (!$consultation) {
            show_error('Lien invalide ou vous n\'êtes pas autorisé à rejoindre cette consultation.', 403);
        }

        // Si déjà connecté, on peut sauter l'étape du mot de passe
        if ($this->session->userdata('logged_in') && $this->session->userdata('user_id') == $user_id) {
            redirect("Joinconsultation/room/$room_id/$user_id");
        }

        // Afficher le formulaire de mot de passe
        $data['room_id'] = $room_id;
        $data['user_id'] = $user_id;
        $data['consultation'] = $consultation;
        $this->load->view('join_password', $data);
    }

    // Traitement du mot de passe
    public function verify() {
        $this->form_validation->set_rules('password', 'Mot de passe', 'required');
        $this->form_validation->set_rules('user_id', 'Utilisateur', 'required|integer');
        $this->form_validation->set_rules('room_id', 'Salle', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("Joinconsultation/index?room=" . $this->input->post('room_id') . "&user=" . $this->input->post('user_id'));
        } else {
            $user_id = $this->input->post('user_id');
            $password = $this->input->post('password');
            $room_id = $this->input->post('room_id');

            // CORRECTION: Utiliser le modèle User au lieu de $this->Model
        
            $user = $this->Model->get_user_by_id($user_id);

            if ($user && password_verify($password, $user['password'])) {
                // Connexion réussie, créer session
                $session_data = array(
                    'user_id'   => $user['id'],
                    'email'     => $user['email'],
                    'nom'       => $user['nom'],
                    'prenom'    => $user['prenom'],
                    'photo'     => $user['photo'],
                    'logged_in' => TRUE
                );
                $this->session->set_userdata($session_data);
                redirect("Joinconsultation/room/$room_id/$user_id");
            } else {
                $this->session->set_flashdata('error', 'Mot de passe incorrect');
                redirect("Joinconsultation/index?room=$room_id&user=$user_id");
            }
        }
    }

    public function room($room_id, $user_id) {
    // Vérifier que l'utilisateur est bien connecté et correspond
    if (!$this->session->userdata('logged_in') || $this->session->userdata('user_id') != $user_id) {
        redirect("Joinconsultation/index?room=$room_id&user=$user_id");
    }

    // Récupérer les informations de la consultation avec les deux participants
    $consultation = $this->JoinConsultation_model->get_full_consultation($room_id);
    if (!$consultation) {
        show_404();
    }

    // Gestion robuste des types (objet vs tableau)
    $current_user_id = (int) $user_id;
    $patient_user_id = is_object($consultation) ? $consultation->patient_user_id : $consultation['patient_user_id'];
    $medecin_user_id = is_object($consultation) ? $consultation->medecin_user_id : $consultation['medecin_user_id'];

    // Déterminer qui est l'autre participant et quel est le rôle du courant
    if ($patient_user_id == $current_user_id) {
        $other_user_id = $medecin_user_id;
        $current_role = 'patient';
    } elseif ($medecin_user_id == $current_user_id) {
        $other_user_id = $patient_user_id;
        $current_role = 'medecin';
    } else {
        show_error('Vous n\'êtes pas autorisé à accéder à cette consultation.', 403);
    }

    
    $other_user = $this->Model->get_user_by_id($other_user_id);

    if (!$other_user) {
        show_error('L\'autre participant est introuvable.', 404);
    }

    // S'assurer que current_user a la clé 'id'
    $current_user_data = $this->session->userdata();
    $current_user_data['id'] = $current_user_data['user_id'];

    // DEBUG - Ajoutez ce log pour vérifier
    log_message('debug', '=== CONSULTATION DEBUG ===');
    log_message('debug', 'Room ID: ' . $room_id);
    log_message('debug', 'Current User ID: ' . $current_user_id);
    log_message('debug', 'Other User ID: ' . $other_user_id);
    log_message('debug', 'Current Role: ' . $current_role);

    $data = array(
        'room_id'       => $room_id,
        'current_user'  => $current_user_data,
        'other_user'    => $other_user,
        'consultation'  => $consultation,
        'current_role'  => $current_role
    );

    $this->load->view('consultation_video', $data);
}
}