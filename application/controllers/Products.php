<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller {

    function __construct()
    {
        parent::__construct();
    }
    
    public function detail($slug)
    {
        // Chercher par slug ou par ID
        $product = $this->Model->readOne('advertise_product', ['slug' => $slug]);
        
        if (empty($product)) {
            // Essayer par ID si slug non trouvé
            $product = $this->Model->readOne('advertise_product', ['id' => $slug]);
        }
        
        if (empty($product)) {
            show_404();
        }
        
        $data['product'] = $product;
        $this->load->view('sections/ProductDetail_View', $data);
    }
}  