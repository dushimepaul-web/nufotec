<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Group_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_active_groups() {
        $this->db->where('actif', 1);
        $this->db->order_by('nom', 'ASC');
        return $this->db->get('groupes_whatsapp')->result();
    }
    
    public function get_all_groups() {
        $this->db->order_by('nom', 'ASC');
        return $this->db->get('groupes_whatsapp')->result();
    }
    
    public function group_exists($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->get('groupes_whatsapp')->num_rows() > 0;
    }
    
    public function upsert_group($groupe_id, $nom = null, $description = null, $participant_count = null) {
        $this->db->where('groupe_id', $groupe_id);
        $exists = $this->db->get('groupes_whatsapp')->row();
        
        $data = [
            'groupe_id' => $groupe_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($nom) $data['nom'] = $nom;
        if ($description) $data['description'] = $description;
        if ($participant_count !== null) $data['participant_count'] = $participant_count;
        
        if ($exists) {
            $this->db->where('id', $exists->id);
            return $this->db->update('groupes_whatsapp', $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['actif'] = 1;
            return $this->db->insert('groupes_whatsapp', $data);
        }
    }
    
    public function update_participant_count($groupe_id, $count) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->update('groupes_whatsapp', ['participant_count' => $count]);
    }
    
    public function update_last_sync($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->update('groupes_whatsapp', ['last_sync_at' => date('Y-m-d H:i:s')]);
    }
    
    public function disable_group($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->update('groupes_whatsapp', ['actif' => 0]);
    }
    
    public function delete_group($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->delete('groupes_whatsapp');
    }
    
    public function toggle_status($groupe_id, $actif) {
        $this->db->where('groupe_id', $groupe_id);
        return $this->db->update('groupes_whatsapp', ['actif' => $actif]);
    }
}