<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Consultation_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('encryption');
    }
    
    public function insert($data) {
        $this->db->insert('consultations', $data);
        return $this->db->insert_id();
    }
    
    public function get_by_room_secure($room_id) {
        return $this->db->get_where('consultations', ['room_id' => $room_id])->row();
    }
    
    public function get_or_create_patient($data) {
        // Vérifier si patient existe par nom et IP
        $this->db->where('full_name', $data['full_name']);
        $this->db->where('ip_address', $data['ip_address']);
        $query = $this->db->get('users');
        
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        }
        
        // Créer nouveau patient
        $user_data = [
            'full_name' => $data['full_name'],
            'age' => $data['age'],
            'email' => null,
            'password' => null,
            'role' => 'patient',
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('users', $user_data);
        return $this->db->insert_id();
    }
    
    public function create_payment($data) {
        return $this->db->insert('payments', $data);
    }
    
    public function save_access_token($data) {
        return $this->db->insert('access_tokens', $data);
    }
    
    public function verify_token($room_id, $token_hash) {
        $sql = "SELECT at.* FROM access_tokens at 
                JOIN consultations c ON c.id = at.consultation_id 
                WHERE c.room_id = ? AND at.token = ? 
                AND at.expires_at > NOW() AND at.used = 0";
        
        $query = $this->db->query($sql, [$room_id, $token_hash]);
        
        if ($query->num_rows() > 0) {
            $token = $query->row();
            $this->db->update('access_tokens', ['used' => 1], ['id' => $token->id]);
            return true;
        }
        
        return false;
    }
}