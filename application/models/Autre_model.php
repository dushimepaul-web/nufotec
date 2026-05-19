<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre_model extends CI_Model {

    protected $table = 'galerie_medias';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Récupérer tous les médias de type 'document', 'image' et 'autre' avec sous_type document/image
     */
    public function get_all($limit = null, $offset = 0) {
        $this->db->group_start();
            // Cas 1: type = 'document' avec sous_type NULL
            $this->db->where('type', 'document');
            $this->db->where('sous_type IS NULL');
        $this->db->or_group_start();
            // Cas 2: type = 'image' avec sous_type NULL
            $this->db->where('type', 'image');
            $this->db->where('sous_type IS NULL');
        $this->db->or_group_start();
            // Cas 3: type = 'autre' ET sous_type IN ('document', 'image')
            $this->db->where('type', 'autre');
            $this->db->where_in('sous_type', ['document', 'image']);
        $this->db->group_end();
        $this->db->group_end();
        $this->db->group_end();
        
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Récupérer un média par son ID
     */
    public function get_by_id($id) {
        $this->db->group_start();
            $this->db->where('type', 'document');
            $this->db->where('sous_type IS NULL');
        $this->db->or_group_start();
            $this->db->where('type', 'image');
            $this->db->where('sous_type IS NULL');
        $this->db->or_group_start();
            $this->db->where('type', 'autre');
            $this->db->where_in('sous_type', ['document', 'image']);
        $this->db->group_end();
        $this->db->group_end();
        $this->db->group_end();
        $this->db->where('id_media', $id);
        
        return $this->db->get($this->table)->row();
    }

    /**
     * Récupérer les médias par sous_type
     */
    public function get_by_sous_type($sous_type = null, $limit = null, $offset = 0) {
        $this->db->group_start();
            // Cas 1: type = 'document' avec sous_type NULL ET sous_type demandé est 'document'
            if ($sous_type == 'document') {
                $this->db->where('type', 'document');
                $this->db->where('sous_type IS NULL');
            }
        $this->db->or_group_start();
            // Cas 2: type = 'image' avec sous_type NULL ET sous_type demandé est 'image'
            if ($sous_type == 'image') {
                $this->db->where('type', 'image');
                $this->db->where('sous_type IS NULL');
            }
        $this->db->or_group_start();
            // Cas 3: type = 'autre' ET sous_type = la valeur demandée
            if ($sous_type && in_array($sous_type, ['document', 'image', 'photo', 'book', 'texte', 'link', 'other'])) {
                $this->db->where('type', 'autre');
                $this->db->where('sous_type', $sous_type);
            }
        $this->db->group_end();
        $this->db->group_end();
        $this->db->group_end();
        
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Compter tous les médias
     */
    public function count_all() {
        $this->db->group_start();
            $this->db->where('type', 'document');
            $this->db->where('sous_type IS NULL');
        $this->db->or_group_start();
            $this->db->where('type', 'image');
            $this->db->where('sous_type IS NULL');
        $this->db->or_group_start();
            $this->db->where('type', 'autre');
            $this->db->where_in('sous_type', ['document', 'image']);
        $this->db->group_end();
        $this->db->group_end();
        $this->db->group_end();
        
        return $this->db->count_all_results($this->table);
    }

    /**
     * Compter les médias par sous_type
     */
    public function count_by_sous_type($sous_type = null) {
        $this->db->group_start();
            if ($sous_type == 'document') {
                $this->db->where('type', 'document');
                $this->db->where('sous_type IS NULL');
            }
        $this->db->or_group_start();
            if ($sous_type == 'image') {
                $this->db->where('type', 'image');
                $this->db->where('sous_type IS NULL');
            }
        $this->db->or_group_start();
            if ($sous_type && in_array($sous_type, ['document', 'image', 'photo', 'book', 'texte', 'link', 'other'])) {
                $this->db->where('type', 'autre');
                $this->db->where('sous_type', $sous_type);
            }
        $this->db->group_end();
        $this->db->group_end();
        $this->db->group_end();
        
        return $this->db->count_all_results($this->table);
    }

    /**
     * Insérer un nouveau média
     */
    public function insert($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Si le type est 'document' ou 'image' sans sous_type, on garde le type original
        if (!isset($data['type'])) {
            $data['type'] = 'autre';
        }
        
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
        // Cette méthode est complexe, on va faire des requêtes séparées
        $stats = [];
        
        // Pour 'document' (type=document, sous_type=NULL)
        $this->db->where('type', 'document');
        $this->db->where('sous_type IS NULL');
        $stats['document'] = $this->db->count_all_results($this->table);
        
        // Pour 'image' (type=image, sous_type=NULL)
        $this->db->where('type', 'image');
        $this->db->where('sous_type IS NULL');
        $stats['image'] = $this->db->count_all_results($this->table);
        
        // Pour les autres sous_types de 'autre'
        $sous_types = ['photo', 'book', 'texte', 'link', 'other'];
        foreach ($sous_types as $st) {
            $this->db->where('type', 'autre');
            $this->db->where('sous_type', $st);
            $stats[$st] = $this->db->count_all_results($this->table);
        }
        
        return $stats;
    }
}