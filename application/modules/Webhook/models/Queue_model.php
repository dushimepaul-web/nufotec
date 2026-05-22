<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Queue_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Ajoute un message à la queue
     */
    public function add_to_queue($data) {
        $queue_data = [
            'message' => $data['message'] ?? null,
            'sender_number' => $data['sender_number'],
            'sender_name' => $data['sender_name'] ?? null,
            'is_admin' => $data['is_admin'] ?? 0,
            'broadcast_type' => $data['broadcast_type'] ?? 'both',
            'media_type' => $data['media_type'] ?? 'text',
            'media_url' => $data['media_url'] ?? null,
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
    
    /**
     * Récupère les messages en attente
     */
    public function get_pending_messages($limit = 5) {
        $this->db->where('status', 'pending')
                 ->where('scheduled_at <=', date('Y-m-d H:i:s'))
                 ->order_by('id', 'ASC')
                 ->limit($limit);
        return $this->db->get('wa_messages_queue')->result();
    }
    
    /**
     * Met à jour le statut d'un message
     */
    public function update_status($id, $status, $error = null) {
        $data = ['status' => $status];
        if ($status == 'sent') {
            $data['processed_at'] = date('Y-m-d H:i:s');
        }
        if ($error) {
            $data['last_error'] = $error;
            $this->db->set('retries', 'retries+1', FALSE);
        }
        $this->db->where('id', $id);
        return $this->db->update('wa_messages_queue', $data);
    }
    
    /**
     * Incrémente le compteur d'envois réussis
     */
    public function increment_sent_count($id) {
        $this->db->set('sent_count', 'sent_count+1', FALSE);
        $this->db->where('id', $id);
        return $this->db->update('wa_messages_queue');
    }
    
    /**
     * Journalise un broadcast
     */
    public function log_broadcast($queue_id, $recipient_type, $recipient_id, $status, $error = null) {
        $this->db->insert('broadcast_logs', [
            'queue_id' => $queue_id,
            'recipient_type' => $recipient_type,
            'recipient_id' => $recipient_id,
            'status' => $status,
            'error_message' => $error,
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }
}
