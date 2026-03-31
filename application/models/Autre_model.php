<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre_model extends CI_Model {

    protected $table = 'galerie_medias';

    public function __construct() {
        parent::__construct();
    }

    public function get_all($limit = null, $offset = 0) {
        $this->db->where('type', 'autre');
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id_media' => $id, 'type' => 'autre'])->row();
    }

    public function get_by_sous_type($sous_type, $limit = null, $offset = 0) {
        $this->db->where(['type' => 'autre', 'sous_type' => $sous_type]);
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table)->result();
    }

    public function count_all() {
        $this->db->where('type', 'autre');
        return $this->db->count_all_results($this->table);
    }

    public function insert($data) {
        $data['type'] = 'autre';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id_media', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        return $this->db->delete($this->table, ['id_media' => $id]);
    }

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

    private function _slug_exists($slug, $id = null) {
        $this->db->where('slug', $slug);
        if ($id) {
            $this->db->where('id_media !=', $id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }
}