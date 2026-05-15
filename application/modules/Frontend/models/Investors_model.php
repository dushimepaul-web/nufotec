<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investors_model extends CI_Model {
    
    protected $table = 'investors';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Insère un nouvel investisseur
     */
    public function insert_investor($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Récupère tous les investisseurs avec pagination et filtres
     */
    public function get_all_investors($limit = null, $offset = 0, $filters = []) {
        $this->db->select('investors.*, pays.pays as country_name');
        $this->db->from($this->table);
        $this->db->join('pays', 'pays.id = investors.id_pays', 'left');
        
        // Filtres
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('investors.full_name', $filters['search']);
            $this->db->or_like('investors.email', $filters['search']);
            $this->db->or_like('investors.organization', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['id_pays'])) {
            $this->db->where('investors.id_pays', $filters['id_pays']);
        }
        
        if (!empty($filters['commitment_range'])) {
            $this->db->where('investors.commitment_range', $filters['commitment_range']);
        }
        
        if (!empty($filters['timeline'])) {
            $this->db->where('investors.timeline', $filters['timeline']);
        }
        
        if (isset($filters['interest_equity']) && $filters['interest_equity'] !== '') {
            $this->db->where('investors.interest_equity', $filters['interest_equity']);
        }
        
        $this->db->order_by('investors.created_at', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Compte le nombre total d'investisseurs
     */
    public function count_all_investors($filters = []) {
        $this->db->from($this->table);
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('full_name', $filters['search']);
            $this->db->or_like('email', $filters['search']);
            $this->db->or_like('organization', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['id_pays'])) {
            $this->db->where('id_pays', $filters['id_pays']);
        }
        
        if (!empty($filters['commitment_range'])) {
            $this->db->where('commitment_range', $filters['commitment_range']);
        }
        
        return $this->db->count_all_results();
    }
    
    /**
     * Récupère un investisseur par son ID
     */
    public function get_investor_by_id($id) {
        $this->db->select('investors.*, pays.pays as country_name');
        $this->db->from($this->table);
        $this->db->join('pays', 'pays.id = investors.id_pays', 'left');
        $this->db->where('investors.id', $id);
        $query = $this->db->get();
        return $query->row();
    }
    
    /**
     * Récupère un investisseur par son email
     */
    public function get_investor_by_email($email) {
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
     * Met à jour un investisseur
     */
    public function update_investor($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Supprime un investisseur
     */
    public function delete_investor($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    /**
     * Récupère les statistiques des investisseurs
     */
    public function get_investor_stats() {
        // Total des investisseurs
        $total = $this->db->count_all($this->table);
        
        // Par tranche d'investissement
        $this->db->select('commitment_range, COUNT(*) as count');
        $this->db->group_by('commitment_range');
        $by_range = $this->db->get($this->table)->result();
        
        // Par pays
        $this->db->select('pays.pays, COUNT(investors.id) as count');
        $this->db->from($this->table);
        $this->db->join('pays', 'pays.id = investors.id_pays', 'left');
        $this->db->group_by('investors.id_pays');
        $this->db->order_by('count', 'DESC');
        $this->db->limit(5);
        $by_country = $this->db->get()->result();
        
        // Par type d'intérêt
        $interests = [
            'interest_equity' => 0,
            'interest_debt' => 0,
            'interest_blended_finance' => 0,
            'interest_grant' => 0,
            'interest_strategic_partnership' => 0
        ];
        
        foreach (array_keys($interests) as $interest) {
            $this->db->where($interest, 1);
            $interests[$interest] = $this->db->count_all_results($this->table);
        }
        
        // Évolution mensuelle (12 derniers mois)
        $this->db->select("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count");
        $this->db->group_by("DATE_FORMAT(created_at, '%Y-%m')");
        $this->db->order_by('month', 'DESC');
        $this->db->limit(12);
        $monthly = $this->db->get($this->table)->result();
        
        return [
            'total' => $total,
            'by_range' => $by_range,
            'by_country' => $by_country,
            'by_interest' => $interests,
            'monthly' => array_reverse($monthly)
        ];
    }
    
    /**
     * Récupère tous les pays pour le formulaire
     */
    public function get_all_pays() {
        $this->db->order_by('pays', 'ASC');
        $query = $this->db->get('pays');
        return $query->result();
    }
    
    /**
     * Exporte les investisseurs en CSV
     */
    public function export_csv($filters = []) {
        $investors = $this->get_all_investors(null, 0, $filters);
        
        $data = [];
        foreach ($investors as $inv) {
            $interests = [];
            if ($inv->interest_equity) $interests[] = 'Equity';
            if ($inv->interest_debt) $interests[] = 'Dette';
            if ($inv->interest_blended_finance) $interests[] = 'Finance mixte';
            if ($inv->interest_grant) $interests[] = 'Grant';
            if ($inv->interest_strategic_partnership) $interests[] = 'Partenariat';
            
            $focus = [];
            if ($inv->focus_research_lab) $focus[] = 'Labo recherche';
            if ($inv->focus_gmp_facility) $focus[] = 'Facility GMP';
            if ($inv->focus_medicinal_plant) $focus[] = 'Plantes médicinales';
            if ($inv->focus_commercialization) $focus[] = 'Commercialisation';
            if ($inv->focus_full_platform) $focus[] = 'Plateforme complète';
            
            $data[] = [
                'ID' => $inv->id,
                'Nom complet' => $inv->full_name,
                'Organisation' => $inv->organization,
                'Poste' => $inv->position_title,
                'Pays' => $inv->country_name ?? '',
                'Email' => $inv->email,
                'Téléphone' => $inv->phone,
                'Types d\'intérêt' => implode(', ', $interests),
                'Autre intérêt' => $inv->interest_other,
                'Montant' => $inv->commitment_range,
                'Segments d\'intérêt' => implode(', ', $focus),
                'Horizon' => $inv->timeline,
                'Message' => $inv->strategic_message,
                'Date' => date('d/m/Y H:i', strtotime($inv->created_at))
            ];
        }
        
        return $data;
    }
    
    /**
     * Récupère les investisseurs pour le dashboard admin
     */
    public function get_recent_investors($limit = 10) {
        $this->db->select('investors.*, pays.pays as country_name');
        $this->db->from($this->table);
        $this->db->join('pays', 'pays.id = investors.id_pays', 'left');
        $this->db->order_by('investors.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
?>