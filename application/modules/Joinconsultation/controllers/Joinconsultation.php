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
    $consultation_id = is_object($consultation) ? $consultation->id : $consultation['id'];
    $patient_user_id = is_object($consultation) ? $consultation->patient_user_id : $consultation['patient_user_id'];
    $medecin_user_id = is_object($consultation) ? $consultation->medecin_user_id : $consultation['medecin_user_id'];
    $statut_actuel = is_object($consultation) ? $consultation->statut : $consultation['statut'];

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

    // NOUVEAU : Mettre à jour le statut de la consultation
    // Si la consultation est 'confirmee', on la passe à 'en_cours'
    // et on enregistre la date de début
    if ($statut_actuel == 'confirmee') {
        $update_data = [
            'statut' => 'en_cours',
            'date_debut' => date('Y-m-d H:i:s')
        ];
        
        $updated = $this->JoinConsultation_model->update_consultation($consultation_id, $update_data);
        
        if ($updated) {
            log_message('info', "Consultation $consultation_id démarrée - Passage de 'confirmee' à 'en_cours'");
        } else {
            log_message('error', "Erreur lors du démarrage de la consultation $consultation_id");
        }
    } elseif ($statut_actuel == 'en_cours') {
        // Si déjà en cours, on ne fait rien
        log_message('debug', "Consultation $consultation_id déjà en cours");
    } elseif ($statut_actuel == 'terminee') {
        // Si déjà terminée, on bloque l'accès
        show_error('Cette consultation est déjà terminée.', 403);
    } elseif ($statut_actuel == 'annulee') {
        // Si annulée, on bloque l'accès
        show_error('Cette consultation a été annulée.', 403);
    }

    // Charger les infos de l'autre participant
    
    $other_user = $this->Model->get_user_by_id($other_user_id);

    if (!$other_user) {
        show_error('L\'autre participant est introuvable.', 404);
    }

    // S'assurer que current_user a la clé 'id'
    $current_user_data = $this->session->userdata();
    $current_user_data['id'] = $current_user_data['user_id'];

    $waitingMessage = ($current_role === 'patient') ? 'En attente du médecin...' : 'En attente du patient...';

    $data = array(
        'room_id'       => $room_id,
        'current_user'  => $current_user_data,
        'other_user'    => $other_user,
        'consultation'  => $consultation,
        'current_role'  => $current_role,
        'waitingMessage' => $waitingMessage 
    );

    $this->load->view('consultation_video', $data);
}

/**
 * API pour terminer une consultation
 * Met à jour le statut de 'en_cours' à 'terminee'
 * Calcule automatiquement la durée
 */
public function endConsultationApi($id = null) {
    // Vérifier que c'est une requête AJAX
    if (!$this->input->is_ajax_request()) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(403)
            ->set_output(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
    }

    // Vérifier que l'utilisateur est connecté
    if (!$this->session->userdata('logged_in')) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(401)
            ->set_output(json_encode(['success' => false, 'message' => 'Non authentifié']));
    }

    // Vérifier l'ID
    if (empty($id)) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'ID de consultation manquant']));
    }
    
    // Récupérer la consultation
    $consultation = $this->JoinConsultation_model->get_by_id($id);
    
    if (!$consultation) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Consultation non trouvée']));
    }
    
    // Vérifier que l'utilisateur est bien participant à cette consultation
    $user_id = $this->session->userdata('user_id');
    $patient_id = is_object($consultation) ? $consultation->patient_id : $consultation['patient_id'];
    $medecin_id = is_object($consultation) ? $consultation->medecin_id : $consultation['medecin_id'];
    
    if ($user_id != $patient_id && $user_id != $medecin_id) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(403)
            ->set_output(json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à terminer cette consultation']));
    }
    
    // Récupérer la date de début
    $date_debut = null;
    if (is_object($consultation)) {
        $date_debut = $consultation->date_debut ?? $consultation->date_confirmee ?? null;
    } else {
        $date_debut = $consultation['date_debut'] ?? $consultation['date_confirmee'] ?? null;
    }
    
    // Calculer la durée
    $duree_minutes = 30; // Durée par défaut
    
    if ($date_debut) {
        $debut = strtotime($date_debut);
        $fin = time();
        $duree_calc = max(1, round(($fin - $debut) / 60)); // Minimum 1 minute
        $duree_minutes = min($duree_calc, 120); // Maximum 2 heures
    }
    
    // Préparer les données de mise à jour
    $update_data = [
        'statut' => 'terminee',
        'date_fin' => date('Y-m-d H:i:s'),
        'duree_minutes' => $duree_minutes
    ];
    
    // Mettre à jour la consultation
    $result = $this->JoinConsultation_model->update_consultation($id, $update_data);
    
    if ($result) {
        // Log pour debug
        log_message('info', "Consultation $id terminée. Durée: $duree_minutes minutes");
        
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true, 
                'message' => 'Consultation terminée avec succès',
                'duration' => $duree_minutes,
                'statut' => 'terminee'
            ]));
    } else {
        log_message('error', "Erreur lors de la terminaison de la consultation $id");
        
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false, 
                'message' => 'Erreur lors de la mise à jour'
            ]));
    }
}
}