<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Participant_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Récupère tous les participants uniques (non bloqués)
     */
    public function get_all_unique_participants() {
        $sql = "SELECT DISTINCT participant_phone, MAX(participant_name) as participant_name 
                FROM participants_whatsapp 
                WHERE is_blocked = 0 
                GROUP BY participant_phone";
        return $this->db->query($sql)->result();
    }
    
    /**
     * Récupère les participants d'un groupe spécifique
     */
    public function get_group_participants($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('is_blocked', 0);
        return $this->db->get('participants_whatsapp')->result();
    }
    
    /**
     * Ajoute ou met à jour un participant
     */
    public function upsert_participant($groupe_id, $phone, $name) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('participant_phone', $phone);
        $exists = $this->db->get('participants_whatsapp')->row();
        
        if ($exists) {
            $this->db->where('id', $exists->id);
            return $this->db->update('participants_whatsapp', [
                'participant_name' => $name,
                'last_active' => date('Y-m-d H:i:s')
            ]);
        } else {
            return $this->db->insert('participants_whatsapp', [
                'groupe_id' => $groupe_id,
                'participant_phone' => $phone,
                'participant_name' => $name,
                'joined_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Vérifie si un utilisateur est bloqué
     */
    public function is_blocked($phone) {
        $this->db->where('participant_phone', $phone);
        $this->db->where('is_blocked', 1);
        return $this->db->get('participants_whatsapp')->num_rows() > 0;
    }
    
    /**
     * Incrémente le compteur de violations et bloque si nécessaire
     * @return bool True si l'utilisateur a été bloqué
     */
    public function increment_violation($phone) {
        $this->db->where('participant_phone', $phone);
        $participant = $this->db->get('participants_whatsapp')->row();
        
        $violations = ($participant->violation_count ?? 0) + 1;
        
        $this->db->where('participant_phone', $phone);
        $this->db->update('participants_whatsapp', ['violation_count' => $violations]);
        
        // Bloquer après 3 violations
        if ($violations >= 3) {
            $this->db->where('participant_phone', $phone);
            $this->db->update('participants_whatsapp', ['is_blocked' => 1]);
            return true;
        }
        return false;
    }
    
    /**
     * Journalise une violation
     */
    public function log_violation($phone, $violation_type, $message_content, $message_id, $groupe_id) {
        $this->db->insert('violations_log', [
            'phone_number' => $phone,
            'violation_type' => $violation_type,
            'message_content' => $message_content,
            'message_id' => $message_id,
            'groupe_id' => $groupe_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
