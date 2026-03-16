<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author:    Dushime Paul
 * Email:     dushimeyesupaulin@gmail.com
 */

class Mode_payement extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Model');
    }

    public function index()
    {
        $data['mode_payements'] = $this->Model->read('mode_payement', null, 'id_mode_payement', 'DESC');
        $this->load->view('Mode_payement_View', $data);
    }

    public function CreateModePayement()
    {
        $this->form_validation->set_rules('description', 'Description', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('etapepaiement', 'Étape de paiement', 'trim|max_length[100]');
        $this->form_validation->set_rules('numero_compte', 'Numéro de compte', 'trim|max_length[50]');
        $this->form_validation->set_rules('nom_compte', 'Nom du compte', 'trim|max_length[100]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Mode_payement'));
            return;
        }

        $data = [
            'description'    => $this->input->post('description', TRUE),
            'etapepaiement'  => $this->input->post('etapepaiement', TRUE) ?: null,
            'numero_compte'  => $this->input->post('numero_compte', TRUE) ?: null,
            'nom_compte'     => $this->input->post('nom_compte', TRUE) ?: null,
            'est_actif'      => $this->input->post('est_actif') ? 1 : 0
        ];

        $rsp = $this->Model->create('mode_payement', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Mode de paiement créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la création.');
        }
        redirect(base_url('Mode_payement'));
    }

    public function UpdateModePayement()
    {
        $id_mode_payement = $this->input->post('id_mode_payement');
        if (!$id_mode_payement) {
            show_404();
        }

        $this->form_validation->set_rules('description', 'Description', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('etapepaiement', 'Étape de paiement', 'trim|max_length[100]');
        $this->form_validation->set_rules('numero_compte', 'Numéro de compte', 'trim|max_length[50]');
        $this->form_validation->set_rules('nom_compte', 'Nom du compte', 'trim|max_length[100]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Mode_payement'));
            return;
        }

        $data = [
            'description'    => $this->input->post('description', TRUE),
            'etapepaiement'  => $this->input->post('etapepaiement', TRUE) ?: null,
            'numero_compte'  => $this->input->post('numero_compte', TRUE) ?: null,
            'nom_compte'     => $this->input->post('nom_compte', TRUE) ?: null,
            'est_actif'      => $this->input->post('est_actif') ? 1 : 0
        ];

        $rsp = $this->Model->update('mode_payement', ['id_mode_payement' => $id_mode_payement], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Mode de paiement modifié avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la modification.');
        }
        redirect(base_url('Mode_payement'));
    }

    public function DeleteModePayement()
    {
        $id_mode_payement = $this->input->post('id_mode_payement');
        if (!$id_mode_payement) {
            show_404();
        }

        $rsp = $this->Model->delete('mode_payement', ['id_mode_payement' => $id_mode_payement]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Mode de paiement supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('Mode_payement'));
    }
}