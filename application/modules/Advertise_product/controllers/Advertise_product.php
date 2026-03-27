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
        $data['products'] = $this->Model->read('advertise_product', null, 'id', 'DESC');
        $this->load->view('AdvertiseProduct_View', $data);
    }
    
    // Générer un slug unique
    private function generate_slug($title, $id = null)
    {
        $slug = url_title($title, '-', true);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        
        // Vérifier si le slug existe déjà
        $where = ['slug' => $slug];
        if ($id) {
            $where['id !='] = $id;
        }
        
        $existing = $this->Model->readOne('advertise_product', $where);
        
        if ($existing) {
            // Ajouter un suffixe numérique
            $suffix = 1;
            while ($this->Model->readOne('advertise_product', ['slug' => $slug . '-' . $suffix])) {
                $suffix++;
            }
            $slug = $slug . '-' . $suffix;
        }
        
        return $slug;
    }

    public function Create()
    {
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
        
        $main_image = 'default-product.png';
        if (!empty($_FILES['main_image']['name'])) {
            $main_image = $this->upload_image($_FILES['main_image']['tmp_name'], $_FILES['main_image']['name']);
            if ($main_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide');
                redirect(base_url('advertise-product'));
                return;
            }
        }

        $data = array(
            'main_image' => $main_image,
            'title' => $title,
            'slug' => $slug,
            'description' => $this->input->post('description'),
            'price' => $this->input->post('price'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('advertise_product', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Produit créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('advertise-product'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        
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

        $data = array(
            'title' => $title,
            'slug' => $slug,
            'description' => $this->input->post('description'),
            'price' => $this->input->post('price'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        if (!empty($_FILES['main_image']['name'])) {
            $new_image = $this->upload_image($_FILES['main_image']['tmp_name'], $_FILES['main_image']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide');
                redirect(base_url('advertise-product'));
                return;
            }
            
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
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('advertise-product'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $product = $this->Model->readOne('advertise_product', ['id' => $id]);
        $rsp = $this->Model->delete('advertise_product', ['id' => $id]);

        if ($rsp) {
            if ($product && $product['main_image'] != 'default-product.png' && file_exists(FCPATH . 'attachments/Products/' . $product['main_image'])) {
                unlink(FCPATH . 'attachments/Products/' . $product['main_image']);
            }
            $this->session->set_flashdata('success', 'Produit supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('advertise-product'));
    }

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