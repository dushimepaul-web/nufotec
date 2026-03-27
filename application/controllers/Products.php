<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller {

    function __construct()
    {
        parent::__construct();
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
}