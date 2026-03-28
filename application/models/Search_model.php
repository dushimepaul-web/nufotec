<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function search_all($term) {
        return [
            'produits' => $this->_search_produits($term)
            //'pages'    => //$this->_search_pages($term)
        ];
    }

    private function _search_produits($term) {
    $this->db->select('id, title as titre, description as extrait, "produit" as type, slug');

    $this->db->from('advertise_product');

    $this->db->group_start();
        $this->db->like('title', $term);
        $this->db->or_like('description', $term);
    $this->db->group_end();

    // Optionnel : seulement les produits actifs
    $this->db->where('is_active', 1);

    $this->db->limit(10);

    $query = $this->db->get();

    return $query->num_rows() > 0 ? $query->result_array() : [];
}

    private function _search_pages($term) {
        $this->db->select('id_page as id, titre_page as titre, meta_description as extrait, "page" as type, slug');
        $this->db->group_start();
        $this->db->like('titre_page', $term);
        $this->db->or_like('meta_description', $term);
        $this->db->group_end();
        $this->db->limit(10);
        $query = $this->db->get('pages');
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }
}