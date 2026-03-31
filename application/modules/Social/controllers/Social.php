<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Social extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->model('Social_model');
    }

    /**
     * Liste des liens sociaux (Admin)
     */
    public function index()
    {
        $data['social_links'] = $this->Social_model->read_all();
        $this->load->view('Social_View', $data);
    }

    /**
     * Changer le statut actif/inactif
     */
    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        $rsp = $this->Social_model->change_status($id, $is_active);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('social'));
    }

    /**
     * Créer un lien social
     */
    public function Create()
    {
        // Validation
        $this->form_validation->set_rules('platform', 'Plateforme', 'required|is_unique[social_links.platform]');
        $this->form_validation->set_rules('label', 'Label', 'required');
        $this->form_validation->set_rules('url', 'URL', 'required|valid_url');
        $this->form_validation->set_rules('icon_name', 'Icône', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('social'));
            return;
        }

        $data = array(
            'platform' => strtolower($this->input->post('platform')),
            'label' => $this->input->post('label'),
            'url' => $this->input->post('url'),
            'icon_name' => $this->input->post('icon_name'),
            'icon_class' => $this->input->post('icon_class') ?: 'bi',
            'display_order' => $this->input->post('display_order') ?: 0,
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'target_blank' => $this->input->post('target_blank') ? 1 : 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Social_model->create($data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Lien social créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la création.');
        }
        redirect(base_url('social'));
    }

    /**
     * Mettre à jour un lien social
     */
    public function Update()
    {
        $id = $this->input->post('id');

        // Validation
        $this->form_validation->set_rules('label', 'Label', 'required');
        $this->form_validation->set_rules('url', 'URL', 'required|valid_url');
        $this->form_validation->set_rules('icon_name', 'Icône', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('social'));
            return;
        }

        $data = array(
            'label' => $this->input->post('label'),
            'url' => $this->input->post('url'),
            'icon_name' => $this->input->post('icon_name'),
            'icon_class' => $this->input->post('icon_class') ?: 'bi',
            'display_order' => $this->input->post('display_order') ?: 0,
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'target_blank' => $this->input->post('target_blank') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Social_model->update(['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Lien social mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la mise à jour.');
        }
        redirect(base_url('social'));
    }

    /**
     * Supprimer un lien social
     */
    public function Delete()
    {
        $id = $this->input->post('id');
        
        $rsp = $this->Social_model->delete(['id' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Lien social supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('social'));
    }

    /**
     * API: Récupérer les liens actifs pour le frontend
     */
    public function api_get_active()
    {
        $links = $this->Social_model->read_active();
        echo json_encode(['success' => true, 'data' => $links]);
    }
}