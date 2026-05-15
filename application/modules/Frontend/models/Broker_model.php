<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broker_model extends CI_Model {
    
    protected $table = 'brokers';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Insère un nouveau broker
     */
    public function insert_broker($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Récupère tous les brokers avec pagination
     */
    public function get_all_brokers($limit = null, $offset = 0, $filters = []) {
        $this->db->select('brokers.*, pays.pays as country_name');
        $this->db->from($this->table);
        $this->db->join('pays', 'pays.id = brokers.id_pays', 'left');
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('brokers.full_name', $filters['search']);
            $this->db->or_like('brokers.firm_name', $filters['search']);
            $this->db->or_like('brokers.email', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['regulatory_status'])) {
            $this->db->where('brokers.regulatory_status', $filters['regulatory_status']);
        }
        
        if (!empty($filters['id_pays'])) {
            $this->db->where('brokers.id_pays', $filters['id_pays']);
        }
        
        // Correction : trier par created_at au lieu de pays
        $this->db->order_by('brokers.created_at', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Compte le nombre total de brokers
     */
    public function count_all_brokers($filters = []) {
        $this->db->from($this->table);
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('full_name', $filters['search']);
            $this->db->or_like('firm_name', $filters['search']);
            $this->db->or_like('email', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['regulatory_status'])) {
            $this->db->where('regulatory_status', $filters['regulatory_status']);
        }
        
        if (!empty($filters['id_pays'])) {
            $this->db->where('id_pays', $filters['id_pays']);
        }
        
        return $this->db->count_all_results();
    }
    
    /**
     * Récupère un broker par son ID
     */
    public function get_broker_by_id($id) {
        $this->db->select('brokers.*, pays.pays as country_name');
        $this->db->from($this->table);
        $this->db->join('pays', 'pays.id = brokers.id_pays', 'left');
        $this->db->where('brokers.id', $id);
        $query = $this->db->get();
        return $query->row();
    }
    
    /**
     * Vérifie si l'email existe déjà
     */
    public function email_exists($email, $exclude_id = null) {
        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
    
    /**
     * Supprime un broker
     */
    public function delete_broker($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    /**
     * Met à jour un broker
     */
    public function update_broker($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }


   
    public function get_all_pays() {
        // Correction : utiliser la bonne colonne 'pays' dans la table 'pays'
        $this->db->order_by('pays','ASC');  // 'pays' est dans la table 'pays'
        $query = $this->db->get('pays');
        return $query->result();
    }
    
    /**
     * Récupère un pays par son ID
     */
    public function get_pays_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('pays');
        return $query->row();
    }
}
