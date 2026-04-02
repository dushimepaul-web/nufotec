<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->library('user_agent');
    }
    
    public function detail($slug)
    {
        $product = $this->Model->readOne('advertise_product', ['slug' => $slug]);
        
        if (empty($product)) {
            $product = $this->Model->readOne('advertise_product', ['id' => $slug]);
        }
        
        if (empty($product)) {
            show_404();
        }
        
        // Récupérer les produits similaires (même catégorie)
        $similar_products = [];
        if (!empty($product['category_id'])) {
            $similar_products = $this->Model->read('advertise_product', 
                ['category_id' => $product['category_id'], 'is_active' => 1, 'id !=' => $product['id']], 
                'id', 'DESC', 4);
        }
        
        $data['product'] = $product;
        $data['similar_products'] = $similar_products;
        
        $this->load->view('sections/ProductDetail_View', $data);
    }
    
    public function index()
    {
        // Récupérer toutes les catégories pour le filtre
        $data['categories'] = $this->Model->read('product_categories', null, 'name', 'ASC');
        
        // Récupérer tous les produits actifs
        $data['products'] = $this->Model->read('advertise_product', ['is_active' => 1], 'id', 'DESC');
        
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
        
        // Construction de la requête WHERE
        $where = ['is_active' => 1];
        
        if (!empty($category_id) && $category_id != 'all') {
            $where['category_id'] = $category_id;
        }
        
        // Récupérer tous les produits correspondant aux critères
        $all_products = $this->Model->read('advertise_product', $where, 'id', 'DESC');
        
        // Appliquer le filtre de recherche
        if (!empty($search)) {
            $all_products = array_filter($all_products, function($product) use ($search) {
                return stripos($product['title'], $search) !== false || 
                       stripos($product['description'], $search) !== false;
            });
            // Réindexer le tableau
            $all_products = array_values($all_products);
        }
        
        $total_products = count($all_products);
        $total_pages = ceil($total_products / $per_page);
        
        // Pagination
        $products = array_slice($all_products, $offset, $per_page);
        
        // Formater les données pour la réponse AJAX
        $result = [];
        foreach ($products as $product) {
            $image_path = !empty($product['main_image']) ? base_url('attachments/Products/' . $product['main_image']) : base_url('attachments/Products/default-product.png');
            
            $result[] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $image_path,
                'slug' => $product['slug'],
                'in_vedette' => $product['in_vedette'] ?? 0,
                'description' => substr(strip_tags($product['description']), 0, 100) . '...'
            ];
        }
        
        $response = [
            'products' => $result,
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
        
        // Incrémenter le compteur de demandes de prix
        //$this->db->set('price_request_count', 'price_request_count + 1', FALSE);
        //$this->db->where('id', $product_id);
        //$this->db->update('advertise_product');
        
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
            'whatsapp_sent' => 0,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->agent->agent_string()
        ];
        
        // Insérer dans la base de données
        $order_id = $this->Model->create('order_requests', $order_data);
        
        if ($order_id) {
            // Journaliser l'envoi WhatsApp
            $whatsapp_data = [
                'order_request_id' => $order_id,
                'product_id' => $product_id,
                'phone_number' => $customer_phone,
                'message_type' => 'price_request',
                'status' => 'pending',
                'message_content' => 'Passage du commande sur le produit interesse #' . $product_id
            ];
            $this->Model->create('whatsapp_logs', $whatsapp_data);
            
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
        // Vérifier si l'utilisateur est admin
        if (!$this->session->userdata('is_admin')) {
            redirect('admin/login');
        }
        
        // Récupérer toutes les commandes avec les infos produits
        $this->db->select('o.*, p.title as product_name, p.main_image');
        $this->db->from('order_requests o');
        $this->db->join('advertise_product p', 'o.product_id = p.id', 'left');
        $this->db->order_by('o.created_at', 'DESC');
        $orders = $this->db->get()->result_array();
        
        $data['orders'] = $orders;
        $data['title'] = 'Gestion des commandes';
        $this->load->view('admin/orders_list', $data);
    }
    
    /**
     * ADMIN: Mettre à jour le statut d'une commande
     */
    public function update_order_status()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $order_id = $this->input->post('order_id');
        $status = $this->input->post('status');
        
        $allowed_status = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $allowed_status)) {
            echo json_encode(['success' => false, 'message' => 'Statut invalide']);
            return;
        }
        
        $updated = $this->Model->update('order_requests', ['order_status' => $status], ['id' => $order_id]);
        
        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
        }
    }
    
    /**
     * ADMIN: Voir les statistiques des demandes
     */
    public function admin_stats()
    {
        if (!$this->session->userdata('is_admin')) {
            redirect('admin/login');
        }
        
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
 * API: Incrémenter le compteur de demande de prix (pour le toast WhatsApp)
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
}