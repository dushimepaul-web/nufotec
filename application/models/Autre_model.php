<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre_model extends CI_Model {

    protected $table = 'galerie_medias';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Récupérer tous les médias de type 'autre' (exclut video, audio)
     */
    public function get_all($limit = null, $offset = 0) {
        $this->db->where('type', 'autre');
        $this->db->where('type !=', 'video');
        $this->db->where('type !=', 'audio');
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Récupérer un média par son ID (uniquement type autre)
     */
    public function get_by_id($id) {
        return $this->db->where('id_media', $id)
                        ->where('type', 'autre')
                        ->where('type !=', 'video')
                        ->where('type !=', 'audio')
                        ->get($this->table)
                        ->row();
    }

    /**
     * Récupérer les médias par sous_type (uniquement type autre)
     */
    public function get_by_sous_type($sous_type, $limit = null, $offset = 0) {
        $this->db->where('type', 'autre')
                 ->where('sous_type', $sous_type)
                 ->where('type !=', 'video')
                 ->where('type !=', 'audio')
                 ->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Compter tous les médias de type 'autre'
     */
    public function count_all() {
        return $this->db->where('type', 'autre')
                        ->where('type !=', 'video')
                        ->where('type !=', 'audio')
                        ->count_all_results($this->table);
    }

    /**
     * Compter les médias par sous_type
     */
    public function count_by_sous_type($sous_type) {
        return $this->db->where('type', 'autre')
                        ->where('sous_type', $sous_type)
                        ->where('type !=', 'video')
                        ->where('type !=', 'audio')
                        ->count_all_results($this->table);
    }

    /**
     * Insérer un nouveau média
     */
    public function insert($data) {
        $data['type'] = 'autre';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Mettre à jour un média
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id_media', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Supprimer un média
     */
    public function delete($id) {
        return $this->db->delete($this->table, ['id_media' => $id]);
    }

    /**
     * Générer un slug unique
     */
    public function generate_slug($titre, $id = null) {
        $slug = url_title($titre, 'dash', TRUE);
        $original_slug = $slug;
        $count = 1;
        
        while ($this->_slug_exists($slug, $id)) {
            $slug = $original_slug . '-' . $count;
            $count++;
        }
        
        return $slug;
    }

    /**
     * Vérifier si un slug existe déjà
     */
    private function _slug_exists($slug, $id = null) {
        $this->db->where('slug', $slug);
        if ($id) {
            $this->db->where('id_media !=', $id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    /**
     * Obtenir les statistiques par sous_type
     */
    public function get_stats_by_sous_type() {
        $this->db->select('sous_type, COUNT(*) as total');
        $this->db->where('type', 'autre');
        $this->db->where('type !=', 'video');
        $this->db->where('type !=', 'audio');
        $this->db->group_by('sous_type');
        return $this->db->get($this->table)->result();
    }
}