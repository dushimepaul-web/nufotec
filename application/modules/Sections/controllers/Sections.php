<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sections extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        // Récupérer les sections avec leurs pages associées
        $data['sections'] = $this->Model->read('sections_contenu', [], '', 'ASC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1],'menu_ordre', 'ASC');
        $this->load->view('Sections_View', $data);
    }

    /**
     * Afficher le formulaire d'ajout de section
     */
    public function add()
    {
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('Sections_add', $data);
    }

    /**
     * Afficher le formulaire d'édition de section
     */
    public function edit($id)
    {
        $data['detail'] = $this->Model->readOne('sections_contenu', ['id_section' => $id]);
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        
        if (empty($data['detail'])) {
            $this->session->set_flashdata('error', 'Section non trouvée.');
            redirect(base_url('Sections'));
        }
        
        $this->load->view('Sections_edit', $data);
    }

    function ChangeOrdre(){
        $id = $this->input->post('id');
        $direction = $this->input->post('direction'); // 'up' ou 'down'
        
        $section = $this->Model->readOne('sections_contenu', ['id_section' => $id]);
        if (!$section) {
            $this->session->set_flashdata('error', 'Section non trouvée.');
            redirect(base_url('Sections'));
            return;
        }

        $current_ordre = $section['ordre'];
        $id_page = $section['id_page'];
        
        if ($direction == 'up') {
            $new_ordre = $current_ordre - 1;
        } else {
            $new_ordre = $current_ordre + 1;
        }

        // Échanger l'ordre avec la section adjacente
        $adjacent = $this->Model->readOne('sections_contenu', [
            'id_page' => $id_page,
            'ordre' => $new_ordre
        ]);

        if ($adjacent) {
            // Mettre à jour la section adjacente
            $this->Model->update('sections_contenu', 
                ['id_section' => $adjacent['id_section']], 
                ['ordre' => $current_ordre]
            );
        }

        // Mettre à jour la section courante
        $rsp = $this->Model->update('sections_contenu', 
            ['id_section' => $id], 
            ['ordre' => $new_ordre]
        );

        if ($rsp) {
            $this->session->set_flashdata('success', 'Ordre mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('Sections'));    
    }

    function SectionDetail($id, $title = '') {
        $data['detail'] = $this->Model->readOne('sections_contenu', ['id_section' => $id]);
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('SectionDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('id_page', 'Page', 'required|integer');
        $this->form_validation->set_rules('type_section', 'Type de section', 'required');
        $this->form_validation->set_rules('ordre', 'Ordre', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Sections/add'));
            return;
        }

        $id_page = $this->input->post('id_page');
        $type_section = $this->input->post('type_section');
        
        // Champs multilingues
        $titre_section_fr = $this->input->post('titre_section_fr');
        $titre_section_en = $this->input->post('titre_section_en');
        $titre_section_sw = $this->input->post('titre_section_sw');
        
        $sous_titre_fr = $this->input->post('sous_titre_fr');
        $sous_titre_en = $this->input->post('sous_titre_en');
        $sous_titre_sw = $this->input->post('sous_titre_sw');
        
        $contenu_texte_fr = $this->input->post('contenu_texte_fr');
        $contenu_texte_en = $this->input->post('contenu_texte_en');
        $contenu_texte_sw = $this->input->post('contenu_texte_sw');
        
        $bouton_texte_fr = $this->input->post('bouton_texte_fr');
        $bouton_texte_en = $this->input->post('bouton_texte_en');
        $bouton_texte_sw = $this->input->post('bouton_texte_sw');
        
        // Champs non traduits
        $image_droite = $this->input->post('image_droite') ? 1 : 0;
        $bouton_lien = $this->input->post('bouton_lien');
        $ordre = $this->input->post('ordre');
        $custom_class = $this->input->post('custom_class');
        $options_json = $this->input->post('options_json');
        $options_json = !empty($options_json) ? $options_json : NULL;

        // Upload image si fournie
        $image_url = NULL;
        if (!empty($_FILES['image_file']['name'])) {
            $image_url = $this->upload_image($_FILES['image_file']['tmp_name'], $_FILES['image_file']['name']);
            if ($image_url === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Sections/add'));
                return;
            }
        } elseif (!empty($this->input->post('image_url'))) {
            $image_url = $this->input->post('image_url');
        }

        $data = array(
            'id_page' => $id_page,
            'type_section' => $type_section,
            'titre_section_fr' => $titre_section_fr,
            'titre_section_en' => $titre_section_en,
            'titre_section_sw' => $titre_section_sw,
            'sous_titre_fr' => $sous_titre_fr,
            'sous_titre_en' => $sous_titre_en,
            'sous_titre_sw' => $sous_titre_sw,
            'contenu_texte_fr' => $contenu_texte_fr,
            'contenu_texte_en' => $contenu_texte_en,
            'contenu_texte_sw' => $contenu_texte_sw,
            'image_url' => $image_url,
            'image_droite' => $image_droite,
            'bouton_texte_fr' => $bouton_texte_fr,
            'bouton_texte_en' => $bouton_texte_en,
            'bouton_texte_sw' => $bouton_texte_sw,
            'bouton_lien' => $bouton_lien,
            'ordre' => $ordre,
            'custom_class' => $custom_class,
            'options_json' => $options_json,
            'est_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('sections_contenu', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Section créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de la section.');
        }
        redirect(base_url('Sections'));
    }

    function Update(){
        $id = $this->input->post('id_section');
        
        // Validation
        $this->form_validation->set_rules('id_page', 'Page', 'required|integer');
        $this->form_validation->set_rules('type_section', 'Type de section', 'required');
        $this->form_validation->set_rules('ordre', 'Ordre', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Sections/edit/'.$id));
            return;
        }

        $id_page = $this->input->post('id_page');
        $type_section = $this->input->post('type_section');
        
        // Champs multilingues
        $titre_section_fr = $this->input->post('titre_section_fr');
        $titre_section_en = $this->input->post('titre_section_en');
        $titre_section_sw = $this->input->post('titre_section_sw');
        
        $sous_titre_fr = $this->input->post('sous_titre_fr');
        $sous_titre_en = $this->input->post('sous_titre_en');
        $sous_titre_sw = $this->input->post('sous_titre_sw');
        
        $contenu_texte_fr = $this->input->post('contenu_texte_fr');
        $contenu_texte_en = $this->input->post('contenu_texte_en');
        $contenu_texte_sw = $this->input->post('contenu_texte_sw');
        
        $bouton_texte_fr = $this->input->post('bouton_texte_fr');
        $bouton_texte_en = $this->input->post('bouton_texte_en');
        $bouton_texte_sw = $this->input->post('bouton_texte_sw');
        
        // Champs non traduits
        $image_droite = $this->input->post('image_droite') ? 1 : 0;
        $bouton_lien = $this->input->post('bouton_lien');
        $ordre = $this->input->post('ordre');
        $custom_class = $this->input->post('custom_class');
        $options_json = $this->input->post('options_json');
        $options_json = !empty($options_json) ? $options_json : NULL;

        $data = array(
            'id_page' => $id_page,
            'type_section' => $type_section,
            'titre_section_fr' => $titre_section_fr,
            'titre_section_en' => $titre_section_en,
            'titre_section_sw' => $titre_section_sw,
            'sous_titre_fr' => $sous_titre_fr,
            'sous_titre_en' => $sous_titre_en,
            'sous_titre_sw' => $sous_titre_sw,
            'contenu_texte_fr' => $contenu_texte_fr,
            'contenu_texte_en' => $contenu_texte_en,
            'contenu_texte_sw' => $contenu_texte_sw,
            'image_droite' => $image_droite,
            'bouton_texte_fr' => $bouton_texte_fr,
            'bouton_texte_en' => $bouton_texte_en,
            'bouton_texte_sw' => $bouton_texte_sw,
            'bouton_lien' => $bouton_lien,
            'ordre' => $ordre,
            'custom_class' => $custom_class,
            'options_json' => $options_json,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Gestion de l'image
        if (!empty($_FILES['image_file']['name'])) {
            $new_image = $this->upload_image($_FILES['image_file']['tmp_name'], $_FILES['image_file']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Sections/edit/'.$id));
                return;
            }
            
            // Supprimer l'ancienne image si existe
            $section = $this->Model->readOne('sections_contenu', ['id_section' => $id]);
            if ($section && !empty($section['image_url']) && file_exists(FCPATH . 'attachments/Sections/' . basename($section['image_url']))) {
                unlink(FCPATH . 'attachments/Sections/' . basename($section['image_url']));
            }
            
            $data['image_url'] = $new_image;
        } elseif (!empty($this->input->post('image_url'))) {
            $data['image_url'] = $this->input->post('image_url');
        }

        $rsp = $this->Model->update('sections_contenu', ['id_section' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Section mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Sections'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer la section pour supprimer son image
        $section = $this->Model->readOne('sections_contenu', ['id_section' => $id]);
        
        $rsp = $this->Model->delete('sections_contenu', ['id_section' => $id]);

        if ($rsp) {
            // Supprimer l'image physique si existe
            if ($section && !empty($section['image_url']) && file_exists(FCPATH . 'attachments/Sections/' . basename($section['image_url']))) {
                unlink(FCPATH . 'attachments/Sections/' . basename($section['image_url']));
            }
            $this->session->set_flashdata('success', 'Section supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Sections'));
    }

    // Upload images
    public function upload_image($tmp_name, $filename)
    {
        $ref_folder = FCPATH . 'attachments/Sections/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($tmp_name, $ref_folder . $fichier . "." . $file_extension);
        return '/attachments/Sections/' . $fichier . "." . $file_extension;
    }

    /**
     * Upload d'images pour TinyMCE
     */
    public function uploadImage()
    {
        // FORCER le type JSON
        header('Content-Type: application/json');
        
        // Vérifier si un fichier a été uploadé
        if (!isset($_FILES['upload'])) {
            echo json_encode([
                "uploaded" => 0, 
                "error" => ["message" => "Aucun fichier reçu"]
            ]);
            return;
        }

        $tmp_name = $_FILES['upload']['tmp_name'];
        $original_name = $_FILES['upload']['name'];

        // Upload de l'image
        $url = $this->upload_image($tmp_name, $original_name);

        if ($url === NULL) {
            echo json_encode([
                "uploaded" => 0, 
                "error" => ["message" => "Format non autorisé. Utilisez jpg, png, gif, webp"]
            ]);
        } else {
            $full_url = base_url($url);
            echo json_encode([
                "uploaded" => 1,
                "fileName" => basename($url),
                "url" => $full_url
            ]);
        }
    }

    /**
     * Browser d'images pour TinyMCE (optionnel)
     */
    public function browseImages()
    {
        $data['images'] = array();
        $folder = FCPATH . 'attachments/Sections/';
        
        if (is_dir($folder)) {
            $files = scandir($folder);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'webp'])) {
                        $data['images'][] = array(
                            'thumb' => base_url('attachments/Sections/' . $file),
                            'image' => base_url('attachments/Sections/' . $file),
                            'title' => $file,
                            'folder' => 'Sections'
                        );
                    }
                }
            }
        }
        
        $this->load->view('ckeditor_browser', $data);
    }

    /**
     * Activer/Désactiver une section (Toggle Status)
     */
    public function ToggleStatus()
    {
        // Vérifier si la requête est AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // Récupérer l'ID de la section (accepter 'id' ou 'id_section')
        $id = $this->input->post('id_section');
        if (empty($id)) {
            $id = $this->input->post('id');
        }
        
        if (empty($id)) {
            $response = [
                'success' => false,
                'message' => 'ID de section manquant'
            ];
            echo json_encode($response);
            return;
        }

        // Récupérer la section actuelle
        $section = $this->Model->readOne('sections_contenu', ['id_section' => $id]);
        
        if (!$section) {
            $response = [
                'success' => false,
                'message' => 'Section non trouvée'
            ];
            echo json_encode($response);
            return;
        }

        // Inverser le statut est_active
        $new_status = $section['est_active'] == 1 ? 0 : 1;
        
        // Mettre à jour la section
        $update_data = [
            'est_active' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->Model->update('sections_contenu', ['id_section' => $id], $update_data);
        
        if ($result) {
            $status_text = $new_status == 1 ? 'activée' : 'désactivée';
            $response = [
                'success' => true,
                'message' => 'Section ' . $status_text . ' avec succès',
                'new_status' => $new_status
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut'
            ];
        }
        
        echo json_encode($response);
    }
}
?>