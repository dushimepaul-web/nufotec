<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Participant_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_all_unique_participants() {
        $sql = "SELECT DISTINCT participant_phone, MAX(participant_name) as participant_name 
                FROM whatsapp_participants 
                WHERE is_blocked = 0 
                GROUP BY participant_phone";
        return $this->db->query($sql)->result();
    }
    
    public function get_all_participants() {
        $this->db->order_by('participant_name', 'ASC');
        return $this->db->get('whatsapp_participants')->result();
    }
    
    public function get_group_participants($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('is_blocked', 0);
        $this->db->order_by('participant_name', 'ASC');
        return $this->db->get('whatsapp_participants')->result();
    }
    
    public function get_group_participants_with_status($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->order_by('participant_name', 'ASC');
        return $this->db->get('whatsapp_participants')->result();
    }
    
    public function get_participant_by_phone($phone) {
        $this->db->where('participant_phone', $phone);
        return $this->db->get('whatsapp_participants')->row();
    }
    
    public function get_blocked_users() {
        $this->db->where('is_blocked', 1);
        $this->db->order_by('violation_count', 'DESC');
        return $this->db->get('whatsapp_participants')->result();
    }
    
    public function upsert_participant($groupe_id, $phone, $name) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('participant_phone', $phone);
        $exists = $this->db->get('whatsapp_participants')->row();
        
        $data = [
            'participant_name' => $name,
            'last_active' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($exists) {
            $this->db->where('id', $exists->id);
            return $this->db->update('whatsapp_participants', $data);
        } else {
            $data['groupe_id'] = $groupe_id;
            $data['participant_phone'] = $phone;
            $data['joined_at'] = date('Y-m-d H:i:s');
            $data['synced_at'] = date('Y-m-d H:i:s');
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('whatsapp_participants', $data);
        }
    }
    
    public function upsert_participant_with_sync($groupe_id, $phone, $name) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('participant_phone', $phone);
        $exists = $this->db->get('whatsapp_participants')->row();
        
        $data = [
            'participant_name' => $name,
            'synced_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($exists) {
            $this->db->where('id', $exists->id);
            return $this->db->update('whatsapp_participants', $data);
        } else {
            $data['groupe_id'] = $groupe_id;
            $data['participant_phone'] = $phone;
            $data['joined_at'] = date('Y-m-d H:i:s');
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('whatsapp_participants', $data);
        }
    }
    
    public function is_blocked($phone) {
        $this->db->where('participant_phone', $phone);
        $this->db->where('is_blocked', 1);
        return $this->db->get('whatsapp_participants')->num_rows() > 0;
    }
    
    public function increment_violation($phone) {
        $this->db->where('participant_phone', $phone);
        $participant = $this->db->get('whatsapp_participants')->row();
        
        if (!$participant) return false;
        
        $violations = ($participant->violation_count ?? 0) + 1;
        
        $this->db->where('participant_phone', $phone);
        $this->db->update('whatsapp_participants', ['violation_count' => $violations]);
        
        if ($violations >= 3) {
            $this->db->where('participant_phone', $phone);
            $this->db->update('whatsapp_participants', ['is_blocked' => 1]);
            return true;
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
    
    public function block_participant($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('whatsapp_participants', ['is_blocked' => 1]);
    }
    
    public function unblock_participant($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('whatsapp_participants', ['is_blocked' => 0, 'violation_count' => 0]);
    }
    
    public function reset_violations($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('whatsapp_participants', ['violation_count' => 0]);
    }
    
    public function delete_participants_by_group($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->delete('whatsapp_participants');
    }
    
    public function count_by_group($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->count_all_results('whatsapp_participants');
    }
}