<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broker_model extends CI_Model {
    
    protected $table = 'brokers';
    protected $investor_table = 'broker_investors';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    // ========== BROKERS ==========
    
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
     * Récupère un broker par son email
     */
    public function get_broker_by_email($email) {
        $this->db->where('email', $email);
        $query = $this->db->get($this->table);
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
    
    // ========== PAYS ==========
    
    /**
     * Récupère tous les pays
     */
    public function get_all_pays() {
        $this->db->order_by('pays', 'ASC');
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
    
    // ========== INVESTORS ==========
    
    /**
     * Récupère tous les investisseurs d'un broker
     */
    public function get_investors_by_broker($broker_id) {
        $this->db->where('broker_id', $broker_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->investor_table)->result();
    }
    
    /**
     * Récupère un investisseur par son ID
     */
    public function get_investor_by_id($id, $broker_id = null) {
        $this->db->where('id', $id);
        if ($broker_id) {
            $this->db->where('broker_id', $broker_id);
        }
        return $this->db->get($this->investor_table)->row();
    }
    
    /**
     * Insère un nouvel investisseur
     */
    public function insert_investor($data) {
        $this->db->insert($this->investor_table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Met à jour un investisseur
     */
    public function update_investor($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->investor_table, $data);
    }
    
    /**
     * Supprime un investisseur
     */
    public function delete_investor($id, $broker_id = null) {
        if ($broker_id) {
            $this->db->where('broker_id', $broker_id);
        }
        $this->db->where('id', $id);
        return $this->db->delete($this->investor_table);
    }
    
    /**
     * Compte le nombre d'investisseurs d'un broker
     */
    public function count_investors_by_broker($broker_id) {
        $this->db->where('broker_id', $broker_id);
        return $this->db->count_all_results($this->investor_table);
    }
    
    /**
     * Récupère les statistiques des investisseurs par statut
     */
    public function get_investor_stats($broker_id) {
        $this->db->select('status, COUNT(*) as count');
        $this->db->where('broker_id', $broker_id);
        $this->db->group_by('status');
        $query = $this->db->get($this->investor_table);
        $result = $query->result();
        
        $stats = [
            'total_investors' => 0, 
            'pending' => 0, 
            'contacted' => 0, 
            'invested' => 0
        ];
        
        foreach ($result as $row) {
            if (isset($stats[$row->status])) {
                $stats[$row->status] = $row->count;
            }
            $stats['total_investors'] += $row->count;
        }
        
        return $stats;
    }


    // ========== USERS (table users) ==========

/**
 * Récupère un utilisateur par son email
 */
public function get_user_by_email($email) {
    $this->db->where('email', $email);
    $query = $this->db->get('users');
    return $query->row();
}

/**
 * Récupère un utilisateur par son ID
 */
public function get_user_by_id($id) {
    $this->db->where('id', $id);
    $query = $this->db->get('users');
    return $query->row();
}

/**
 * Vérifie si l'email existe déjà dans la table users
 */
public function user_email_exists($email) {
    $this->db->where('email', $email);
    return $this->db->get('users')->num_rows() > 0;
}

/**
 * Insère un nouvel utilisateur
 */
public function insert_user($data) {
    $this->db->insert('users', $data);
    return $this->db->insert_id();
}

/**
 * Met à jour le mot de passe d'un utilisateur
 */
public function update_user_password($user_id, $hashed_password) {
    $this->db->where('id', $user_id);
    return $this->db->update('users', ['password' => $hashed_password]);
}
}
?>