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
    
    // Charger les produits similaires si nécessaire
    $similar_products = []; // Récupérez vos produits similaires ici
    
    $data['product'] = $product;
    $data['similar_products'] = $similar_products;
    
    $this->load->view('sections/ProductDetail_View', $data);
}
}  