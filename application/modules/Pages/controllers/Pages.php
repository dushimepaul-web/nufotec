<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        is_admin();
    }
    
    public function index()
    {
        // Récupérer les pages avec leur parent pour l'affichage hiérarchique
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL], 'menu_ordre', 'ASC');
        $data['pages_list'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('Pages_View',$data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_publiee = $this->input->post('est_publiee');
        
        $status = ($est_publiee == 1) ? 0 : 1;
        $rsp = $this->Model->update('pages', ['id_page' => $id], ['est_publiee' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut de la page mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Pages'));    
    }

    function PageDetail($pageDetail){
        $id = explode('_', $pageDetail);
        $data['detail'] = $this->Model->readOne('pages', ['id_page' => $id[0]]);
        $data['pages_list'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $data['sections'] = $this->Model->read('sections_contenu', ['id_page' => $id[0]], 'ordre', 'ASC');
        $this->load->view('PageDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('titre_page', 'Titre de la page', 'required');
        $this->form_validation->set_rules('slug', 'Slug', 'required|is_unique[pages.slug]');
        $this->form_validation->set_rules('menu_ordre', 'Ordre du menu', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Pages'));
            return;
        }

        $titre_page = $this->input->post('titre_page');
        $slug = $this->input->post('slug');
        $menu_ordre = $this->input->post('menu_ordre');
        $menu_parent_id = $this->input->post('menu_parent_id') ?: NULL;
        $meta_description = $this->input->post('meta_description');
        $meta_keywords = $this->input->post('meta_keywords');
        $template_specifique = $this->input->post('template_specifique') ?: 'default';
        $icone_menu = $this->input->post('icone_menu');

        $data = array(
            'titre_page' => $titre_page,
            'slug' => $slug,
            'menu_ordre' => $menu_ordre,
            'menu_parent_id' => $menu_parent_id,
            'est_publiee' => 1,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'template_specifique' => $template_specifique,
            'icone_menu' => $icone_menu,
            'date_creation' => date('Y-m-d H:i:s'),
            'date_modification' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('pages', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Page créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de la page.');
        }
        redirect(base_url('Pages'));
    }

    function Update(){
        $id = $this->input->post('id_page');
        
        // Validation
        $this->form_validation->set_rules('titre_page', 'Titre de la page', 'required');
        $this->form_validation->set_rules('slug', 'Slug', 'required');
        $this->form_validation->set_rules('menu_ordre', 'Ordre du menu', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Pages'));
            return;
        }

        $titre_page = $this->input->post('titre_page');
        $slug = $this->input->post('slug');
        $menu_ordre = $this->input->post('menu_ordre');
        $menu_parent_id = $this->input->post('menu_parent_id') ?: NULL;
        $meta_description = $this->input->post('meta_description');
        $meta_keywords = $this->input->post('meta_keywords');
        $template_specifique = $this->input->post('template_specifique') ?: 'default';
        $icone_menu = $this->input->post('icone_menu');
        $est_publiee = $this->input->post('est_publiee') ? 1 : 0;

        $data = array(
            'titre_page' => $titre_page,
            'slug' => $slug,
            'menu_ordre' => $menu_ordre,
            'menu_parent_id' => $menu_parent_id,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'template_specifique' => $template_specifique,
            'icone_menu' => $icone_menu,
            'est_publiee' => $est_publiee,
            'date_modification' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->update('pages', ['id_page' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Page mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Pages'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Soft delete
        $rsp = $this->Model->update('pages', ['id_page' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'est_publiee' => 0,
            'date_modification' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Page supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Pages'));
    }

    // Méthode pour générer le slug automatiquement
    function GenerateSlug(){
        $titre = $this->input->post('titre');
        $slug = url_title($titre, 'dash', TRUE);
        
        // Vérifier si le slug existe déjà
        $existing = $this->Model->readOne('pages', ['slug' => $slug, 'deleted_at' => NULL]);
        if ($existing) {
            $slug .= '-' . time();
        }
        
        echo json_encode(['slug' => $slug]);
    }
}