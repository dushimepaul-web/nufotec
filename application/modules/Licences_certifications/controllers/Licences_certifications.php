<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Licences_certifications extends MY_Controller {

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
        // Pas de deleted_at dans cette table
        $data['licences'] = $this->Model->read('licences_certifications', null, 'id_licence','ASC');
        $data['pages'] = $this->Model->read('pages', ['est_publiee' => 1], 'id_page');
        $this->load->view('Licences_certifications_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $statut = $this->input->post('statut');
        
        // Cycle: obtenue -> en_cours -> a_renouveler -> obtenue
        $nouveau_statut = 'obtenue';
        if ($statut == 'obtenue') {
            $nouveau_statut = 'en_cours';
        } elseif ($statut == 'en_cours') {
            $nouveau_statut = 'a_renouveler';
        } elseif ($statut == 'a_renouveler') {
            $nouveau_statut = 'obtenue';
        }
        
        $rsp = $this->Model->update('licences_certifications', ['id_licence' => $id], ['statut' => $nouveau_statut]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut de la licence mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Licences_certifications'));    
    }

    function LicenceDetail($licenceDetail){
        $id = explode('_', $licenceDetail);
        $data['detail'] = $this->Model->readOne('licences_certifications', ['id_licence' => $id[0]]);
        $data['pages'] = $this->Model->read('pages', ['est_publiee' => 1], 'id_page');
        $this->load->view('LicenceDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('nom_licence', 'Nom de la licence', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Licences_certifications'));
            return;
        }

        $nom_licence = $this->input->post('nom_licence');
        $organisme = $this->input->post('organisme') ?: NULL;
        $date_obtention = $this->input->post('date_obtention') ?: NULL;
        $date_expiration = $this->input->post('date_expiration') ?: NULL;
        $statut = $this->input->post('statut') ?: 'en_cours';
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        // Upload fichier si fourni
        $fichier_url = NULL;
        if (!empty($_FILES['fichier_url']['name'])) {
            $fichier_url = $this->upload_file($_FILES['fichier_url']['tmp_name'], $_FILES['fichier_url']['name']);
            if ($fichier_url === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: pdf, jpg, png, jpeg');
                redirect(base_url('Licences_certifications'));
                return;
            }
        }

        $data = array(
            'nom_licence' => $nom_licence,
            'organisme' => $organisme,
            'date_obtention' => $date_obtention,
            'date_expiration' => $date_expiration,
            'fichier_url' => $fichier_url,
            'statut' => $statut,
            'id_page_associee' => $id_page_associee
        );
        
        $rsp = $this->Model->create('licences_certifications', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Licence/Certification créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Licences_certifications'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('nom_licence', 'Nom de la licence', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Licences_certifications'));
            return;
        }

        $nom_licence = $this->input->post('nom_licence');
        $organisme = $this->input->post('organisme') ?: NULL;
        $date_obtention = $this->input->post('date_obtention') ?: NULL;
        $date_expiration = $this->input->post('date_expiration') ?: NULL;
        $statut = $this->input->post('statut') ?: 'en_cours';
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'nom_licence' => $nom_licence,
            'organisme' => $organisme,
            'date_obtention' => $date_obtention,
            'date_expiration' => $date_expiration,
            'statut' => $statut,
            'id_page_associee' => $id_page_associee
        );

        // Upload nouveau fichier si fourni
        if (!empty($_FILES['fichier_url']['name'])) {
            $new_file = $this->upload_file($_FILES['fichier_url']['tmp_name'], $_FILES['fichier_url']['name']);
            if ($new_file === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide.');
                redirect(base_url('Licences_certifications'));
                return;
            }
            
            // Supprimer l'ancien fichier si existe
            $licence = $this->Model->readOne('licences_certifications', ['id_licence' => $id]);
            if ($licence && !empty($licence['fichier_url']) && file_exists(FCPATH . 'attachments/Licences/' . $licence['fichier_url'])) {
                unlink(FCPATH . 'attachments/Licences/' . $licence['fichier_url']);
            }
            
            $data['fichier_url'] = $new_file;
        }

        $rsp = $this->Model->update('licences_certifications', ['id_licence' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Licence/Certification mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Licences_certifications'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer la licence pour supprimer son fichier
        $licence = $this->Model->readOne('licences_certifications', ['id_licence' => $id]);
        
        // Suppression définitive (pas de soft delete)
        $rsp = $this->Model->delete('licences_certifications', ['id_licence' => $id]);

        if ($rsp) {
            // Supprimer le fichier physique si existe
            if ($licence && !empty($licence['fichier_url']) && file_exists(FCPATH . 'attachments/Licences/' . $licence['fichier_url'])) {
                unlink(FCPATH . 'attachments/Licences/' . $licence['fichier_url']);
            }
            $this->session->set_flashdata('success', 'Licence/Certification supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Licences_certifications'));
    }

    // Upload fichiers
    public function upload_file($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Licences/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('pdf', 'jpg', 'png', 'jpeg', 'doc', 'docx');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }

    // Organismes prédéfinis
    public function get_organismes() {
        return [
            'PACRA' => 'PACRA',
            'ZDA' => 'ZDA',
            'ZEMA' => 'ZEMA',
            'ZMRA' => 'ZMRA',
            'ZAMRA' => 'ZAMRA',
            'ZABS' => 'ZABS',
            'IMMIGRATION' => 'IMMIGRATION',
            'Local Health Department' => 'Local Health Department',
            'Ministry of Land' => 'Ministry of Land',
            'ISO' => 'ISO',
            'Autre' => 'Autre'
        ];
    }
}