<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workflow_categories extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        $data['workflows'] = $this->Model->read('workflow_categories', null, 'id_categorie, etape_ordre', 'ASC');
        $data['categories'] = $this->Model->read('categories', null, 'nom_categorie', 'ASC');
        
        // Calculer les statistiques
        $data['stats'] = $this->calculate_stats($data['workflows'], $data['categories']);
        
        $this->load->view('Workflow_categories_View', $data);
    }

    private function calculate_stats($workflows, $categories) {
        $stats = [
            'total_etapes' => count($workflows),
            'total_categories' => count($categories),
            'categories_with_workflow' => [],
            'moyenne_etapes_par_cat' => 0,
            'delai_total_moyen' => 0,
            'etapes_obligatoires' => 0,
            'etapes_optionnelles' => 0
        ];
        
        $etapes_par_cat = [];
        $delai_total = 0;
        $nb_delais = 0;
        
        foreach ($workflows as $wf) {
            $cat_id = $wf['id_categorie'];
            if (!isset($etapes_par_cat[$cat_id])) {
                $etapes_par_cat[$cat_id] = 0;
                $stats['categories_with_workflow'][] = $cat_id;
            }
            $etapes_par_cat[$cat_id]++;
            
            if (!empty($wf['delai_heures'])) {
                $delai_total += $wf['delai_heures'];
                $nb_delais++;
            }
            
            if ($wf['est_obligatoire']) {
                $stats['etapes_obligatoires']++;
            } else {
                $stats['etapes_optionnelles']++;
            }
        }
        
        if (count($etapes_par_cat) > 0) {
            $stats['moyenne_etapes_par_cat'] = round(array_sum($etapes_par_cat) / count($etapes_par_cat), 1);
        }
        
        if ($nb_delais > 0) {
            $stats['delai_total_moyen'] = round($delai_total / $nb_delais, 1);
        }
        
        return $stats;
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_active = $this->input->post('est_active');
        
        $status = ($est_active == 1) ? 0 : 1;
        $rsp = $this->Model->update('workflow_categories', ['id_workflow' => $id], ['est_active' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut de l\'étape mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Workflow_categories'));    
    }

    function WorkflowDetail($workflowDetail){
        $id = explode('_', $workflowDetail);
        $data['detail'] = $this->Model->readOne('workflow_categories', ['id_workflow' => $id[0]]);
        $data['categories'] = $this->Model->read('categories', null, 'nom_categorie', 'ASC');
        
        // Récupérer les étapes de la même catégorie pour visualiser le flux
        if ($data['detail']) {
            $data['workflow_steps'] = $this->Model->read(
                'workflow_categories', 
                ['id_categorie' => $data['detail']['id_categorie'], 'est_active' => 1], 
                'etape_ordre', 
                'ASC'
            );
        }
        
        $this->load->view('Workflow_categoryDetail_View', $data);
    }

    function Create(){
        $this->form_validation->set_rules('id_categorie', 'Catégorie', 'required|integer');
        $this->form_validation->set_rules('nom_etape', 'Nom de l\'étape', 'required|max_length[100]');
        $this->form_validation->set_rules('etape_ordre', 'Ordre de l\'étape', 'required|integer');
        $this->form_validation->set_rules('type_etape', 'Type d\'étape', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Workflow_categories'));
            return;
        }

        $data = array(
            'id_categorie' => $this->input->post('id_categorie'),
            'etape_ordre' => $this->input->post('etape_ordre'),
            'nom_etape' => $this->input->post('nom_etape'),
            'description_etape' => $this->input->post('description_etape') ?: NULL,
            'type_etape' => $this->input->post('type_etape'),
            'responsable_role' => $this->input->post('responsable_role') ?: NULL,
            'delai_heures' => $this->input->post('delai_heures') ?: NULL,
            'est_obligatoire' => $this->input->post('est_obligatoire') ? 1 : 0,
            'notification_email' => $this->input->post('notification_email') ? 1 : 0,
            'icone_etape' => $this->input->post('icone_etape') ?: 'arrow-right',
            'couleur_etape' => $this->input->post('couleur_etape') ?: '#0f4c3a',
            'est_active' => 1
        );

        $rsp = $this->Model->create('workflow_categories', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Étape de workflow créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de l\'étape.');
        }
        redirect(base_url('Workflow_categories'));
    }

    function Update(){
        $id = $this->input->post('id_workflow');
        
        $this->form_validation->set_rules('id_categorie', 'Catégorie', 'required|integer');
        $this->form_validation->set_rules('nom_etape', 'Nom de l\'étape', 'required|max_length[100]');
        $this->form_validation->set_rules('etape_ordre', 'Ordre de l\'étape', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Workflow_categories'));
            return;
        }

        $data = array(
            'id_categorie' => $this->input->post('id_categorie'),
            'etape_ordre' => $this->input->post('etape_ordre'),
            'nom_etape' => $this->input->post('nom_etape'),
            'description_etape' => $this->input->post('description_etape') ?: NULL,
            'type_etape' => $this->input->post('type_etape'),
            'responsable_role' => $this->input->post('responsable_role') ?: NULL,
            'delai_heures' => $this->input->post('delai_heures') ?: NULL,
            'est_obligatoire' => $this->input->post('est_obligatoire') ? 1 : 0,
            'notification_email' => $this->input->post('notification_email') ? 1 : 0,
            'icone_etape' => $this->input->post('icone_etape') ?: 'arrow-right',
            'couleur_etape' => $this->input->post('couleur_etape') ?: '#0f4c3a',
            'est_active' => $this->input->post('est_active') ? 1 : 0
        );

        $rsp = $this->Model->update('workflow_categories', ['id_workflow' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Étape de workflow mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Workflow_categories'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $rsp = $this->Model->delete('workflow_categories', ['id_workflow' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Étape de workflow supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Workflow_categories'));
    }

    // API pour récupérer les étapes d'une catégorie (AJAX)
    function getStepsByCategory($id_categorie) {
        $steps = $this->Model->read(
            'workflow_categories',
            ['id_categorie' => $id_categorie, 'est_active' => 1],
            'etape_ordre',
            'ASC'
        );
        echo json_encode($steps);
    }
}