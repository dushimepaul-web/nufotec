<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Joinconsultation extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('JoinConsultation_model');
        $this->load->library('form_validation');
    }

    // URL : /joinconsultation/index?room=ABC123&user=12
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
            redirect("/joinconsultation/room/$room_id/$user_id");
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
        $this->form_validation->set_rules('user_id', 'Utilisateur', 'required');
        $this->form_validation->set_rules('room_id', 'Salle', 'required');

        if ($this->form_validation->run() == FALSE) {
            // Rediriger avec erreur
            $this->index();
        } else {
            $user_id = $this->input->post('user_id');
            $password = $this->input->post('password');
            $room_id = $this->input->post('room_id');

            
            $user = $this->Model->read('users',['id'=>$user_id]);

            if ($user && password_verify($password, $user->password)) {
                // Connexion réussie, créer session
                $session_data = array(
                    'user_id'   => $user->id,
                    'email'     => $user->email,
                    'nom'       => $user->nom,
                    'prenom'    => $user->prenom,
                    'photo'     => $user->photo,
                    'logged_in' => TRUE
                );
                $this->session->set_userdata($session_data);
                redirect("/joinconsultation/room/$room_id/$user_id");
            } else {
                $this->session->set_flashdata('error', 'Mot de passe incorrect');
                redirect("/joinconsultation/index?room=$room_id&user=$user_id");
            }
        }
    }




public function room($room_id, $user_id) {
    // Vérifier que l'utilisateur est bien connecté et correspond
    if (!$this->session->userdata('logged_in') || $this->session->userdata('user_id') != $user_id) {
        redirect("/joinconsultation/index?room=$room_id&user=$user_id");
    }

    // Récupérer les informations de la consultation avec les deux participants
    $consultation = $this->JoinConsultation_model->get_full_consultation($room_id);
    if (!$consultation) {
        show_404();
    }

    // Déterminer qui est l'autre participant et quel est le rôle du courant
    $current_user_id = $user_id;
    if ($consultation->patient_user_id == $current_user_id) {
        $other_user_id = $consultation->medecin_user_id;
        $current_role = 'patient';
    } else {
        $other_user_id = $consultation->patient_user_id;
        $current_role = 'medecin';
    }

    // Charger les infos de l'autre participant
    $other_user = $this->Model->read('users', ['id' => $other_user_id]);

    $data = array(
        'room_id'       => $room_id,
        'current_user'  => $this->session->userdata(),
        'other_user'    => $other_user,
        'consultation'  => $consultation,
        'current_role'  => $current_role   // Ajout du rôle
    );

    $this->load->view('consultation_video', $data);
}



public function endConsultationApi($id = null)
    {
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }
        
        $consultation = $this->Model->readOne('consultations', ['id' => $id]);
        if (!$consultation) {
            echo json_encode(['success' => false, 'message' => 'Consultation non trouvée']);
            return;
        }
        
        // Calculer durée
        $debut = strtotime($consultation['date_debut']);
        $fin = time();
        $duree_minutes = round(($fin - $debut) / 60);
        
        $this->Model->update('consultations', ['id' => $id], [
            'statut' => 'terminee',
            'date_fin' => date('Y-m-d H:i:s'),
            'duree_minutes' => $duree_minutes
        ]);
        
        echo json_encode(['success' => true, 'duration' => $duree_minutes]);
    }
}