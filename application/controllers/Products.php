<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Public_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->library('user_agent');
    }
    
    public function detail($slug)
    {
        // Utilisation directe des champs sans traduction
        $this->db->select("id, main_image, slug, price, category_id, created_at, title, description");
        $this->db->where('slug', $slug);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at IS NULL');
        $product = $this->db->get('advertise_product')->row_array();

        // Si pas trouvé par slug, essayer par ID
        if (empty($product) && is_numeric($slug)) {
            $this->db->select("id, main_image, slug, price, category_id, created_at, title, description");
            $this->db->where('id', $slug);
            $this->db->where('is_active', 1);
            $this->db->where('deleted_at IS NULL');
            $product = $this->db->get('advertise_product')->row_array();
        }

        if (empty($product)) {
            show_404();
        }

        // Récupérer les produits similaires (même catégorie)
        $similar_products = [];
        if (!empty($product['category_id'])) {
            $this->db->select("id, main_image, slug, price, title, description");
            $this->db->where('category_id', $product['category_id']);
            $this->db->where('is_active', 1);
            $this->db->where('id !=', $product['id']);
            $this->db->where('deleted_at IS NULL');
            $this->db->limit(4);
            $similar_products = $this->db->get('advertise_product')->result_array();
        }

        $data['product'] = $product;
        $data['similar_products'] = $similar_products;

        $this->load->view('sections/ProductDetail_View', $data);
    }

    public function index()
    {
        // Catégories
        $data['categories'] = $this->Model->read('product_categories', null, 'name', 'ASC');

        // Produits avec champs directs
        $this->db->select("id, main_image, slug, price, created_at, title, description");
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at IS NULL');
        $this->db->order_by('id', 'DESC');
        $data['products'] = $this->db->get('advertise_product')->result_array();

        $data['title'] = 'Nos Produits';
        $this->load->view('sections/Produits_View', $data);
    }

    public function get_products_ajax()
    {
        $category_id = $this->input->get('category');
        $search = $this->input->get('search');
        $page = (int) $this->input->get('page') ?: 1;
        $per_page = (int) $this->input->get('per_page') ?: 12;
        $offset = ($page - 1) * $per_page;

        // Requête de base avec champs directs
        $this->db->select("id, main_image, slug, price, created_at, title, description");
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at IS NULL');
        
        if (!empty($category_id) && $category_id != 'all') {
            $this->db->where('category_id', $category_id);
        }

        // Appliquer le filtre de recherche
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }

        // Pagination
        $total_products = $this->db->count_all_results('advertise_product', FALSE);
        
        $this->db->limit($per_page, $offset);
        $this->db->order_by('id', 'DESC');
        $products_db = $this->db->get()->result_array();

        $products = [];
        foreach ($products_db as $p) {
            $image_path = !empty($p['main_image']) ? base_url('attachments/Products/' . $p['main_image']) : base_url('attachments/Products/default-product.png');
            $products[] = [
                'id' => $p['id'],
                'title' => $p['title'],
                'price' => $p['price'],
                'image' => $image_path,
                'slug' => $p['slug'],
                'in_vedette' => $p['in_vedette'] ?? 0,
                'description' => substr(strip_tags($p['description']), 0, 100) . '...'
            ];
        }

        $total_pages = ceil($total_products / $per_page);

        $response = [
            'products' => $products,
            'total_products' => $total_products,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];

        echo json_encode($response);
    }
    
    /**
     * API: Enregistrer une demande de commande
     */
    public function save_order_request()
    {
        // Vérifier si c'est une requête AJAX
        if (!$this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Requête non autorisée']));
            return;
        }
        
        // Récupérer les données POST
        $product_id = $this->input->post('product_id');
        $customer_name = trim($this->input->post('customer_name'));
        $customer_phone = trim($this->input->post('customer_phone'));
        $customer_country = trim($this->input->post('customer_country'));
        $customer_city = trim($this->input->post('customer_city'));
        $customer_address = trim($this->input->post('customer_address'));
        $customer_notes = trim($this->input->post('customer_notes'));
        $product_title = trim($this->input->post('product_title'));
        $product_price = trim($this->input->post('product_price'));
        
        // Validation
        if (empty($product_id) || empty($customer_name) || empty($customer_phone) || 
            empty($customer_country) || empty($customer_city) || empty($customer_address)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']));
            return;
        }
        
        // Vérifier si le produit existe
        $product = $this->Model->readOne('advertise_product', ['id' => $product_id]);
        if (empty($product)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Produit non trouvé']));
            return;
        }
        
        // Préparer les données pour la table order_requests
        $order_data = [
            'product_id' => $product_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_country' => $customer_country,
            'customer_city' => $customer_city,
            'customer_address' => $customer_address,
            'customer_notes' => $customer_notes,
            'product_title' => $product_title,
            'product_price' => $product_price,
            'order_status' => 'pending',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->agent->agent_string()
        ];
        
        // Insérer dans la base de données
        $order_id = $this->Model->create('order_requests', $order_data);
        
        if ($order_id) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true, 
                    'order_id' => $order_id,
                    'message' => 'Demande enregistrée avec succès'
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']));
        }
    }
    
    /**
     * ADMIN: Liste des commandes (pour le backoffice)
     */
    public function admin_orders()
    {
        is_admin();
        
        // Récupérer toutes les commandes avec les infos produits
        $this->db->select('o.*, p.title as product_name, p.main_image');
        $this->db->from('order_requests o');
        $this->db->join('advertise_product p', 'o.product_id = p.id', 'left');
        $this->db->order_by('o.created_at', 'DESC');
        $orders = $this->db->get()->result_array();
        
        // === AJOUTER LES STATISTIQUES ===
        $total_orders = $this->Model->count('order_requests');
        $pending_orders = $this->Model->count('order_requests', ['order_status' => 'pending']);
        $processing_orders = $this->Model->count('order_requests', ['order_status' => 'processing']);
        $completed_orders = $this->Model->count('order_requests', ['order_status' => 'completed']);
        $cancelled_orders = $this->Model->count('order_requests', ['order_status' => 'cancelled']);
        
        $data['orders'] = $orders;
        $data['total_orders'] = $total_orders;
        $data['pending_orders'] = $pending_orders;
        $data['processing_orders'] = $processing_orders;
        $data['completed_orders'] = $completed_orders;
        $data['cancelled_orders'] = $cancelled_orders;
        $data['title'] = 'Gestion des commandes';
        
        $this->load->view('admin/orders_list', $data);
    }
    
    /**
     * ADMIN: Mettre à jour le statut d'une commande
     */
    public function update_order_status()
    {    
        is_admin();
        // Forcer l'en-tête JSON
        $this->output->set_content_type('application/json');
        
        // Vérifier si c'est une requête AJAX
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $status = $this->input->post('status');
        
        // Statuts autorisés
        $allowed_status = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $allowed_status)) {
            echo json_encode(['success' => false, 'message' => 'Statut invalide']);
            return;
        }
        
        // Mettre à jour le statut
        $this->db->where('id', $order_id);
        $updated = $this->db->update('order_requests', [
            'order_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($updated) {
            echo json_encode([
                'success' => true, 
                'message' => 'Statut mis à jour avec succès',
                'new_status' => $status
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }
    
    /**
     * ADMIN: Voir les statistiques des demandes
     */
    public function admin_stats()
    {
        is_admin();
        
        // Statistiques globales
        $total_orders = $this->Model->count('order_requests');
        $pending_orders = $this->Model->count('order_requests', ['order_status' => 'pending']);
        $completed_orders = $this->Model->count('order_requests', ['order_status' => 'completed']);
        
        // Produits les plus demandés
        $this->db->select('p.id, p.title, p.price_request_count, COUNT(o.id) as total_orders');
        $this->db->from('advertise_product p');
        $this->db->join('order_requests o', 'p.id = o.product_id', 'left');
        $this->db->group_by('p.id');
        $this->db->order_by('p.price_request_count', 'DESC');
        $this->db->limit(10);
        $top_products = $this->db->get()->result_array();
        
        // Commandes par jour (30 derniers jours)
        $this->db->select('DATE(created_at) as date, COUNT(*) as count');
        $this->db->from('order_requests');
        $this->db->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('date', 'ASC');
        $daily_orders = $this->db->get()->result_array();
        
        $data = [
            'total_orders' => $total_orders,
            'pending_orders' => $pending_orders,
            'completed_orders' => $completed_orders,
            'top_products' => $top_products,
            'daily_orders' => $daily_orders,
            'title' => 'Statistiques des commandes'
        ];
        
        $this->load->view('admin/orders_stats', $data);
    }

    /**
     * API: Incrémenter le compteur de demande de prix
     */
    public function increment_price_request()
    {
        // Désactiver l'affichage des erreurs pour cette méthode
        error_reporting(0);
        
        // Forcer l'en-tête JSON
        header('Content-Type: application/json');
        
        // Vérifier la requête
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        $product_id = $this->input->post('product_id');
        
        if (empty($product_id)) {
            echo json_encode(['success' => false, 'message' => 'ID produit requis']);
            return;
        }
        
        // Incrémenter le compteur
        $sql = "UPDATE advertise_product SET price_request_count = price_request_count + 1 WHERE id = ?";
        $this->db->query($sql, array($product_id));
        
        if ($this->db->affected_rows() > 0) {
            $new_count = $this->db->query("SELECT price_request_count FROM advertise_product WHERE id = ?", array($product_id))->row()->price_request_count;
            echo json_encode([
                'success' => true,
                'product_id' => $product_id,
                'new_count' => $new_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
        }
    }

    /**
     * ADMIN: Supprimer une commande
     */
    public function delete_order()
    {   
        is_admin();
        if (!$this->input->is_ajax_request()) show_404();
        
        $order_id = $this->input->post('order_id');
        $deleted = $this->Model->delete('order_requests', ['id' => $order_id]);
        
        echo json_encode(['success' => $deleted, 'message' => $deleted ? 'Commande supprimée' : 'Erreur de suppression']);
    }

    /**
     * ADMIN: Exporter les commandes en CSV
     */
    public function export_orders_csv()
    {    
        is_admin();
        $this->db->select('o.*, p.title as product_name');
        $this->db->from('order_requests o');
        $this->db->join('advertise_product p', 'o.product_id = p.id', 'left');
        $this->db->order_by('o.created_at', 'DESC');
        $orders = $this->db->get()->result_array();
        
        $filename = 'commandes_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'N° Commande', 'Produit', 'Client', 'Téléphone', 'Pays', 'Ville', 'Adresse', 'Montant', 'Statut', 'Date']);
        
        foreach ($orders as $order) {
            fputcsv($output, [
                $order['id'], 
                'CMD-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT), 
                $order['product_title'],
                $order['customer_name'], 
                $order['customer_phone'], 
                $order['customer_country'],
                $order['customer_city'], 
                $order['customer_address'], 
                $order['product_price'],
                $order['order_status'], 
                $order['created_at']
            ]);
        }
        fclose($output);
        exit;
    }
}