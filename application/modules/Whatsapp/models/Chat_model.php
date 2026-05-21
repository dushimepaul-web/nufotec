<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        // Créer la table des conversations si elle n'existe pas
        $this->create_chat_tables();
    }
    
    private function create_chat_tables() {
        // Table des conversations
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `conversations` (
                `id` int NOT NULL AUTO_INCREMENT,
                `chat_id` varchar(100) NOT NULL,
                `chat_type` enum('group','private') NOT NULL,
                `chat_name` varchar(255) DEFAULT NULL,
                `last_message` text,
                `last_message_time` datetime DEFAULT NULL,
                `unread_count` int DEFAULT '0',
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `chat_id_type` (`chat_id`, `chat_type`)
            ) ENGINE=InnoDB
        ");
        
        // Table des messages
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `chat_messages` (
                `id` int NOT NULL AUTO_INCREMENT,
                `chat_id` varchar(100) NOT NULL,
                `chat_type` enum('group','private') NOT NULL,
                `message_id` varchar(255) DEFAULT NULL,
                `message` text,
                `direction` enum('incoming','outgoing') DEFAULT 'incoming',
                `sender` varchar(255) DEFAULT NULL,
                `sender_number` varchar(50) DEFAULT NULL,
                `status` enum('sent','delivered','read','failed') DEFAULT 'sent',
                `media_type` varchar(50) DEFAULT NULL,
                `media_url` varchar(500) DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `chat_id` (`chat_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB
        ");
    }
    
    // ============================================
    // MÉTHODES CHAT (CONVERSATIONS)
    // ============================================
    
    public function get_all_chats() {
        $this->db->order_by('last_message_time', 'DESC');
        return $this->db->get('conversations')->result();
    }
    
    public function get_conversation($chat_id, $type, $limit = 50) {
        $this->db->where('chat_id', $chat_id);
        $this->db->where('chat_type', $type);
        $this->db->order_by('created_at', 'ASC');
        $this->db->limit($limit);
        return $this->db->get('chat_messages')->result();
    }
    
    public function save_message($data) {
        $this->db->insert('chat_messages', $data);
        
        // Mettre à jour la dernière conversation
        $this->db->where('chat_id', $data['chat_id']);
        $this->db->where('chat_type', $data['chat_type']);
        $conversation = $this->db->get('conversations')->row();
        
        if ($conversation) {
            $this->db->where('id', $conversation->id);
            $this->db->update('conversations', [
                'last_message' => $data['message'],
                'last_message_time' => date('Y-m-d H:i:s')
            ]);
            
            // Incrémenter le compteur non lu si c'est un message entrant
            if ($data['direction'] == 'incoming') {
                $this->db->set('unread_count', 'unread_count+1', FALSE);
                $this->db->where('id', $conversation->id);
                $this->db->update('conversations');
            }
        } else {
            $this->db->insert('conversations', [
                'chat_id' => $data['chat_id'],
                'chat_type' => $data['chat_type'],
                'chat_name' => $data['sender'] ?? $data['chat_id'],
                'last_message' => $data['message'],
                'last_message_time' => date('Y-m-d H:i:s'),
                'unread_count' => ($data['direction'] == 'incoming') ? 1 : 0
            ]);
        }
        
        return $this->db->insert_id();
    }
    
    public function mark_as_read($chat_id, $type) {
        $this->db->where('chat_id', $chat_id);
        $this->db->where('chat_type', $type);
        $this->db->where('direction', 'incoming');
        $this->db->update('chat_messages', ['status' => 'read']);
        
        $this->db->where('chat_id', $chat_id);
        $this->db->where('chat_type', $type);
        $this->db->update('conversations', ['unread_count' => 0]);
    }
    
    public function get_recent_messages($limit = 50) {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('chat_messages')->result();
    }
    
    public function sync_from_webhook($message) {
        $chat_id = $message['chatId'] ?? $message['from'];
        $chat_type = (strpos($chat_id, '@g.us') !== false) ? 'group' : 'private';
        
        $message_data = [
            'chat_id' => $chat_id,
            'chat_type' => $chat_type,
            'message_id' => $message['id'] ?? null,
            'message' => $message['body'] ?? null,
            'direction' => 'incoming',
            'sender' => $message['pushName'] ?? $message['author'] ?? 'Unknown',
            'sender_number' => $message['from'] ?? null,
            'status' => 'delivered',
            'media_type' => $message['type'] ?? 'text',
            'media_url' => $message['mediaUrl'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->save_message($message_data);
    }
    
    public function update_message_status($message_id, $status) {
        $this->db->where('message_id', $message_id);
        return $this->db->update('chat_messages', ['status' => $status]);
    }
    
    public function get_unread_counts() {
        return $this->db
            ->select('chat_id, chat_type, unread_count')
            ->where('unread_count >', 0)
            ->get('conversations')
            ->result();
    }
    
    public function delete_conversation($chat_id, $type) {
        $this->db->where('chat_id', $chat_id);
        $this->db->where('chat_type', $type);
        $this->db->delete('conversations');
        
        $this->db->where('chat_id', $chat_id);
        $this->db->where('chat_type', $type);
        $this->db->delete('chat_messages');
    }
    
    // ============================================
    // MÉTHODES GROUPES
    // ============================================
    
    /**
     * Récupère tous les groupes actifs
     */
    public function get_active_groups() {
        $this->db->where('actif', 1);
        $this->db->order_by('nom', 'ASC');
        return $this->db->get('groupes_whatsapp')->result();
    }
    
    /**
     * Active ou désactive un groupe
     */
    public function toggle_status($groupe_id, $actif) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->update('groupes_whatsapp', ['actif' => $actif]);
    }
    
    /**
     * Supprime un groupe
     */
    public function delete_group($groupe_id) {
        // Supprimer d'abord les participants liés
        $this->db->where('groupe_id', $groupe_id);
        $this->db->delete('participants_whatsapp');
        
        // Puis supprimer le groupe
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->delete('groupes_whatsapp');
    }
    
    /**
     * Ajoute ou met à jour un groupe
     */
    public function upsert_group($groupe_id, $nom = null, $description = null) {
        $this->db->where('groupe_id', $groupe_id);
        $exists = $this->db->get('groupes_whatsapp')->row();
        
        $data = [
            'groupe_id' => $groupe_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($nom) $data['nom'] = $nom;
        if ($description) $data['description'] = $description;
        
        if ($exists) {
            $this->db->where('id', $exists->id);
            return $this->db->update('groupes_whatsapp', $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['actif'] = 1;
            return $this->db->insert('groupes_whatsapp', $data);
        }
    }
    
    /**
     * Récupère un groupe par son ID
     */
    public function get_group_by_id($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->get('groupes_whatsapp')->row();
    }
    
    /**
     * Récupère tous les groupes (actifs et inactifs)
     */
    public function get_all_groups() {
        $this->db->order_by('nom', 'ASC');
        return $this->db->get('groupes_whatsapp')->result();
    }
    
    /**
     * Récupère les statistiques d'un groupe
     */
    public function get_group_stats($groupe_id) {
        $sql = "SELECT 
                COUNT(DISTINCT p.id) as total_participants,
                COUNT(DISTINCT CASE WHEN p.is_blocked = 1 THEN p.id END) as blocked_count,
                COUNT(DISTINCT v.id) as total_violations,
                COUNT(DISTINCT b.id) as total_messages
                FROM groupes_whatsapp g
                LEFT JOIN participants_whatsapp p ON p.groupe_id = g.groupe_id
                LEFT JOIN violations_log v ON v.groupe_id = g.groupe_id
                LEFT JOIN broadcast_logs b ON b.recipient_id = g.groupe_id AND b.status = 'sent'
                WHERE g.groupe_id = ?
                GROUP BY g.id";
        $result = $this->db->query($sql, [$groupe_id])->row();
        
        if (!$result) {
            return (object)[
                'total_participants' => 0,
                'blocked_count' => 0,
                'total_violations' => 0,
                'total_messages' => 0
            ];
        }
        return $result;
    }
    
    // ============================================
    // MÉTHODES PARTICIPANTS
    // ============================================
    
    /**
     * Récupère tous les participants avec leur statut
     */
    public function get_all_participants() {
        $sql = "SELECT p.*, 
                COUNT(DISTINCT v.id) as violation_count,
                MAX(v.created_at) as last_violation
                FROM participants_whatsapp p
                LEFT JOIN violations_log v ON v.phone_number = p.participant_phone
                GROUP BY p.id
                ORDER BY p.participant_name ASC";
        return $this->db->query($sql)->result();
    }
    
    /**
     * Récupère les participants d'un groupe avec leur statut
     */
    public function get_group_participants_with_status($groupe_id) {
        $sql = "SELECT p.*, 
                COUNT(DISTINCT v.id) as violation_count,
                MAX(v.created_at) as last_violation
                FROM participants_whatsapp p
                LEFT JOIN violations_log v ON v.phone_number = p.participant_phone
                WHERE p.groupe_id = ?
                GROUP BY p.id
                ORDER BY p.participant_name ASC";
        return $this->db->query($sql, [$groupe_id])->result();
    }
    
    /**
     * Récupère les utilisateurs bloqués
     */
    public function get_blocked_users() {
        $this->db->where('is_blocked', 1);
        $this->db->order_by('violation_count', 'DESC');
        return $this->db->get('participants_whatsapp')->result();
    }
    
    /**
     * Bloque un participant par son ID
     */
    public function block_participant($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('participants_whatsapp', [
            'is_blocked' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Débloque un participant
     */
    public function unblock_participant($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('participants_whatsapp', [
            'is_blocked' => 0,
            'violation_count' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Réinitialise le compteur de violations
     */
    public function reset_violations($participant_id) {
        $this->db->where('id', $participant_id);
        return $this->db->update('participants_whatsapp', [
            'violation_count' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Ajoute un participant
     */
    public function add_participant($groupe_id, $phone, $name) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->where('participant_phone', $phone);
        $exists = $this->db->get('participants_whatsapp')->row();
        
        if (!$exists) {
            return $this->db->insert('participants_whatsapp', [
                'groupe_id' => $groupe_id,
                'participant_phone' => $phone,
                'participant_name' => $name,
                'joined_at' => date('Y-m-d H:i:s')
            ]);
        }
        return false;
    }
    
    /**
     * Met à jour un participant
     */
    public function update_participant($participant_id, $data) {
        $this->db->where('id', $participant_id);
        return $this->db->update('participants_whatsapp', $data);
    }
    
    /**
     * Vérifie si un participant est bloqué
     */
    public function is_participant_blocked($phone) {
        $this->db->where('participant_phone', $phone);
        $this->db->where('is_blocked', 1);
        return $this->db->get('participants_whatsapp')->num_rows() > 0;
    }
    
    /**
     * Incrémente le compteur de violations
     */
    public function increment_violation($phone) {
        $this->db->where('participant_phone', $phone);
        $participant = $this->db->get('participants_whatsapp')->row();
        
        if ($participant) {
            $violations = ($participant->violation_count ?? 0) + 1;
            $this->db->where('participant_phone', $phone);
            $this->db->update('participants_whatsapp', ['violation_count' => $violations]);
            
            // Bloquer après 3 violations
            if ($violations >= 3) {
                $this->db->where('participant_phone', $phone);
                $this->db->update('participants_whatsapp', ['is_blocked' => 1]);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Journalise une violation
     */
    public function log_violation($phone, $violation_type, $message_content, $groupe_id) {
        return $this->db->insert('violations_log', [
            'phone_number' => $phone,
            'violation_type' => $violation_type,
            'message_content' => $message_content,
            'groupe_id' => $groupe_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}