<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inbox_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Ajoute des messages à l'inbox des participants
     */
    public function add_to_inbox($queue_id, $participants, $media_data) {
        $batch_data = [];
        foreach ($participants as $participant) {
            $batch_data[] = [
                'queue_id' => $queue_id,
                'participant_phone' => $participant->participant_phone,
                'message_content' => $media_data['message'] ?? $media_data['caption'] ?? null,
                'media_type' => $media_data['media_type'] ?? 'text',
                'media_url' => $media_data['media_url'] ?? null,
                'local_media_path' => $media_data['local_media_path'] ?? null,  // NOUVEAU
                'media_caption' => $media_data['media_caption'] ?? null,
                'media_filename' => $media_data['media_filename'] ?? null,
                'status' => 'pending',
                'retries' => 0
            ];
        }
        
        if (!empty($batch_data)) {
            return $this->db->insert_batch('messages_inbox', $batch_data);
        }
        return false;
    }
    
    /**
     * Récupère les messages inbox en attente
     */
    public function get_pending_inbox_messages($limit = 20) {
        $this->db->where('status', 'pending');
        $this->db->order_by('id', 'ASC');
        $this->db->limit($limit);
        return $this->db->get('messages_inbox')->result();
    }
    
    /**
     * Récupère les messages inbox en attente avec lock
     */
    public function get_pending_inbox_messages_locked($limit = 20) {
        $sql = "SELECT * FROM messages_inbox 
                WHERE status = 'pending' 
                ORDER BY id ASC 
                LIMIT ? 
                FOR UPDATE SKIP LOCKED";
        return $this->db->query($sql, [$limit])->result();
    }
    
    /**
     * Met à jour le statut d'un message inbox
     */
    public function update_status($id, $status, $error = null) {
        $data = ['status' => $status];
        if ($status == 'sent') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        if ($error) {
            $data['error_message'] = $error;
            $this->db->set('retries', 'retries+1', FALSE);
        }
        $this->db->where('id', $id);
        return $this->db->update('messages_inbox', $data);
    }
    
    /**
     * Récupère les messages inbox par queue_id
     */
    public function get_by_queue_id($queue_id) {
        $this->db->where('queue_id', $queue_id);
        return $this->db->get('messages_inbox')->result();
    }
}