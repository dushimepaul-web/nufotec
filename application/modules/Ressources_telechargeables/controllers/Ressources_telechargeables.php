<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ressources_telechargeables extends MY_Controller {

    function __construct()
    {
        parent::__construct();
       is_admin();
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
{
    $data['ressources'] = $this->Model->read('ressources_telechargeables', ['deleted_at' => NULL], 'id_ressource','DESC');
    $data['pages'] = $this->Model->read('pages', ['est_publiee' => 1], 'id_page');
    
    // Ajouter les méthodes au tableau $data
    $data['get_type_icon'] = function($type) {
        $icons = [
            'pdf' => 'bx bxs-file-pdf text-danger',
            'video' => 'bx bxs-video text-warning',
            'etude_clinique' => 'bx bxs-flask text-primary',
            'rapport_annuel' => 'bx bxs-report text-success',
            'fiche_technique' => 'bx bxs-file-doc text-info',
            'brochure' => 'bx bxs-book text-secondary',
            'dossier_investisseur' => 'bx bxs-briefcase text-dark'
        ];
        return $icons[$type] ?? 'bx bxs-file text-muted';
    };
    
    $data['get_type_label'] = function($type) {
        $labels = [
            'pdf' => 'PDF',
            'video' => 'Vidéo',
            'etude_clinique' => 'Étude clinique',
            'rapport_annuel' => 'Rapport annuel',
            'fiche_technique' => 'Fiche technique',
            'brochure' => 'Brochure',
            'dossier_investisseur' => 'Dossier investisseur'
        ];
        return $labels[$type] ?? ucfirst($type);
    };
    
    $this->load->view('Ressources_telechargeables_View', $data);
}

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_public = $this->input->post('est_public');
        
        $status = ($est_public == 1) ? 0 : 1;
        $rsp = $this->Model->update('ressources_telechargeables', ['id_ressource' => $id], ['est_public' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Visibilité mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Ressources_telechargeables'));    
    }

    function RessourceDetail($ressourceDetail){
        $id = explode('_', $ressourceDetail);
        $data['detail'] = $this->Model->readOne('ressources_telechargeables', ['id_ressource' => $id[0]]);
        $data['pages'] = $this->Model->read('pages', ['est_publiee' => 1], 'id_page');
        $this->load->view('RessourceDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('type', 'Type', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Ressources_telechargeables'));
            return;
        }

        $titre = $this->input->post('titre');
        $type = $this->input->post('type');
        $description = $this->input->post('description') ?: NULL;
        $date_publication = $this->input->post('date_publication') ?: date('Y-m-d');
        $langue = $this->input->post('langue') ?: 'fr';
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_public = $this->input->post('est_public') ? 1 : 0;

        // Upload fichier si fourni
        $fichier_url = NULL;
        $taille_fichier = NULL;
        if (!empty($_FILES['fichier']['name'])) {
            $upload_result = $this->upload_file($_FILES['fichier']['tmp_name'], $_FILES['fichier']['name']);
            if ($upload_result === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: pdf, doc, docx, xls, xlsx, ppt, pptx, mp4, mp3, zip');
                redirect(base_url('Ressources_telechargeables'));
                return;
            }
            $fichier_url = $upload_result['file_name'];
            $taille_fichier = $upload_result['file_size'];
        }

        $data = array(
            'titre' => $titre,
            'type' => $type,
            'fichier_url' => $fichier_url,
            'description' => $description,
            'date_publication' => $date_publication,
            'langue' => $langue,
            'taille_fichier' => $taille_fichier,
            'est_public' => $est_public,
            'id_page_associee' => $id_page_associee,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('ressources_telechargeables', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Ressource créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Ressources_telechargeables'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('type', 'Type', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Ressources_telechargeables'));
            return;
        }

        $titre = $this->input->post('titre');
        $type = $this->input->post('type');
        $description = $this->input->post('description') ?: NULL;
        $date_publication = $this->input->post('date_publication') ?: date('Y-m-d');
        $langue = $this->input->post('langue') ?: 'fr';
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_public = $this->input->post('est_public') ? 1 : 0;

        $data = array(
            'titre' => $titre,
            'type' => $type,
            'description' => $description,
            'date_publication' => $date_publication,
            'langue' => $langue,
            'est_public' => $est_public,
            'id_page_associee' => $id_page_associee,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Upload nouveau fichier si fourni
        if (!empty($_FILES['fichier']['name'])) {
            $upload_result = $this->upload_file($_FILES['fichier']['tmp_name'], $_FILES['fichier']['name']);
            if ($upload_result === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: pdf, doc, docx, xls, xlsx, ppt, pptx, mp4, mp3, zip');
                redirect(base_url('Ressources_telechargeables'));
                return;
            }
            
            // Supprimer l'ancien fichier si existe
            $ressource = $this->Model->readOne('ressources_telechargeables', ['id_ressource' => $id]);
            if ($ressource && !empty($ressource['fichier_url']) && file_exists(FCPATH . 'attachments/Ressources/' . $ressource['fichier_url'])) {
                unlink(FCPATH . 'attachments/Ressources/' . $ressource['fichier_url']);
            }
            
            $data['fichier_url'] = $upload_result['file_name'];
            $data['taille_fichier'] = $upload_result['file_size'];
        }

        $rsp = $this->Model->update('ressources_telechargeables', ['id_ressource' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Ressource mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Ressources_telechargeables'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer la ressource pour supprimer son fichier
        $ressource = $this->Model->readOne('ressources_telechargeables', ['id_ressource' => $id]);
        
        // Soft delete
        $rsp = $this->Model->update('ressources_telechargeables', ['id_ressource' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'est_public' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            // Supprimer le fichier physique si existe
            if ($ressource && !empty($ressource['fichier_url']) && file_exists(FCPATH . 'attachments/Ressources/' . $ressource['fichier_url'])) {
                unlink(FCPATH . 'attachments/Ressources/' . $ressource['fichier_url']);
            }
            $this->session->set_flashdata('success', 'Ressource supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Ressources_telechargeables'));
    }

    // Upload fichiers
    public function upload_file($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Ressources/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'mp4', 'mp3', 'zip', 'rar');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        $file_size = $this->format_file_size($_FILES['fichier']['size']);
        $final_name = $fichier . "." . $file_extension;

        move_uploaded_file($nom_file, $ref_folder . $final_name);
        
        return [
            'file_name' => $final_name,
            'file_size' => $file_size
        ];
    }

    // Formater la taille du fichier
    private function format_file_size($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // Icônes selon le type
    public function get_type_icon($type) {
        $icons = [
            'pdf' => 'bx bxs-file-pdf text-danger',
            'video' => 'bx bxs-video text-warning',
            'etude_clinique' => 'bx bxs-flask text-primary',
            'rapport_annuel' => 'bx bxs-report text-success',
            'fiche_technique' => 'bx bxs-file-doc text-info',
            'brochure' => 'bx bxs-book text-secondary',
            'dossier_investisseur' => 'bx bxs-briefcase text-dark'
        ];
        return $icons[$type] ?? 'bx bxs-file text-muted';
    }

    // Labels des types
    public function get_type_label($type) {
        $labels = [
            'pdf' => 'PDF',
            'video' => 'Vidéo',
            'etude_clinique' => 'Étude clinique',
            'rapport_annuel' => 'Rapport annuel',
            'fiche_technique' => 'Fiche technique',
            'brochure' => 'Brochure',
            'dossier_investisseur' => 'Dossier investisseur'
        ];
        return $labels[$type] ?? ucfirst($type);
    }
}