<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        $data['categories'] = $this->Model->read('categories', null, 'id_categorie', 'ASC');
        $this->load->view('Categories_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        // Note: La table categories n'a pas de champ is_active dans votre structure
        // Ajoutez-le si nécessaire ou adaptez cette fonction
        $status = ($is_active == 1) ? 0 : 1;
        $rsp = $this->Model->update('categories', ['id_categorie' => $id], ['is_active' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Categories'));    
    }

    function CategoryDetail($categoryDetail){
        $id = explode('_', $categoryDetail);
        $data['detail'] = $this->Model->readOne('categories', ['id_categorie' => $id[0]]);
        $this->load->view('CategoryDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('code_categorie', 'Code Catégorie', 'required|exact_length[1]|is_unique[categories.code_categorie]');
        $this->form_validation->set_rules('nom_categorie', 'Nom Catégorie', 'required|max_length[100]');
        $this->form_validation->set_rules('description_courte', 'Description Courte', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Categories'));
            return;
        }

        $code_categorie = strtoupper($this->input->post('code_categorie'));
        $nom_categorie = $this->input->post('nom_categorie');
        $description_courte = $this->input->post('description_courte');
        $description_longue = $this->input->post('description_longue') ?: NULL;
        $icone = $this->input->post('icone') ?: NULL;

        // Upload image si fournie
        $image_url = NULL;
        if (!empty($_FILES['image_url']['name'])) {
            $image_url = $this->upload_image($_FILES['image_url']['tmp_name'], $_FILES['image_url']['name']);
            if ($image_url === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Categories'));
                return;
            }
        }

        $data = array(
            'code_categorie' => $code_categorie,
            'nom_categorie' => $nom_categorie,
            'description_courte' => $description_courte,
            'description_longue' => $description_longue,
            'icone' => $icone,
            'image_url' => $image_url
        );
        
        $rsp = $this->Model->create('categories', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Catégorie créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de la catégorie.');
        }
        redirect(base_url('Categories'));
    }

    function Update(){
        $id = $this->input->post('id_categorie');
        
        // Validation
        $this->form_validation->set_rules('code_categorie', 'Code Catégorie', 'required|exact_length[1]');
        $this->form_validation->set_rules('nom_categorie', 'Nom Catégorie', 'required|max_length[100]');
        $this->form_validation->set_rules('description_courte', 'Description Courte', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Categories'));
            return;
        }

        $code_categorie = strtoupper($this->input->post('code_categorie'));
        $nom_categorie = $this->input->post('nom_categorie');
        $description_courte = $this->input->post('description_courte');
        $description_longue = $this->input->post('description_longue') ?: NULL;
        $icone = $this->input->post('icone') ?: NULL;

        $data = array(
            'code_categorie' => $code_categorie,
            'nom_categorie' => $nom_categorie,
            'description_courte' => $description_courte,
            'description_longue' => $description_longue,
            'icone' => $icone
        );

        // Upload nouvelle image si fournie
        if (!empty($_FILES['image_url']['name'])) {
            $new_image = $this->upload_image($_FILES['image_url']['tmp_name'], $_FILES['image_url']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Categories'));
                return;
            }
            
            // Supprimer l'ancienne image si existe
            $category = $this->Model->readOne('categories', ['id_categorie' => $id]);
            if ($category && !empty($category['image_url']) && file_exists(FCPATH . 'attachments/Categories/' . $category['image_url'])) {
                unlink(FCPATH . 'attachments/Categories/' . $category['image_url']);
            }
            
            $data['image_url'] = $new_image;
        }

        $rsp = $this->Model->update('categories', ['id_categorie' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Catégorie mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Categories'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer la catégorie pour supprimer son image
        $category = $this->Model->readOne('categories', ['id_categorie' => $id]);
        
        // Vérifier si des produits sont associés à cette catégorie
        // $products_count = $this->Model->count('produits', ['id_categorie' => $id]);
        // if ($products_count > 0) {
        //     $this->session->set_flashdata('error', 'Impossible de supprimer cette catégorie car des produits y sont associés.');
        //     redirect(base_url('Categories'));
        //     return;
        // }

        $rsp = $this->Model->delete('categories', ['id_categorie' => $id]);

        if ($rsp) {
            // Supprimer l'image physique si existe
            if ($category && !empty($category['image_url']) && file_exists(FCPATH . 'attachments/Categories/' . $category['image_url'])) {
                unlink(FCPATH . 'attachments/Categories/' . $category['image_url']);
            }
            $this->session->set_flashdata('success', 'Catégorie supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Categories'));
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Categories/';
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