<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visionmission extends MY_Controller {

    function __construct() {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->helper('text');   // ← SOLUTION
    }

    public function index() {
        $data['statements'] = $this->Model->read('company_statements', null, 'order', 'ASC');
        $this->load->view('Visionmission_View', $data);
    }

    /**
     * Page publique - Affichage utilisateur (Frontend)
     */
    public function about()
    {
        $data['page_title'] = 'Notre Entreprise - Mission & Vision';
        
        // Récupérer le hero section depuis la base
        $data['hero_section'] = $this->get_hero_section();
        
        // Préparer les données pour la vue frontend
        if (!empty($data['hero_section'])) {
            $data['section'] = $data['hero_section'];
            $data['image_opacity'] = $data['hero_section']['options']['image_opacity'] ?? 0.6;
            $data['raw_content'] = strip_tags($data['hero_section']['contenu_texte'] ?? '');
        } else {
            // Hero par défaut
            $data['section'] = [
                'titre_section' => 'Notre Entreprise',
                'sous_titre' => 'Mission, Vision & Valeurs',
                'contenu_texte' => 'Découvrez ce qui motive African Green Farmers',
                'image_url' => null,
                'bouton_texte' => 'En savoir plus',
                'bouton_lien' => '#statements',
                'custom_class' => ''
            ];
            $data['image_opacity'] = 0.6;
            $data['raw_content'] = 'Découvrez ce qui motive African Green Farmers';
        }
        
        // Récupérer les déclarations actives pour l'affichage public
        $data['statements'] = $this->Model->read('company_statements', ['is_active' => 1], 'order', 'ASC');
        
        $this->load->view('frontend/company_statements', $data);
    }

    /**
     * Récupérer la section hero depuis la base
     */
    private function get_hero_section()
    {
        // Chercher la page "company-statements" ou "about"
        $page = $this->Model->readOne('pages', [
            'slug' => 'company-statements',
            'est_publiee' => 1
        ]);

        // Si pas trouvé, essayer d'autres slugs
        if (empty($page)) {
            $page = $this->db->where_in('slug', ['about', 'qui-sommes-nous', 'notre-entreprise'])
                            ->where('est_publiee', 1)
                            ->get('pages')
                            ->row_array();
        }

        if (empty($page)) {
            log_message('debug', 'Page company-statements non trouvée');
            return null;
        }

        $hero = $this->Model->readOne('sections_contenu', [
            'id_page' => $page['id_page'],
            'type_section' => 'hero',
            'est_active' => 1
        ]);

        if (empty($hero)) {
            log_message('debug', 'Section hero non trouvée pour la page ' . $page['id_page']);
            return null;
        }

        // Décoder les options JSON
        if (!empty($hero['options_json'])) {
            $hero['options'] = json_decode($hero['options_json'], true);
        } else {
            $hero['options'] = [];
        }

        return $hero;
    }

    /**
     * Changer le statut actif/inactif
     */
    function ChangeStatus()
    {
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        $status = ($is_active == 1) ? 0 : 1;
        $rsp = $this->Model->update('company_statements', ['id' => $id], ['is_active' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Visionmission'));    
    }

    /**
     * Créer une nouvelle déclaration
     */
    function Create()
    {
        // Validation des champs
        $this->form_validation->set_rules('type', 'Type', 'required|in_list[mission,vision,objective,value,slogan,other]');
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Visionmission'));
            return;
        }

        $type = $this->input->post('type');
        $title = $this->input->post('title') ?: NULL;
        $description = $this->input->post('description');
        $icon = $this->input->post('icon') ?: 'bx-bullseye';
        $order = $this->input->post('order') ?: 0;
        $is_active = $this->input->post('is_active') ? 1 : 0;

        $data = array(
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'order' => $order,
            'is_active' => $is_active,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('company_statements', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Déclaration créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Visionmission'));
    }

    /**
     * Mettre à jour une déclaration
     */
    function Update()
    {
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('type', 'Type', 'required|in_list[mission,vision,objective,value,slogan,other]');
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Visionmission'));
            return;
        }

        $type = $this->input->post('type');
        $title = $this->input->post('title') ?: NULL;
        $description = $this->input->post('description');
        $icon = $this->input->post('icon') ?: 'bx-bullseye';
        $order = $this->input->post('order') ?: 0;
        $is_active = $this->input->post('is_active') ? 1 : 0;

        $data = array(
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'order' => $order,
            'is_active' => $is_active,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->update('company_statements', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Déclaration mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Visionmission'));
    }

    /**
     * Supprimer une déclaration (soft delete via is_active)
     */
    function Delete()
    {
        $id = $this->input->post('id');
        
        // Soft delete - mettre is_active à 0
        $rsp = $this->Model->update('company_statements', ['id' => $id], [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Déclaration supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Visionmission'));
    }

    /**
     * API pour récupérer les déclarations (AJAX)
     */
    public function api_list()
    {
        $this->output->set_content_type('application/json');
        
        $statements = $this->Model->read('company_statements', ['is_active' => 1], 'order', 'ASC');
        
        echo json_encode([
            'success' => true,
            'data' => $statements,
            'count' => count($statements)
        ]);
    }
}