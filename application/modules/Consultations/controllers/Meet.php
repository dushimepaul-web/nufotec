<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meet extends MY_Controller {

    public function index($room_id = null)
    {
        if (empty($room_id)) {
            show_404();
        }
        
        // Vérifier que la consultation existe et est confirmée
        $this->db->where('room_id', $room_id);
        $consultation = $this->db->get('consultations')->row_array();
        
        if (!$consultation) {
            show_404();
        }
        
        // Vérifier que la consultation est bien confirmée ou en cours
        if (!in_array($consultation['statut'], ['confirmee', 'en_cours'])) {
            $data['error'] = 'Cette consultation n\'est plus disponible.';
            $this->load->view('meet/error', $data);
            return;
        }
        
        // Vérifier que la date est valide (pas plus de 15 min avant ou après)
        $now = time();
        $consultation_time = strtotime($consultation['date_confirmee']);
        $diff_minutes = abs($now - $consultation_time) / 60;
        
        // Autoriser l'accès 15 min avant et jusqu'à 1h après
        if ($diff_minutes > 75 && $consultation['statut'] != 'en_cours') {
            $data['error'] = 'La consultation n\'est pas encore disponible ou a expiré.';
            $data['scheduled_time'] = $consultation['date_confirmee'];
            $this->load->view('meet/waiting', $data);
            return;
        }
        
        // Mettre à jour le statut si c'est la première connexion
        if ($consultation['statut'] == 'confirmee') {
            $this->db->where('id', $consultation['id']);
            $this->db->update('consultations', [
                'statut' => 'en_cours',
                'date_debut' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Récupérer les infos complètes
        $data['consultation'] = $this->getConsultationDetails($consultation['id']);
        $data['room_id'] = $room_id;
        
        // Charger la vue de la salle de meet
        $this->load->view('meet/room', $data);
    }
    
    private function getConsultationDetails($id)
    {
        $this->db->select('
            c.*,
            p.nom as patient_nom,
            p.prenom as patient_prenom,
            p.email as patient_email,
            u.nom as medecin_nom,
            u.prenom as medecin_prenom,
            m.specialite as medecin_specialite
        ');
        $this->db->from('consultations c');
        $this->db->join('users p', 'p.id = c.patient_id', 'left');
        $this->db->join('medecins m', 'm.id = c.medecin_id', 'left');
        $this->db->join('users u', 'u.id = m.user_id', 'left');
        $this->db->where('c.id', $id);
        
        return $this->db->get()->row_array();
    }
    
    /**
     * API pour terminer la consultation depuis la salle
     */
    public function endConsultation()
    {
        $room_id = $this->input->post('room_id');
        
        if (empty($room_id)) {
            echo json_encode(['success' => false, 'message' => 'Room ID manquant']);
            return;
        }
        
        $this->db->where('room_id', $room_id);
        $consultation = $this->db->get('consultations')->row_array();
        
        if (!$consultation) {
            echo json_encode(['success' => false, 'message' => 'Consultation non trouvée']);
            return;
        }
        
        // Calculer la durée
        $debut = strtotime($consultation['date_debut']);
        $fin = time();
        $duree_minutes = round(($fin - $debut) / 60);
        
        $this->db->where('id', $consultation['id']);
        $this->db->update('consultations', [
            'statut' => 'terminee',
            'date_fin' => date('Y-m-d H:i:s'),
            'duree_minutes' => $duree_minutes
        ]);
        
        echo json_encode(['success' => true, 'duration' => $duree_minutes]);
    }
}