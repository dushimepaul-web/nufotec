<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre extends MY_Controller {

    public function __construct() {
        parent::__construct();
        is_admin();
        $this->load->model('autre_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form', 'text']);
    }

    // ============ ADMIN LISTE (Vue principale) ============

    public function admin_liste($offset = 0) {
        // Vérifier connexion admin si nécessaire
        // $this->_check_admin();
        
        // Gestion du filtre
        $filtre = $this->input->get('filtre');
        $types_valides = ['photo', 'book', 'texte', 'link', 'other'];
        
        $config['base_url'] = base_url('Autre/admin_liste');
        $config['per_page'] = 12;
        
        if ($filtre && in_array($filtre, $types_valides)) {
            $data['medias'] = $this->autre_model->get_by_sous_type($filtre, $config['per_page'], $offset);
            $config['total_rows'] = count($this->autre_model->get_by_sous_type($filtre));
            $data['filtre_actif'] = $filtre;
        } else {
            $data['medias'] = $this->autre_model->get_all($config['per_page'], $offset);
            $config['total_rows'] = $this->autre_model->count_all();
        }
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $data['title'] = 'Gestion - Autres Médias';
        $data['pagination'] = $this->pagination->create_links();
        
        $this->load->view('Autre_View', $data);
    }

    // ============ AJOUTER ============

    public function admin_ajouter() {
        if ($this->input->post()) {
            $this->_validation_form();
            
            if ($this->form_validation->run()) {
                $insert_data = $this->_prepare_data();
                $sous_type = $this->input->post('sous_type');
                
                // Traitement selon le type
                if ($sous_type === 'link') {
                    // Lien externe
                    $insert_data['lien'] = $this->input->post('lien');
                    $insert_data['miniature'] = $this->input->post('miniature_externe');
                    
                } elseif ($sous_type === 'texte') {
                    // Texte pur - pas de fichier
                    $insert_data['contenu_texte'] = $this->input->post('contenu_texte');
                    
                } else {
                    // Upload fichier (photo, book, other)
                    if (!empty($_FILES['fichier']['name'])) {
                        $fichier = $this->_upload_fichier();
                        
                        if ($fichier) {
                            $insert_data['fichier'] = $fichier;
                            $insert_data['taille'] = $_FILES['fichier']['size'];
                            $insert_data['mime_type'] = $_FILES['fichier']['type'];
                        }
                    }
                    
                    // Upload miniature personnalisée
                    if (!empty($_FILES['miniature']['name'])) {
                        $miniature = $this->_upload_miniature();
                        if ($miniature) {
                            $insert_data['miniature'] = $miniature;
                        }
                    }
                }
                
                $id = $this->autre_model->insert($insert_data);
                
                if ($id) {
                    $this->session->set_flashdata('success', 'Média ajouté avec succès');
                } else {
                    $this->session->set_flashdata('error', 'Erreur lors de l\'ajout');
                }
                
                redirect('Autre/admin_liste');
            }
        }
        
        redirect('autre/admin_liste');
    }

    // ============ MODIFIER ============

    public function admin_modifier($id) {
        if ($this->input->post()) {
            $this->_validation_form();
            
            if ($this->form_validation->run()) {
                $media = $this->autre_model->get_by_id($id);
                
                if (!$media) {
                    $this->session->set_flashdata('error', 'Média introuvable');
                    redirect('autre/admin_liste');
                }
                
                $update_data = $this->_prepare_data();
                $sous_type = $this->input->post('sous_type');
                
                // Traitement selon le type
                if ($sous_type === 'link') {
                    $update_data['lien'] = $this->input->post('lien');
                    $update_data['miniature'] = $this->input->post('miniature_externe');
                    // Supprimer ancien fichier si existait
                    if (!empty($media->fichier)) {
                        $this->_supprimer_fichier($media->fichier);
                        $update_data['fichier'] = null;
                        $update_data['taille'] = null;
                        $update_data['mime_type'] = null;
                    }
                    
                } elseif ($sous_type === 'texte') {
                    $update_data['contenu_texte'] = $this->input->post('contenu_texte');
                    // Supprimer ancien fichier si existait
                    if (!empty($media->fichier)) {
                        $this->_supprimer_fichier($media->fichier);
                        $update_data['fichier'] = null;
                        $update_data['taille'] = null;
                        $update_data['mime_type'] = null;
                    }
                    
                } else {
                    // Nouveau fichier uploadé ?
                    if (!empty($_FILES['fichier']['name'])) {
                        $fichier = $this->_upload_fichier();
                        
                        if ($fichier) {
                            // Supprimer ancien fichier
                            if (!empty($media->fichier)) {
                                $this->_supprimer_fichier($media->fichier);
                            }
                            
                            $update_data['fichier'] = $fichier;
                            $update_data['taille'] = $_FILES['fichier']['size'];
                            $update_data['mime_type'] = $_FILES['fichier']['type'];
                        }
                    }
                    
                    // Nouvelle miniature ?
                    if (!empty($_FILES['miniature']['name'])) {
                        $miniature = $this->_upload_miniature();
                        if ($miniature) {
                            if (!empty($media->miniature)) {
                                $this->_supprimer_fichier($media->miniature);
                            }
                            $update_data['miniature'] = $miniature;
                        }
                    }
                }
                
                $this->autre_model->update($id, $update_data);
                $this->session->set_flashdata('success', 'Média modifié avec succès');
                redirect('autre/admin_liste');
            }
        }
        
        redirect('autre/admin_liste');
    }

    // ============ SUPPRIMER ============

    public function admin_supprimer($id) {
        $media = $this->autre_model->get_by_id($id);
        
        if ($media) {
            // Supprimer les fichiers physiques
            if (!empty($media->fichier)) {
                $this->_supprimer_fichier($media->fichier);
            }
            if (!empty($media->miniature)) {
                $this->_supprimer_fichier($media->miniature);
            }
            
            $this->autre_model->delete($id);
            $this->session->set_flashdata('success', 'Média supprimé');
        } else {
            $this->session->set_flashdata('error', 'Média introuvable');
        }
        
        redirect('autre/admin_liste');
    }

    // ============ AJAX - GET JSON ============

    public function get_json($id) {
        $media = $this->autre_model->get_by_id($id);
        
        if ($media) {
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(['success' => true, 'media' => $media]));
        } else {
            $this->output->set_status_header(404);
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Non trouvé']));
        }
    }

    // ============ MÉTHODES PRIVÉES ============

    private function _validation_form() {
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('sous_type', 'Type', 'required|trim');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('categorie', 'Catégorie', 'trim|max_length[100]');
        
        $sous_type = $this->input->post('sous_type');
        
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien externe', 'required|valid_url');
        } elseif ($sous_type === 'texte') {
            $this->form_validation->set_rules('contenu_texte', 'Contenu', 'required|trim');
        }
    }

    private function _prepare_data() {
        $titre = $this->input->post('titre');
        $id = $this->input->post('id_media');
        
        return [
            'titre' => $titre,
            'slug' => $this->autre_model->generate_slug($titre, $id),
            'sous_type' => $this->input->post('sous_type'),
            'description' => $this->input->post('description'),
            'categorie' => $this->input->post('categorie'),
            'est_actif' => $this->input->post('est_actif') ? 1 : 0,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
        ];
    }

    private function _upload_fichier() {
        $ref_folder = FCPATH . 'attachments/autre/files/';
        
        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }
        
        $code = date("YmdHis") . uniqid();
        $file_extension = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
        $filename = $code . '.' . $file_extension;
        $filepath = $ref_folder . $filename;
        
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp', 'svg', 'pdf', 'doc', 'docx', 'txt', 'zip', 'mp4', 'mp3'];
        
        if (!in_array($file_extension, $valid_ext)) {
            return null;
        }
        
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $filepath)) {
            return 'attachments/autre/files/' . $filename;
        }
        
        return null;
    }

    private function _upload_miniature() {
        $ref_folder = FCPATH . 'attachments/autre/thumbnails/';
        
        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }
        
        $code = date("YmdHis") . uniqid();
        $file_extension = strtolower(pathinfo($_FILES['miniature']['name'], PATHINFO_EXTENSION));
        $filename = $code . '.' . $file_extension;
        $filepath = $ref_folder . $filename;
        
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp'];
        
        if (!in_array($file_extension, $valid_ext)) {
            return null;
        }
        
        if (move_uploaded_file($_FILES['miniature']['tmp_name'], $filepath)) {
            return 'attachments/autre/thumbnails/' . $filename;
        }
        
        return null;
    }

    private function _supprimer_fichier($chemin_relatif) {
        $chemin_complet = FCPATH . $chemin_relatif;
        if (file_exists($chemin_complet)) {
            unlink($chemin_complet);
        }
    }
}