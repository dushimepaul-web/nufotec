<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produits extends MY_Controller {

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
        $data['produits'] = $this->Model->read('produits', [], 'ordre_affichage', 'ASC');
        $data['categories'] = $this->Model->read('categories_produits', [], 'nom_categorie', 'ASC');
        $this->load->view('Produits_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $statut = $this->input->post('statut');
        
        $nouveau_statut = ($statut == 'commercialise') ? 'rupture' : 'commercialise';
        $rsp = $this->Model->update('produits', ['id_produit' => $id], ['statut' => $nouveau_statut]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut du produit mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Produits'));    
    }

    function Create(){
        $this->form_validation->set_rules('nom_produit', 'Nom du produit', 'required');
        $this->form_validation->set_rules('slug', 'Slug', 'required|is_unique[produits.slug]');
        $this->form_validation->set_rules('id_categorie', 'Catégorie', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Produits'));
            return;
        }

        $slug = $this->input->post('slug');
        // Vérifier si le slug existe déjà
        $existing = $this->Model->readOne('produits', ['slug' => $slug]);
        if ($existing) {
            $slug = $slug . '-' . time();
        }

        $data = array(
            'id_categorie' => $this->input->post('id_categorie'),
            'nom_produit' => $this->input->post('nom_produit'),
            'slug' => $slug,
            'description_courte' => $this->input->post('description_courte') ?: NULL,
            'description_longue' => $this->input->post('description_longue') ?: NULL,
            'composition' => $this->input->post('composition') ?: NULL,
            'indications' => $this->input->post('indications') ?: NULL,
            'contre_indications' => $this->input->post('contre_indications') ?: NULL,
            'mode_emploi' => $this->input->post('mode_emploi') ?: NULL,
            'presentation' => $this->input->post('presentation') ?: NULL,
            'conditionnement' => $this->input->post('conditionnement') ?: NULL,
            'prix_public' => $this->input->post('prix_public') ?: NULL,
            'prix_grossiste' => $this->input->post('prix_grossiste') ?: NULL,
            'statut' => $this->input->post('statut') ?: 'commercialise',
            'date_lancement' => $this->input->post('date_lancement') ?: NULL,
            'est_vedette' => $this->input->post('est_vedette') ? 1 : 0,
            'ordre_affichage' => $this->input->post('ordre_affichage') ?: 0
        );

        // Upload image principale si fournie
        if (!empty($_FILES['image_principale']['name'])) {
            $image = $this->upload_image($_FILES['image_principale']['tmp_name'], $_FILES['image_principale']['name'], 'Produits');
            if ($image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Produits'));
                return;
            }
            $data['image_principale'] = $image;
        }

        // Upload fiche technique si fournie
        if (!empty($_FILES['fiche_technique_url']['name'])) {
            $fiche = $this->upload_file($_FILES['fiche_technique_url']['tmp_name'], $_FILES['fiche_technique_url']['name'], 'Fiches');
            if ($fiche === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide pour la fiche technique. Formats acceptés: pdf, doc, docx');
                redirect(base_url('Produits'));
                return;
            }
            $data['fiche_technique_url'] = $fiche;
        }

        // Gestion certifications (JSON)
        $certifications = $this->input->post('certifications');
        if (!empty($certifications) && is_array($certifications)) {
            $data['certifications'] = json_encode($certifications);
        }

        $rsp = $this->Model->create('produits', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Produit créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création du produit.');
        }
        redirect(base_url('Produits'));
    }

    function Update(){
        $id = $this->input->post('id_produit');
        
        $this->form_validation->set_rules('nom_produit', 'Nom du produit', 'required');
        $this->form_validation->set_rules('id_categorie', 'Catégorie', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Produits'));
            return;
        }

        $data = array(
            'id_categorie' => $this->input->post('id_categorie'),
            'nom_produit' => $this->input->post('nom_produit'),
            'description_courte' => $this->input->post('description_courte') ?: NULL,
            'description_longue' => $this->input->post('description_longue') ?: NULL,
            'composition' => $this->input->post('composition') ?: NULL,
            'indications' => $this->input->post('indications') ?: NULL,
            'contre_indications' => $this->input->post('contre_indications') ?: NULL,
            'mode_emploi' => $this->input->post('mode_emploi') ?: NULL,
            'presentation' => $this->input->post('presentation') ?: NULL,
            'conditionnement' => $this->input->post('conditionnement') ?: NULL,
            'prix_public' => $this->input->post('prix_public') ?: NULL,
            'prix_grossiste' => $this->input->post('prix_grossiste') ?: NULL,
            'statut' => $this->input->post('statut') ?: 'commercialise',
            'date_lancement' => $this->input->post('date_lancement') ?: NULL,
            'est_vedette' => $this->input->post('est_vedette') ? 1 : 0,
            'ordre_affichage' => $this->input->post('ordre_affichage') ?: 0
        );

        // Gestion du slug
        $new_slug = $this->input->post('slug');
        $current = $this->Model->readOne('produits', ['id_produit' => $id]);
        if ($new_slug != $current['slug']) {
            $existing = $this->Model->readOne('produits', ['slug' => $new_slug, 'id_produit !=' => $id]);
            if ($existing) {
                $new_slug = $new_slug . '-' . time();
            }
            $data['slug'] = $new_slug;
        }

        // Upload nouvelle image principale si fournie
        if (!empty($_FILES['image_principale']['name'])) {
            $image = $this->upload_image($_FILES['image_principale']['tmp_name'], $_FILES['image_principale']['name'], 'Produits');
            if ($image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Produits'));
                return;
            }
            
            // Supprimer l'ancienne image
            if (!empty($current['image_principale']) && file_exists(FCPATH . 'attachments/Produits/' . $current['image_principale'])) {
                unlink(FCPATH . 'attachments/Produits/' . $current['image_principale']);
            }
            
            $data['image_principale'] = $image;
        }

        // Upload nouvelle fiche technique si fournie
        if (!empty($_FILES['fiche_technique_url']['name'])) {
            $fiche = $this->upload_file($_FILES['fiche_technique_url']['tmp_name'], $_FILES['fiche_technique_url']['name'], 'Fiches');
            if ($fiche === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide pour la fiche technique. Formats acceptés: pdf, doc, docx');
                redirect(base_url('Produits'));
                return;
            }
            
            // Supprimer l'ancienne fiche
            if (!empty($current['fiche_technique_url']) && file_exists(FCPATH . 'attachments/Fiches/' . $current['fiche_technique_url'])) {
                unlink(FCPATH . 'attachments/Fiches/' . $current['fiche_technique_url']);
            }
            
            $data['fiche_technique_url'] = $fiche;
        }

        // Gestion certifications (JSON)
        $certifications = $this->input->post('certifications');
        if (!empty($certifications) && is_array($certifications)) {
            $data['certifications'] = json_encode($certifications);
        }

        $rsp = $this->Model->update('produits', ['id_produit' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Produit mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Produits'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $produit = $this->Model->readOne('produits', ['id_produit' => $id]);
        
        $rsp = $this->Model->delete('produits', ['id_produit' => $id]);

        if ($rsp) {
            // Supprimer les fichiers associés
            if (!empty($produit['image_principale']) && file_exists(FCPATH . 'attachments/Produits/' . $produit['image_principale'])) {
                unlink(FCPATH . 'attachments/Produits/' . $produit['image_principale']);
            }
            if (!empty($produit['fiche_technique_url']) && file_exists(FCPATH . 'attachments/Fiches/' . $produit['fiche_technique_url'])) {
                unlink(FCPATH . 'attachments/Fiches/' . $produit['fiche_technique_url']);
            }
            $this->session->set_flashdata('success', 'Produit supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Produits'));
    }

    public function upload_image($nom_file, $nom_champ, $folder)
    {
        $ref_folder = FCPATH . 'attachments/' . $folder . '/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }

    public function upload_file($nom_file, $nom_champ, $folder)
    {
        $ref_folder = FCPATH . 'attachments/' . $folder . '/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('pdf', 'doc', 'docx');

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