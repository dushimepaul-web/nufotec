<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VideoCall extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        
    }
    
    /**
     * Affiche la salle de vidéoconférence
     * L'authentification et l'autorisation sont déjà vérifiées dans joinMeet()
     */
    public function index() {
        $room_id = $this->input->get('room');
        
        if (empty($room_id)) {
            redirect('Consultations/Entente');
            return;
        }
        
        $consultation = $this->getConsultationByRoomId($room_id);
        
        // On récupère simplement les infos pour l'affichage
        // (la vérification des droits a déjà été faite dans joinMeet)
        
        $user_id = $this->session->userdata('user_id');
        $user_type = $this->session->userdata('type_utilisateur');
        $user_nom = $this->session->userdata('nom');
        $user_prenom = $this->session->userdata('prenom');
        
        // Déterminer le nom d'affichage et si c'est l'initiateur
        if ($user_type === 'medecin') {
            $display_name = $user_nom . ' ' . $user_prenom;
            $is_initiator = true;
        } else {
            $display_name = $user_prenom . ' ' . $user_nom;
            $is_initiator = false;
        }
        
        $data = [
            'room_id'         => $room_id,
            'consultation_id' => $consultation['id'],
            'user_id'         => $user_id,
            'user_type'       => $user_type,
            'username'        => $display_name,
            'is_initiator'    => $is_initiator,
            'patient_name'    => $consultation['patient_prenom'] . ' ' . $consultation['patient_nom'],
            'medecin_name'    => $consultation['medecin_prenom'] . ' ' . $consultation['medecin_nom'],
            'specialite'      => $consultation['medecin_specialite'],
            'date_debut'      => $consultation['date_debut'],
            'ws_url'          => $this->getWebSocketUrl(),
            'ice_servers'     => $this->getIceServers()
        ];
        
        $this->load->view('video_call', $data);
    }
    
    private function getConsultationByRoomId($room_id) {
        $this->db->select('
            c.*,
            p.nom as patient_nom,
            p.prenom as patient_prenom,
            u.nom as medecin_nom,
            u.prenom as medecin_prenom,
            m.specialite as medecin_specialite
        ');
        $this->db->from('consultations c');
        $this->db->join('users p', 'p.id = c.patient_id', 'left');
        $this->db->join('medecins m', 'm.id = c.medecin_id', 'left');
        $this->db->join('users u', 'u.id = m.user_id', 'left');
        $this->db->where('c.room_id', $room_id);
        
        return $this->db->get()->row_array();
    }
    
    public function endConsultation() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $room_id = $this->input->post('room_id');
        
        if (empty($room_id)) {
            echo json_encode(['success' => false]);
            return;
        }
        
        $consultation = $this->getConsultationByRoomId($room_id);
        
        $user_type = $this->session->userdata('type_utilisateur');
        
        // Seul le médecin peut terminer
        if ($user_type !== 'medecin') {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }
        
        $debut = !empty($consultation['date_debut']) ? strtotime($consultation['date_debut']) : time();
        $fin = time();
        $duree_minutes = max(1, round(($fin - $debut) / 60));
        
        $this->db->where('id', $consultation['id']);
        $success = $this->db->update('consultations', [
            'statut' => 'terminee',
            'date_fin' => date('Y-m-d H:i:s'),
            'duree_minutes' => $duree_minutes
        ]);
        
        echo json_encode([
            'success' => $success,
            'duration' => $duree_minutes
        ]);
    }
    
    private function getWebSocketUrl() {
        return $this->Model->get_setting('websocket_url', 'http://localhost:3001');
    }
    
    private function getIceServers() {
        return [
            ['urls' => 'stun:stun.l.google.com:19302'],
            ['urls' => 'stun:stun1.l.google.com:19302']
        ];
    }
}