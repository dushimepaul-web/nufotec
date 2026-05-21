<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_whapi extends MX_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
        
        if (PHP_SAPI !== 'cli') {
            die('Access denied');
        }
    }
    
    public function sync_all() {
        echo "[" . date('Y-m-d H:i:s') . "] Starting full synchronization...\n";
        
        $groups_synced = $this->sync_groups();
        echo "Groups synced: $groups_synced\n";
        
        $participants_synced = $this->sync_participants();
        echo "Participants synced: $participants_synced\n";
        
        $admins_synced = $this->sync_admins();
        echo "Admins synced: $admins_synced\n";
        
        $cleaned = $this->cleanup_deleted_groups();
        echo "Deleted groups cleaned: $cleaned\n";
        
        echo "[" . date('Y-m-d H:i:s') . "] Synchronization completed!\n";
    }
    
    public function sync_groups() {
        return $this->whapi_library->sync_all_groups();
    }
    
    public function sync_participants() {
        $groups = $this->db->get('groupes_whatsapp')->result();
        $total_synced = 0;
        
        foreach ($groups as $group) {
            $synced = $this->whapi_library->sync_group_participants($group->groupe_id);
            $total_synced += $synced;
            echo "Synced participants for group {$group->groupe_id}: $synced\n";
            
            smart_delay();
        }
        
        return $total_synced;
    }
    
    public function sync_admins() {
        $groups = $this->db->get('groupes_whatsapp')->result();
        $admin_count = 0;
        $admin_numbers = [];
        
        foreach ($groups as $group) {
            $this->db->where('groupe_id', $group->groupe_id);
            $this->db->where('is_admin', 1);
            $admins = $this->db->get('whatsapp_participants')->result();
            
            foreach ($admins as $admin) {
                if (!in_array($admin->phone_formatted, $admin_numbers)) {
                    $admin_numbers[] = $admin->phone_formatted;
                    $admin_count++;
                }
            }
        }
        
        // Mettre à jour les numéros admin dans les settings
        $this->whapi_library->update_setting('admin_numbers', json_encode($admin_numbers));
        
        return $admin_count;
    }
    
    public function cleanup_deleted_groups() {
        return $this->whapi_library->cleanup_deleted_groups();
    }
}