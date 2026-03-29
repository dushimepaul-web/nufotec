<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Groupe_model extends CI_Model {
    
    private $table = 'groupes_whatsapp';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->creer_table();
    }
    
    private function creer_table() {
        $this->db->query("CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            groupe_id VARCHAR(100) NOT NULL,
            nom VARCHAR(255),
            description TEXT,
            actif TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY groupe_id (groupe_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    
    
    
    /**
     * Récupère tous les groupes actifs avec ID et nom
     */
    public function get_all_groupes() {
        $this->db->where('actif', 1);
        $this->db->order_by('nom', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    
    /**
     * Récupère les N premiers groupes (ex: 10)
     */
    public function get_groupes_limit($limit = 10) {
        $this->db->where('actif', 1);
        $this->db->limit($limit);
        $this->db->order_by('nom', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    
    /**
     * Récupère les IDs des groupes actifs
     */
    public function get_ids_groupes() {
        $this->db->select('groupe_id, nom');
        $this->db->where('actif', 1);
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    
    /**
     * Récupère un groupe par son nom (recherche)
     */
    public function get_groupe_par_nom($nom) {
        $this->db->like('nom', $nom);
        $this->db->where('actif', 1);
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    
    /**
     * Récupère un groupe par son ID WhatsApp
     */
    public function get_groupe_par_id_whatsapp($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        $query = $this->db->get($this->table);
        return $query->row_array();
    }
    
    /**
     * Met à jour le nom d'un groupe
     */
    public function update_nom($groupe_id, $nouveau_nom) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->update($this->table, array('nom' => $nouveau_nom));
    }
    
    /**
     * Active/désactive un groupe
     */
    public function set_actif($groupe_id, $actif = true) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->update($this->table, array('actif' => $actif ? 1 : 0));
    }
    
    /**
     * Supprime un groupe (désactive seulement)
     */
    public function supprimer($groupe_id) {
        $this->db->where('groupe_id', $groupe_id);
        $this->db->update($this->table, array('actif' => 0));
    }
    
    /**
     * Compte le nombre de groupes actifs
     */
    public function compter_groupes() {
        $this->db->where('actif', 1);
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Récupère les groupes avec pagination
     */
    public function get_groupes_pagines($limit, $offset) {
        $this->db->where('actif', 1);
        $this->db->order_by('nom', 'ASC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    public function sauvegarder($groupe_id, $nom, $description = '') {
    $data = array(
        'groupe_id' => $groupe_id,
        'nom' => $nom,
        'description' => $description,
        'actif' => 1
    );
    
    $this->db->replace($this->table, $data);
    return $this->db->insert_id();
}
}