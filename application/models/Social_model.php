<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Social_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Récupérer tous les liens sociaux actifs
     */
    public function read_active()
    {
        return $this->db->where('is_active', 1)
                       ->order_by('display_order', 'ASC')
                       ->get('social_links')
                       ->result_array();
    }

    /**
     * Récupérer tous les liens (admin)
     */
    public function read_all($where = [], $order_by = 'display_order', $order = 'ASC')
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->order_by($order_by, $order)
                       ->get('social_links')
                       ->result_array();
    }

    /**
     * Récupérer un lien par ID
     */
    public function read_one($where)
    {
        return $this->db->where($where)
                       ->get('social_links')
                       ->row_array();
    }

    /**
     * Créer un lien social
     */
    public function create($data)
    {
        return $this->db->insert('social_links', $data);
    }

    /**
     * Mettre à jour un lien social
     */
    public function update($where, $data)
    {
        return $this->db->where($where)->update('social_links', $data);
    }

    /**
     * Supprimer un lien social
     */
    public function delete($where)
    {
        return $this->db->where($where)->delete('social_links');
    }

    /**
     * Changer le statut actif/inactif
     */
    public function change_status($id, $is_active)
    {
        $status = ($is_active == 1) ? 0 : 1;
        return $this->db->where('id', $id)
                       ->update('social_links', ['is_active' => $status]);
    }
}