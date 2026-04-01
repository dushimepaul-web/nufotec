<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chatbot_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Récupère ou crée un utilisateur
     */
    public function getOrCreateUser($phone) {
        $phoneNumber = str_replace('@s.whatsapp.net', '', $phone);
        
        $user = $this->db->get_where('chatbot_users', ['phone' => $phoneNumber])->row();
        
        if (!$user) {
            $this->db->insert('chatbot_users', [
                'phone' => $phoneNumber,
                'last_message_at' => date('Y-m-d H:i:s'),
                'total_messages' => 1
            ]);
            $user_id = $this->db->insert_id();
            $user = $this->db->get_where('chatbot_users', ['id' => $user_id])->row();
        } else {
            $this->db->where('id', $user->id);
            $this->db->update('chatbot_users', [
                'last_message_at' => date('Y-m-d H:i:s'),
                'total_messages' => $user->total_messages + 1
            ]);
        }
        
        return $user;
    }
    
    /**
     * Sauvegarde un message
     */
    public function saveMessage($user_id, $message, $response = null, $direction = 'incoming') {
        $data = [
            'user_id' => $user_id,
            'message_text' => $message,
            'response_text' => $response,
            'direction' => $direction,
            'status' => 'received'
        ];
        
        return $this->db->insert('chatbot_conversations', $data);
    }
    
    /**
     * Récupère une commande
     */
    public function getCommand($command) {
        return $this->db->get_where('chatbot_commands', [
            'command' => $command,
            'is_active' => 1
        ])->row();
    }
    
    /**
     * Récupère toutes les commandes
     */
    public function getAllCommands() {
        return $this->db->order_by('command', 'ASC')->get('chatbot_commands')->result();
    }
    
    /**
     * Ajoute ou modifie une commande
     */
    public function saveCommand($data) {
        if (isset($data['id']) && $data['id']) {
            $this->db->where('id', $data['id']);
            return $this->db->update('chatbot_commands', $data);
        } else {
            return $this->db->insert('chatbot_commands', $data);
        }
    }
    
    /**
     * Supprime une commande
     */
    public function deleteCommand($id) {
        return $this->db->delete('chatbot_commands', ['id' => $id]);
    }
    
    /**
     * Récupère les statistiques
     */
    public function getStats() {
        $stats = [];
        
        $stats['total_users'] = $this->db->count_all('chatbot_users');
        $stats['total_messages'] = $this->db->count_all('chatbot_conversations');
        $stats['today_messages'] = $this->db->where('DATE(created_at)', date('Y-m-d'))->count_all_results('chatbot_conversations');
        $stats['active_users'] = $this->db->where('last_message_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))->count_all_results('chatbot_users');
        
        return $stats;
    }
    
    /**
     * Récupère les derniers utilisateurs
     */
    public function getRecentUsers($limit = 10) {
        return $this->db->order_by('last_message_at', 'DESC')->limit($limit)->get('chatbot_users')->result();
    }
    
    /**
     * Journalise les erreurs
     */
    public function logError($message, $details = '') {
        $this->db->insert('chatbot_logs', [
            'type' => 'error',
            'message' => $message,
            'details' => $details
        ]);
    }
}