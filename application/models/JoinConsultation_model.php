<?php
class JoinConsultation_model extends CI_Model {

    /**
     * Vérifie qu'une consultation avec ce room_id existe et que l'utilisateur
     * (patient ou médecin) y est associé.
     */
    public function get_by_room_and_user($room_id, $user_id) {
        $sql = "SELECT c.* 
                FROM consultations c
                LEFT JOIN medecins m ON m.id = c.medecin_id
                WHERE c.room_id = ? 
                  AND (c.patient_id = ? OR m.user_id = ?)";
        $query = $this->db->query($sql, array($room_id, $user_id, $user_id));
        return $query->row();
    }

    /**
     * Récupère les détails complets d'une consultation (avec les infos des deux participants)
     */
    public function get_full_consultation($room_id) {
        $this->db->select('c.*, 
                           u1.id as patient_user_id, u1.nom as patient_nom, u1.prenom as patient_prenom, u1.photo as patient_photo,
                           u2.id as medecin_user_id, u2.nom as medecin_nom, u2.prenom as medecin_prenom, u2.photo as medecin_photo');
        $this->db->from('consultations c');
        $this->db->join('users u1', 'c.patient_id = u1.id');
        $this->db->join('medecins m', 'c.medecin_id = m.id');
        $this->db->join('users u2', 'm.user_id = u2.id');
        $this->db->where('c.room_id', $room_id);
        $query = $this->db->get();
        return $query->row();
    }


    /**
 * Récupérer une consultation par son ID
 */
public function get_by_id($id) {
    return $this->db->where('id', $id)
                    ->get('consultations')
                    ->row();
}

/**
 * Mettre à jour une consultation
 */
public function update_consultation($id, $data) {
    $this->db->where('id', $id);
    return $this->db->update('consultations', $data);
}

/**
 * Récupérer la consultation en cours par room_id
 */
public function get_by_room_id($room_id) {
    return $this->db->where('room_id', $room_id)
                    ->where('statut', 'en_cours')
                    ->or_where('statut', 'confirmee')
                    ->get('consultations')
                    ->row();
}




    /**
     * Crée ou récupère un salon Daily.co pour une consultation
     */
    public function getOrCreateDailyRoom($room_id, $consultation_id = null) {
        // Clé API Daily.co (à configurer dans votre fichier .env ou config)
        $api_key = $this->config->item('daily_api_key');
        
        if (empty($api_key)) {
            // Mode sans API - utiliser le salon public
            return "https://nufotec.daily.co/consultation_{$room_id}";
        }
        
        // Vérifier si le salon existe déjà en base
        $this->db->where('consultation_id', $consultation_id);
        $existing = $this->db->get('daily_rooms')->row();
        
        if ($existing && !empty($existing->room_url)) {
            return $existing->room_url;
        }
        
        // Créer le salon sur Daily.co
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.daily.co/v1/rooms");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'name' => "consultation_{$room_id}",
            'privacy' => 'private',
            'properties' => [
                'enable_chat' => true,
                'enable_screenshare' => true,
                'lang' => 'fr',
                'max_participants' => 2
            ]
        ]));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $room = json_decode($response, true);
            $room_url = $room['url'];
            
            // Sauvegarder en base
            if ($consultation_id) {
                $this->db->insert('daily_rooms', [
                    'consultation_id' => $consultation_id,
                    'room_id' => $room_id,
                    'room_url' => $room_url,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            return $room_url;
        }
        
        // Fallback : utiliser le mode public
        return "https://nufotec.daily.co/consultation_{$room_id}";
    }

}