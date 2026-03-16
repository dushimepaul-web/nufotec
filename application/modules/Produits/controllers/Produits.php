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
        $data['produits'] = $this->Model->read('produits', null, 'ordre_affichage', 'ASC');
        $data['categories'] = $this->Model->read('categories', null, 'nom_categorie', 'ASC');
        $this->load->view('Produits_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        
        $status = ($est_actif == 1) ? 0 : 1;
        $rsp = $this->Model->update('produits', ['id_produit' => $id], ['est_actif' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Produits'));    
    }

    function ProduitDetail($produitDetail){
        $id = explode('_', $produitDetail);
        $data['detail'] = $this->Model->readOne('produits', ['id_produit' => $id[0]]);
        $data['categories'] = $this->Model->read('categories', null, 'nom_categorie', 'ASC');
        $this->load->view('ProduitDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('nom_produit', 'Nom du Produit', 'required|max_length[255]');
        $this->form_validation->set_rules('slug', 'Slug', 'required|max_length[255]|is_unique[produits.slug]');
        $this->form_validation->set_rules('id_categorie', 'Catégorie', 'required|integer');
        $this->form_validation->set_rules('description_courte', 'Description Courte', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Produits'));
            return;
        }

        $slug = $this->create_slug($this->input->post('nom_produit'));
        
        // Vérifier si le slug existe déjà
        $existing = $this->Model->readOne('produits', ['slug' => $slug]);
        if ($existing) {
            $slug = $slug . '-' . time();
        }

        $data = array(
            'id_categorie' => $this->input->post('id_categorie'),
            'nom_produit' => $this->input->post('nom_produit'),
            'slug' => $slug,
            'description_courte' => $this->input->post('description_courte'),
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
            'ordre_affichage' => $this->input->post('ordre_affichage') ?: 0,
            'est_actif' => 1
        );

        // Gestion des champs JSON
        $certifications = $this->input->post('certifications');
        if (!empty($certifications)) {
            $data['certifications'] = json_encode(array_map('trim', explode(',', $certifications)));
        }

        // Upload image principale
        if (!empty($_FILES['image_principale']['name'])) {
            $image = $this->upload_image($_FILES['image_principale']['tmp_name'], $_FILES['image_principale']['name'], 'Produits');
            if ($image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Produits'));
                return;
            }
            $data['image_principale'] = $image;
        }

        // Upload fiche technique
        if (!empty($_FILES['fiche_technique']['name'])) {
            $fiche = $this->upload_file($_FILES['fiche_technique']['tmp_name'], $_FILES['fiche_technique']['name'], 'Fiches');
            if ($fiche === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide pour la fiche technique. Formats acceptés: pdf, doc, docx');
                redirect(base_url('Produits'));
                return;
            }
            $data['fiche_technique_url'] = $fiche;
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
        
        // Validation
        $this->form_validation->set_rules('nom_produit', 'Nom du Produit', 'required|max_length[255]');
        $this->form_validation->set_rules('id_categorie', 'Catégorie', 'required|integer');
        $this->form_validation->set_rules('description_courte', 'Description Courte', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Produits'));
            return;
        }

        $data = array(
            'id_categorie' => $this->input->post('id_categorie'),
            'nom_produit' => $this->input->post('nom_produit'),
            'description_courte' => $this->input->post('description_courte'),
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
            'ordre_affichage' => $this->input->post('ordre_affichage') ?: 0,
            'est_actif' => $this->input->post('est_actif') ? 1 : 0
        );

        // Gestion des champs JSON
        $certifications = $this->input->post('certifications');
        if (!empty($certifications)) {
            $data['certifications'] = json_encode(array_map('trim', explode(',', $certifications)));
        }

        // Upload nouvelle image principale si fournie
        if (!empty($_FILES['image_principale']['name'])) {
            $new_image = $this->upload_image($_FILES['image_principale']['tmp_name'], $_FILES['image_principale']['name'], 'Produits');
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Produits'));
                return;
            }
            
            // Supprimer l'ancienne image si existe
            $produit = $this->Model->readOne('produits', ['id_produit' => $id]);
            if ($produit && !empty($produit['image_principale']) && file_exists(FCPATH . 'attachments/Produits/' . $produit['image_principale'])) {
                unlink(FCPATH . 'attachments/Produits/' . $produit['image_principale']);
            }
            
            $data['image_principale'] = $new_image;
        }

        // Upload nouvelle fiche technique si fournie
        if (!empty($_FILES['fiche_technique']['name'])) {
            $new_fiche = $this->upload_file($_FILES['fiche_technique']['tmp_name'], $_FILES['fiche_technique']['name'], 'Fiches');
            if ($new_fiche === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide pour la fiche technique. Formats acceptés: pdf, doc, docx');
                redirect(base_url('Produits'));
                return;
            }
            
            // Supprimer l'ancienne fiche si existe
            $produit = $this->Model->readOne('produits', ['id_produit' => $id]);
            if ($produit && !empty($produit['fiche_technique_url']) && file_exists(FCPATH . 'attachments/Fiches/' . $produit['fiche_technique_url'])) {
                unlink(FCPATH . 'attachments/Fiches/' . $produit['fiche_technique_url']);
            }
            
            $data['fiche_technique_url'] = $new_fiche;
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
        
        // Récupérer le produit pour supprimer ses fichiers
        $produit = $this->Model->readOne('produits', ['id_produit' => $id]);
        
        $rsp = $this->Model->delete('produits', ['id_produit' => $id]);

        if ($rsp) {
            // Supprimer l'image physique si existe
            if ($produit && !empty($produit['image_principale']) && file_exists(FCPATH . 'attachments/Produits/' . $produit['image_principale'])) {
                unlink(FCPATH . 'attachments/Produits/' . $produit['image_principale']);
            }
            // Supprimer la fiche technique si existe
            if ($produit && !empty($produit['fiche_technique_url']) && file_exists(FCPATH . 'attachments/Fiches/' . $produit['fiche_technique_url'])) {
                unlink(FCPATH . 'attachments/Fiches/' . $produit['fiche_technique_url']);
            }
            $this->session->set_flashdata('success', 'Produit supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Produits'));
    }

    // Créer un slug à partir du nom
    private function create_slug($string) {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    // Upload images
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

    // Upload fichiers (PDF, DOC)
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




    
// Envoi newsletter à tous les abonnés
    private function sendNewsletter($id, $title, $details)
    {
        $subscribers = $this->Model->read('newsletter');
        if (!$subscribers) return;

        $smtp_pass = $this->Model->get_setting('smtp_password', '');
        $smtp_email = $this->Model->get_setting('smtp_email', 'noreply@agf-phytomed.com');
        $site_name = $this->Model->get_setting('site_name', 'AGF Phytomed');
        $admin_email = $this->Model->get_setting('admin_email', 'partnerships@agf-phytomed.com');
        $whatsapp = $this->Model->get_setting('site_phone', '68863945'); 
        $logo_url = base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png'));

        $config = [
            'protocol'    => 'smtp',
            'smtp_host'   => 'smtp.gmail.com',
            'smtp_port'   => 587,
            'smtp_user'   => $smtp_email,
            'smtp_pass'   => $smtp_pass,
            'smtp_crypto' => 'tls',
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n"
        ];

        $this->email->initialize($config);

        foreach ($subscribers as $row) {
            $this->email->clear(TRUE);
            $this->email->from($smtp_email, $site_name);
            $this->email->to($row['email']);
            $this->email->subject($title);
            $this->email->message($this->newsletterTemplate($id, $title, $details, $logo_url, $site_name));
            $this->email->send();
        }
    }

    // Template HTML newsletter
    private function newsletterTemplate($id, $title, $details, $logo_url, $site_name)
    {
        return "
        <div style='font-family: Arial, sans-serif; text-align: center; background-color: #f8f9fa; padding: 20px;'>
            <img src='{$logo_url}' alt='{$site_name}' style='max-height: 60px; margin-bottom: 20px;'>
            <h2>{$title}</h2>
            <p>{$details}</p>
            <a href='".base_url('Home/News_detail/'.$id)."' style='display:inline-block; margin-top: 10px; padding: 10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px;'>Voir les détails</a>
            <p style='font-size:12px; color:#777; margin-top:20px;'>&copy; ".date('Y')." {$site_name}</p>
        </div>";
    }

}