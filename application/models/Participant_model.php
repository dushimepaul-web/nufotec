<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Participant_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_all_unique_participants() {
        $sql = "SELECT DISTINCT phone, MAX(name) as name 
                FROM whatsapp_participants 
                WHERE is_blocked = 0 
                GROUP BY phone";
        return $this->db->query($sql)->result();
    }
    
    public function get_group_participants($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('is_blocked', 0);
        return $this->db->get('whatsapp_participants')->result();
    }
    
    public function upsert_participant($groupe_id, $phone, $name = null) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('phone', $phone);
        $exists = $this->db->get('whatsapp_participants')->row();
        
        if ($exists) {
            $this->db->where('id', $exists->id);
            return $this->db->update('whatsapp_participants', [
                'name' => $name ?? $exists->name,
                'synced_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            return $this->db->insert('whatsapp_participants', [
                'groupe_id' => $groupe_id,
                'phone' => $phone,
                'name' => $name,
                'synced_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    public function is_blocked($phone) {
        $this->db->where('phone', $phone);
        $this->db->where('is_blocked', 1);
        return $this->db->get('whatsapp_participants')->num_rows() > 0;
    }
    
    public function increment_violation($phone) {
        $this->db->where('phone', $phone);
        $participant = $this->db->get('whatsapp_participants')->row();
        
        $violations = ($participant->violation_count ?? 0) + 1;
        
        $this->db->where('phone', $phone);
        $this->db->update('whatsapp_participants', ['violation_count' => $violations]);
        
        if ($violations >= 3) {
            $this->db->where('phone', $phone);
            $this->db->update('whatsapp_participants', ['is_blocked' => 1]);
            return true; // Bloqué
        }
        return false;
    }
    
    public function log_violation($phone, $violation_type, $message_content, $groupe_id) {
        $this->db->insert('violations_log', [
            'phone_number' => $phone,
            'violation_type' => $violation_type,
            'message_content' => $message_content,
            'groupe_id' => $groupe_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function get_blocked_users() {
        $this->db->where('is_blocked', 1);
        return $this->db->get('whatsapp_participants')->result();
    }
    
    public function block_participant($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('whatsapp_participants', ['is_blocked' => 1]);
    }
    
    public function unblock_participant($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('whatsapp_participants', [
            'is_blocked' => 0,
            'violation_count' => 0
        ]);
    }
    
    public function reset_violations($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('whatsapp_participants', ['violation_count' => 0]);
    }
}