<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advertise_product extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer tous les produits
        $data['products'] = $this->Model->read('advertise_product', null, 'id', 'DESC');
        $data['categories'] = $this->Model->read('product_categories', null, 'id', 'ASC');
        $this->load->view('AdvertiseProduct_View', $data);
    }
    
    function ChangeStatus()
    {
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        $status = ($is_active == 1) ? 0 : 1;
        $rsp = $this->Model->update('advertise_product', ['id' => $id], ['is_active' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('advertise-product'));    
    }
    
    function ChangeFeatured()
    {
        $id = $this->input->post('id');
        $in_vedette = $this->input->post('in_vedette');
        
        $status = ($in_vedette == 1) ? 0 : 1;
        $rsp = $this->Model->update('advertise_product', ['id' => $id], ['in_vedette' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut vedette mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut vedette.');
        }
        redirect(base_url('advertise-product'));    
    }
    
    function ProductDetail($productDetail)
    {
        $id = explode('_', $productDetail);
        $data['detail'] = $this->Model->readOne('advertise_product', ['id' => $id[0]]);
        $data['categories'] = $this->Model->read('product_categories', null, 'id');
        $this->load->view('AdvertiseProductDetail_View', $data);
    }
    
   // Générer un slug unique et sécurisé (version avec CI)
private function generate_slug($title, $id = null)
{
    // Utiliser la fonction native de CodeIgniter pour le slug
    $slug = url_title($title, '-', true);
    
    // Nettoyer davantage : ne garder que lettres, chiffres et tirets
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    
    // Si le slug est vide après nettoyage, créer un slug basé sur l'ID
    if (empty($slug)) {
        $slug = 'produit-' . uniqid();
    }
    
    // Limiter la longueur du slug (max 100 caractères)
    $slug = substr($slug, 0, 100);
    
    // Vérifier l'unicité
    $where = ['slug' => $slug];
    if ($id) {
        $where['id !='] = $id;
    }
    
    $existing = $this->Model->readOne('advertise_product', $where);
    
    if ($existing) {
        // Ajouter un suffixe numérique incrémental
        $suffix = 1;
        $base_slug = $slug;
        
        while ($this->Model->readOne('advertise_product', ['slug' => $base_slug . '-' . $suffix])) {
            $suffix++;
        }
        $slug = $base_slug . '-' . $suffix;
    }
    
    return $slug;
}

    function Create()
    {
        // Validation des champs requis
        $this->form_validation->set_rules('title', 'Titre', 'required');
        $this->form_validation->set_rules('price', 'Prix', 'required');
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('advertise-product'));
            return;
        }

        $title = $this->input->post('title');
        $slug = $this->generate_slug($title);
        $category_id = $this->input->post('category_id') ?: NULL;
        
        // Upload image principale
        $main_image = 'default-product.png';
        if (!empty($_FILES['main_image']['name'])) {
            $main_image = $this->upload_image($_FILES['main_image']['tmp_name'], $_FILES['main_image']['name']);
            if ($main_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('advertise-product'));
                return;
            }
        } else {
            $this->session->set_flashdata('error', 'L\'image principale est requise.');
            redirect(base_url('advertise-product'));
            return;
        }

        $data = array(
            'category_id' => $category_id,
            'main_image' => $main_image,
            'title' => $title,
            'slug' => $slug,
            'description' => $this->input->post('description'),
            'price' => $this->input->post('price'),
            'is_active' => 1,
            'in_vedette' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('advertise_product', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Produit créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création du produit.');
        }
        redirect(base_url('advertise-product'));
    }

    function Update()
    {
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('title', 'Titre', 'required');
        $this->form_validation->set_rules('price', 'Prix', 'required');
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('advertise-product'));
            return;
        }

        $title = $this->input->post('title');
        $slug = $this->generate_slug($title, $id);
        $category_id = $this->input->post('category_id') ?: NULL;
        $is_active = $this->input->post('is_active') ? 1 : 0;
        $in_vedette = $this->input->post('in_vedette') ? 1 : 0;

        $data = array(
            'category_id' => $category_id,
            'title' => $title,
            'slug' => $slug,
            'description' => $this->input->post('description'),
            'price' => $this->input->post('price'),
            'is_active' => $is_active,
            'in_vedette' => $in_vedette,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Upload nouvelle image si fournie
        if (!empty($_FILES['main_image']['name'])) {
            $new_image = $this->upload_image($_FILES['main_image']['tmp_name'], $_FILES['main_image']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('advertise-product'));
                return;
            }
            
            // Supprimer l'ancienne image si ce n'est pas l'image par défaut
            $product = $this->Model->readOne('advertise_product', ['id' => $id]);
            if ($product && $product['main_image'] != 'default-product.png' && file_exists(FCPATH . 'attachments/Products/' . $product['main_image'])) {
                unlink(FCPATH . 'attachments/Products/' . $product['main_image']);
            }
            
            $data['main_image'] = $new_image;
        }

        $rsp = $this->Model->update('advertise_product', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Produit mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('advertise-product'));
    }

    function Delete()
    {
        $id = $this->input->post('id');
        
        // Récupérer le produit pour supprimer son image
        $product = $this->Model->readOne('advertise_product', ['id' => $id]);
        
        $rsp = $this->Model->delete('advertise_product', ['id' => $id]);

        if ($rsp) {
            // Supprimer l'image physique si ce n'est pas l'image par défaut
            if ($product && $product['main_image'] != 'default-product.png' && file_exists(FCPATH . 'attachments/Products/' . $product['main_image'])) {
                unlink(FCPATH . 'attachments/Products/' . $product['main_image']);
            }
            $this->session->set_flashdata('success', 'Produit supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('advertise-product'));
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Products/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }
}