<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modèle pour gérer les participants WhatsApp
 * Synchronisation automatique avec l'API Whapi
 */
class Participant_model extends CI_Model {
    
    protected $table = 'whatsapp_participants';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * ✅ Sauvegarder ou mettre à jour un participant (UPSERT)
     * Appelé automatiquement à chaque synchronisation
     */
    public function sauvegarder($data) {
        // Vérifier si le participant existe déjà
        $exists = $this->db->where([
            'groupe_id' => $data['groupe_id'],
            'phone' => $data['phone']
        ])->get($this->table)->row();
        
        $now = date('Y-m-d H:i:s');
        
        $insert_data = [
            'groupe_id' => $data['groupe_id'],
            'phone' => $data['phone'],
            'phone_formatted' => $data['phone_formatted'] ?? $this->format_phone($data['phone']),
            'rank' => $data['rank'] ?? 'member',
            'is_admin' => in_array($data['rank'] ?? '', ['creator', 'admin']) ? 1 : 0,
            'is_creator' => ($data['rank'] ?? '') === 'creator' ? 1 : 0,
            'profile_name' => $data['profile_name'] ?? null,
            'synced_at' => $now
        ];
        
        if ($exists) {
            // Mise à jour
            $this->db->where([
                'groupe_id' => $data['groupe_id'],
                'phone' => $data['phone']
            ])->update($this->table, $insert_data);
            
            return ['action' => 'updated', 'id' => $exists->id];
        } else {
            // Insertion
            $this->db->insert($this->table, $insert_data);
            return ['action' => 'inserted', 'id' => $this->db->insert_id()];
        }
    }
    
    /**
     * ✅ Synchroniser tous les participants d'un groupe
     * Supprime les anciens participants qui ne sont plus dans le groupe
     */
    public function synchroniser_groupe($groupe_id, $participants) {
        $now = date('Y-m-d H:i:s');
        $phones_actuels = [];
        $stats = ['inserted' => 0, 'updated' => 0, 'deleted' => 0];
        
        // Insérer/mettre à jour chaque participant
        foreach ($participants as $p) {
            $phones_actuels[] = $p['phone'];
            
            $result = $this->sauvegarder([
                'groupe_id' => $groupe_id,
                'phone' => $p['phone'],
                'phone_formatted' => $p['number_formatted'] ?? null,
                'rank' => $p['rank'] ?? 'member',
                'profile_name' => $p['profile_name'] ?? null
            ]);
            
            $stats[$result['action']]++;
        }
        
        // Supprimer les participants qui ne sont plus dans le groupe
        if (!empty($phones_actuels)) {
            $this->db->where('groupe_id', $groupe_id);
            $this->db->where_not_in('phone', $phones_actuels);
            $stats['deleted'] = $this->db->delete($this->table);
        }
        
        // Mettre à jour le timestamp de synchronisation du groupe
        $this->db->where('id_groupe', $groupe_id)
                 ->update('groupes_whatsapp', ['last_sync' => $now]);
        
        return $stats;
    }
    
    /**
     * ✅ Récupérer les participants d'un groupe
     */
    public function get_by_groupe($groupe_id, $filters = []) {
        $this->db->where('groupe_id', $groupe_id);
        
        if (!empty($filters['rank'])) {
            $this->db->where('rank', $filters['rank']);
        }
        
        if (!empty($filters['is_admin'])) {
            $this->db->where('is_admin', 1);
        }
        
        return $this->db->order_by('is_creator', 'DESC')
                       ->order_by('is_admin', 'DESC')
                       ->order_by('phone')
                       ->get($this->table)
                       ->result();
    }
    
    /**
     * ✅ Récupérer un participant par numéro
     */
    public function get_by_phone($phone) {
        return $this->db->where('phone', $phone)
                       ->or_where('phone_formatted', $phone)
                       ->get($this->table)
                       ->row();
    }
    
    /**
     * ✅ Rechercher un participant dans tous les groupes
     */
    public function rechercher($query) {
        $this->db->like('phone', $query);
        $this->db->or_like('phone_formatted', $query);
        $this->db->or_like('profile_name', $query);
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * ✅ Statistiques des participants
     */
    public function get_stats() {
        return [
            'total' => $this->db->count_all($this->table),
            'admins' => $this->db->where('is_admin', 1)->count_all_results($this->table),
            'creators' => $this->db->where('is_creator', 1)->count_all_results($this->table),
            'groupes_uniques' => $this->db->select('COUNT(DISTINCT groupe_id) as count')->get($this->table)->row()->count
        ];
    }
    
    /**
     * ✅ Nettoyer les anciennes synchronisations (>30 jours)
     */
    public function cleanup($jours = 30) {
        $date_limit = date('Y-m-d H:i:s', strtotime("-$jours days"));
        
        $this->db->where('synced_at <', $date_limit);
        return $this->db->delete($this->table);
    }
    
    /**
     * Helper: Formater un numéro de téléphone
     */
    private function format_phone($phone) {
        $phone = str_replace(['@s.whatsapp.net', '@c.us'], '', $phone);
        if (preg_match('/^\d{10,15}$/', $phone)) {
            return '+' . $phone;
        }
        return $phone;
    }
}