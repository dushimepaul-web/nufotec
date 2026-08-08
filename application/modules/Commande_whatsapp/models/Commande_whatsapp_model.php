<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commande_whatsapp_model extends CI_Model {

    protected $table = 'order_requests';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Liste des commandes avec les infos produit
     */
    public function get_orders($status = null, $limit = null, $offset = null)
    {
        $this->db->select('o.*, p.title as product_name, p.main_image');
        $this->db->from($this->table . ' o');
        $this->db->join('advertise_product p', 'o.product_id = p.id', 'left');
        if (!empty($status)) {
            $this->db->where('o.order_status', $status);
        }
        $this->db->order_by('o.created_at', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result_array();
    }

    /**
     * Commande par ID avec les infos produit
     */
    public function get_order($id)
    {
        $this->db->select('o.*, p.title as product_name, p.main_image, p.slug');
        $this->db->from($this->table . ' o');
        $this->db->join('advertise_product p', 'o.product_id = p.id', 'left');
        $this->db->where('o.id', $id);
        return $this->db->get()->row_array();
    }

    /**
     * Recherche de commandes (client, téléphone, produit)
     */
    public function search_orders($q)
    {
        $this->db->select('o.*, p.title as product_name, p.main_image');
        $this->db->from($this->table . ' o');
        $this->db->join('advertise_product p', 'o.product_id = p.id', 'left');
        $this->db->group_start();
        $this->db->like('o.customer_name', $q);
        $this->db->or_like('o.customer_phone', $q);
        $this->db->or_like('o.customer_city', $q);
        $this->db->or_like('o.product_title', $q);
        $this->db->group_end();
        $this->db->order_by('o.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Statistiques globales des commandes
     */
    public function get_stats()
    {
        $row = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN order_status='pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN order_status='processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN order_status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN order_status='cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN DATE(created_at)=CURDATE() THEN 1 ELSE 0 END) as today
            FROM {$this->table}
        ")->row_array();

        return array(
            'total'      => (int) $row['total'],
            'pending'    => (int) $row['pending'],
            'processing' => (int) $row['processing'],
            'completed'  => (int) $row['completed'],
            'cancelled'  => (int) $row['cancelled'],
            'today'      => (int) $row['today'],
        );
    }

    /**
     * Nombre de commandes par statut
     */
    public function count_by_status($status)
    {
        return (int) $this->db->where('order_status', $status)->count_all_results($this->table);
    }

    /**
     * Mise à jour du statut d'une commande
     */
    public function update_status($id, $status)
    {
        return $this->db->where('id', $id)->update($this->table, array(
            'order_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Suppression d'une commande
     */
    public function delete_order($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Commandes par jour (pour le graphique)
     */
    public function get_daily_orders($days = 30)
    {
        $this->db->select('DATE(created_at) as date, COUNT(*) as count');
        $this->db->from($this->table);
        $this->db->where('created_at >=', date('Y-m-d', strtotime('-'.$days.' days')));
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('date', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Produits les plus demandés
     */
    public function get_top_products($limit = 10)
    {
        $this->db->select('p.id, p.title, p.price_request_count, COUNT(o.id) as total_orders');
        $this->db->from('advertise_product p');
        $this->db->join($this->table . ' o', 'p.id = o.product_id', 'left');
        $this->db->group_by('p.id');
        $this->db->order_by('p.price_request_count', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }
}