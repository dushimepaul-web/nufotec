<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Queue_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function add_to_queue($data) {
        $queue_data = [
            'message' => $data['message'] ?? null,
            'sender_number' => $data['sender_number'],
            'sender_name' => $data['sender_name'] ?? null,
            'is_admin' => $data['is_admin'] ?? 0,
            'target_type' => $data['target_type'] ?? 'both',
            'media_type' => $data['media_type'] ?? 'text',
            'media_url' => $data['media_url'] ?? null,
            'local_media_path' => $data['local_media_path'] ?? null,
            'media_caption' => $data['media_caption'] ?? null,
            'media_filename' => $data['media_filename'] ?? null,
            'status' => 'pending',
            'retries' => 0,
            'max_retries' => 3,
            'total_recipients' => $data['total_recipients'] ?? 0,
            'scheduled_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('wa_messages_queue', $queue_data);
        return $this->db->insert_id();
    }
    
    public function get_pending_messages($limit = 5) {
        $this->db->where('status', 'pending')
                 ->where('scheduled_at <=', date('Y-m-d H:i:s'))
                 ->order_by('id', 'ASC')
                 ->limit($limit);
        return $this->db->get('wa_messages_queue')->result();
    }
    
    public function get_pending_messages_locked($limit = 5) {
        $sql = "SELECT * FROM wa_messages_queue 
                WHERE status = 'pending' AND scheduled_at <= NOW() 
                ORDER BY id ASC 
                LIMIT ? 
                FOR UPDATE SKIP LOCKED";
        return $this->db->query($sql, [$limit])->result();
    }
    
    public function update_status($id, $status, $error = null) {
        $data = ['status' => $status];
        if ($status == 'sent') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        if ($error) {
            $data['last_error'] = $error;
            $this->db->set('retries', 'retries+1', FALSE);
        }
        $this->db->where('id', $id);
        return $this->db->update('wa_messages_queue', $data);
    }
    
    public function increment_sent_count($id) {
        $this->db->set('sent_count', 'sent_count+1', FALSE);
        $this->db->where('id', $id);
        return $this->db->update('wa_messages_queue');
    }
    
    public function get_recipients_by_target_type($message, $exclude_source = null) {
        $recipients = [
            'groups' => [],
            'participants' => []
        ];
        
        if ($message->target_type == 'groups' || $message->target_type == 'both') {
            $this->db->where('actif', 1);
            if ($exclude_source) {
                $this->db->where('groupe_id !=', $exclude_source);
            }
            $recipients['groups'] = $this->db->get('groupes_whatsapp')->result();
        }
        
        if ($message->target_type == 'inbox' || $message->target_type == 'both') {
            $sql = "SELECT DISTINCT phone as participant_phone, MAX(name) as participant_name 
                    FROM whatsapp_participants 
                    WHERE is_blocked = 0 
                    GROUP BY phone";
            $recipients['participants'] = $this->db->query($sql)->result();
        }
        
        return $recipients;
    }
    
    public function get_processing_messages() {
        $this->db->where('status', 'processing');
        return $this->db->get('wa_messages_queue')->result();
    }
    
    public function get_failed_messages($limit = 50) {
        $this->db->where('status', 'failed');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('wa_messages_queue')->result();
    }
    
    public function retry_message($id) {
        $this->db->where('id', $id);
        return $this->db->update('wa_messages_queue', [
            'status' => 'pending',
            'retries' => 0,
            'last_error' => null
        ]);
    }
    
    public function cancel_message($id) {
        $this->db->where('id', $id);
        return $this->db->update('wa_messages_queue', ['status' => 'cancelled']);
    }
}