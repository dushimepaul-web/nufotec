<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_categories extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer toutes les catégories
        $data['product_categories'] = $this->Model->read('product_categories', null, 'id', 'ASC');
        $this->load->view('Product_Categories_View', $data);
    }

    function create()
    {
        // Validation des champs requis
        $this->form_validation->set_rules('name', 'Nom de la catégorie', 'required|is_unique[product_categories.name]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('product_categories'));
            return;
        }

        $name = $this->input->post('name');

        $data = array(
            'name' => $name
        );
        
        $rsp = $this->Model->create('product_categories', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Catégorie créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de la catégorie.');
        }
        redirect(base_url('product_categories'));
    }

    function update()
    {
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('name', 'Nom de la catégorie', 'required');
        
        // Vérifier si le nom existe déjà (exclure l'ID actuel)
        $existing = $this->Model->readOne('product_categories', ['name' => $this->input->post('name'), 'id !=' => $id]);
        if ($existing) {
            $this->session->set_flashdata('error', 'Ce nom de catégorie existe déjà.');
            redirect(base_url('product_categories'));
            return;
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('product_categories'));
            return;
        }

        $name = $this->input->post('name');

        $data = array(
            'name' => $name
        );

        $rsp = $this->Model->update('product_categories', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Catégorie mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('product_categories'));
    }

    function delete()
    {
        $id = $this->input->post('id');
        
        // Vérifier s'il y a des produits dans cette catégorie
        $products = $this->Model->read('advertise_product', ['category_id' => $id]);
        if (!empty($products)) {
            $this->session->set_flashdata('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
            redirect(base_url('product_categories'));
            return;
        }
        
        $rsp = $this->Model->delete('product_categories', ['id' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Catégorie supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('product_categories'));
    }

    function view($id)
    {
        $data['detail'] = $this->Model->readOne('product_categories', ['id' => $id]);
        $this->load->view('ProductCategoryDetail_View', $data);
    }
}