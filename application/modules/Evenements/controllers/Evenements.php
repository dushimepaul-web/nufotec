<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Evenements extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Pas de deleted_at dans cette table
        $data['evenements'] = $this->Model->read('evenements', null, 'date_debut','DESC');
        $this->load->view('Evenements_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_public = $this->input->post('est_public');
        
        $status = ($est_public == 1) ? 0 : 1;
        $rsp = $this->Model->update('evenements', ['id_evenement' => $id], ['est_public' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Visibilité de l\'événement mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Evenements'));    
    }

    function EvenementDetail($evenementDetail){
        $id = explode('_', $evenementDetail);
        $data['detail'] = $this->Model->readOne('evenements', ['id_evenement' => $id[0]]);
        $this->load->view('EvenementDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('slug', 'Slug', 'required|is_unique[evenements.slug]');
        $this->form_validation->set_rules('date_debut', 'Date de début', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Evenements'));
            return;
        }

        $titre = $this->input->post('titre');
        $slug = $this->url_slug($this->input->post('slug'));
        $description = $this->input->post('description') ?: NULL;
        $lieu = $this->input->post('lieu') ?: NULL;
        $date_debut = $this->input->post('date_debut');
        $date_fin = $this->input->post('date_fin') ?: NULL;
        $capacite_max = $this->input->post('capacite_max') ?: NULL;
        $lien_inscription = $this->input->post('lien_inscription') ?: NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_public = $this->input->post('est_public') ? 1 : 0;

        // Upload image si fournie
        $image_url = NULL;
        if (!empty($_FILES['image_url']['name'])) {
            $image_url = $this->upload_image($_FILES['image_url']['tmp_name'], $_FILES['image_url']['name']);
            if ($image_url === NULL) {
                $this->session->set_flashdata('error', 'Format d\'image non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Evenements'));
                return;
            }
        }

        $data = array(
            'titre' => $titre,
            'slug' => $slug,
            'description' => $description,
            'lieu' => $lieu,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'image_url' => $image_url,
            'capacite_max' => $capacite_max,
            'inscriptions_actuelles' => 0,
            'lien_inscription' => $lien_inscription,
            'est_public' => $est_public,
            'id_page_associee' => $id_page_associee
        );
        
        $rsp = $this->Model->create('evenements', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Événement créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Evenements'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('slug', 'Slug', 'required');
        $this->form_validation->set_rules('date_debut', 'Date de début', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Evenements'));
            return;
        }

        $titre = $this->input->post('titre');
        $slug = $this->url_slug($this->input->post('slug'));
        $description = $this->input->post('description') ?: NULL;
        $lieu = $this->input->post('lieu') ?: NULL;
        $date_debut = $this->input->post('date_debut');
        $date_fin = $this->input->post('date_fin') ?: NULL;
        $capacite_max = $this->input->post('capacite_max') ?: NULL;
        $lien_inscription = $this->input->post('lien_inscription') ?: NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_public = $this->input->post('est_public') ? 1 : 0;

        $data = array(
            'titre' => $titre,
            'slug' => $slug,
            'description' => $description,
            'lieu' => $lieu,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'capacite_max' => $capacite_max,
            'lien_inscription' => $lien_inscription,
            'est_public' => $est_public,
            'id_page_associee' => $id_page_associee
        );

        // Upload nouvelle image si fournie
        if (!empty($_FILES['image_url']['name'])) {
            $new_image = $this->upload_image($_FILES['image_url']['tmp_name'], $_FILES['image_url']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format d\'image non valide.');
                redirect(base_url('Evenements'));
                return;
            }
            
            // Supprimer l'ancienne image si existe
            $evenement = $this->Model->readOne('evenements', ['id_evenement' => $id]);
            if ($evenement && !empty($evenement['image_url']) && file_exists(FCPATH . 'attachments/Evenements/' . $evenement['image_url'])) {
                unlink(FCPATH . 'attachments/Evenements/' . $evenement['image_url']);
            }
            
            $data['image_url'] = $new_image;
        }

        $rsp = $this->Model->update('evenements', ['id_evenement' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Événement mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Evenements'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer l'événement pour supprimer son image
        $evenement = $this->Model->readOne('evenements', ['id_evenement' => $id]);
        
        // Suppression définitive (pas de soft delete)
        $rsp = $this->Model->delete('evenements', ['id_evenement' => $id]);

        if ($rsp) {
            // Supprimer l'image physique si existe
            if ($evenement && !empty($evenement['image_url']) && file_exists(FCPATH . 'attachments/Evenements/' . $evenement['image_url'])) {
                unlink(FCPATH . 'attachments/Evenements/' . $evenement['image_url']);
            }
            $this->session->set_flashdata('success', 'Événement supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Evenements'));
    }

    // Génération de slug URL-friendly
    private function url_slug($str, $options = array()) {
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => true
        );
        $options = array_merge($defaults, $options);
        $char_map = array(
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
            'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ő' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y',
            'þ' => 'th', 'ÿ' => 'y'
        );
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
        $str = trim($str, $options['delimiter']);
        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Evenements/';
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
