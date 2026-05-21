<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function log_sync($sync_type, $status, $groups_found, $groups_synced, $participants_found, $participants_synced, $error_message = null, $triggered_by = 'manual') {
        $start_time = microtime(true);
        
        $data = [
            'sync_type' => $sync_type,
            'status' => $status,
            'groups_found' => $groups_found,
            'groups_synced' => $groups_synced,
            'participants_found' => $participants_found,
            'participants_synced' => $participants_synced,
            'error_message' => $error_message,
            'triggered_by' => $triggered_by,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('sync_logs', $data);
    }
    
    public function get_last_sync($sync_type = null) {
        if ($sync_type) {
            $this->db->where('sync_type', $sync_type);
        }
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('sync_logs')->row();
    }
    
    public function get_sync_history($limit = 50) {
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('sync_logs')->result();
    }
}