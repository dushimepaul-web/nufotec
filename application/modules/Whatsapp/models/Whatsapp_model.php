<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_stats() {
        $stats = [];
        
        // Nombre de groupes actifs
        $stats['total_groups'] = $this->db->where('actif', 1)->count_all_results('groupes_whatsapp');
        
        // Nombre de participants
        $stats['total_participants'] = $this->db->count_all('participants_whatsapp');
        
        // Nombre de participants bloqués
        $stats['blocked_participants'] = $this->db->where('is_blocked', 1)->count_all_results('participants_whatsapp');
        
        // Messages aujourd'hui
        $stats['messages_today'] = $this->db
            ->where('DATE(created_at)', date('Y-m-d'))
            ->count_all_results('wa_messages_queue');
        
        // Messages en attente
        $stats['pending_messages'] = $this->db
            ->where('status', 'pending')
            ->count_all_results('wa_messages_queue');
        
        // Messages échoués
        $stats['failed_messages'] = $this->db
            ->where('status', 'failed')
            ->count_all_results('wa_messages_queue');
        
        // Violations aujourd'hui
        $stats['violations_today'] = $this->db
            ->where('DATE(created_at)', date('Y-m-d'))
            ->count_all_results('violations_log');
        
        return $stats;
    }
    
    public function get_detailed_stats() {
        $stats = [];
        
        // Messages par statut
        $stats['by_status'] = $this->db
            ->select('status, COUNT(*) as count')
            ->group_by('status')
            ->get('wa_messages_queue')
            ->result();
        
        // Messages par type de média
        $stats['by_media_type'] = $this->db
            ->select('media_type, COUNT(*) as count')
            ->group_by('media_type')
            ->get('wa_messages_queue')
            ->result();
        
        // Messages par admin vs membre
        $stats['by_admin'] = $this->db
            ->select('is_admin, COUNT(*) as count')
            ->group_by('is_admin')
            ->get('wa_messages_queue')
            ->result();
        
        return $stats;
    }
    
    public function get_messages_per_day($days = 30) {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM wa_messages_queue 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        return $this->db->query($sql, [$days])->result();
    }
    
    public function get_violations_per_day($days = 30) {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM violations_log 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        return $this->db->query($sql, [$days])->result();
    }
    
    public function get_top_groups($limit = 10) {
        $sql = "SELECT g.nom, g.groupe_id, COUNT(m.id) as message_count 
                FROM groupes_whatsapp g
                LEFT JOIN wa_messages_queue m ON m.id IN (
                    SELECT queue_id FROM broadcast_logs WHERE recipient_id = g.groupe_id
                )
                GROUP BY g.id
                ORDER BY message_count DESC
                LIMIT ?";
        return $this->db->query($sql, [$limit])->result();
    }
    
    public function get_top_violators($limit = 10) {
        $sql = "SELECT phone_number, COUNT(*) as violation_count 
                FROM violations_log 
                GROUP BY phone_number 
                ORDER BY violation_count DESC 
                LIMIT ?";
        return $this->db->query($sql, [$limit])->result();
    }
    
    public function get_all_groups_with_stats() {
        $sql = "SELECT g.*, 
                (SELECT COUNT(*) FROM participants_whatsapp WHERE groupe_id = g.groupe_id) as participant_count,
                (SELECT COUNT(*) FROM broadcast_logs WHERE recipient_id = g.groupe_id AND status = 'sent') as message_count
                FROM groupes_whatsapp g
                ORDER BY g.nom ASC";
        return $this->db->query($sql)->result();
    }
    
    public function get_logs($limit = 100) {
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('broadcast_logs')->result();
    }
    
    public function clear_logs() {
        $this->db->empty_table('broadcast_logs');
    }
    
    public function count_recipients($target_type) {
        $count = 0;
        if ($target_type == 'groups' || $target_type == 'both') {
            $count += $this->db->where('actif', 1)->count_all_results('groupes_whatsapp');
        }
        if ($target_type == 'inbox' || $target_type == 'both') {
            $count += $this->db->where('is_blocked', 0)
                              ->count_all_results('participants_whatsapp');
        }
        return $count;
    }
    
    public function get_antiban_settings() {
        return $this->db->get('antiban_settings')->result();
    }
    
    public function update_setting($key, $value) {
        $this->db->where('key', $key);
        $this->db->update('antiban_settings', ['value' => $value]);
    }
}