<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('autre_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form', 'text']);
    }

    // ============ FRONTEND ============

    // Liste publique des médias "Autre"
    public function index() {
        $data['title'] = 'Galerie - Autres Médias';
        $data['medias'] = $this->autre_model->get_all();
        $data['stats'] = $this->autre_model->get_stats();
        
        $this->load->view('templates/header', $data);
        $this->load->view('autre/index', $data);
        $this->load->view('templates/footer');
    }

    // Voir un média spécifique
    public function voir($slug) {
        $data['media'] = $this->autre_model->get_by_slug($slug);
        
        if (!$data['media']) {
            show_404();
        }
        
        $data['title'] = $data['media']->titre;
        
        $this->load->view('templates/header', $data);
        $this->load->view('autre/detail', $data);
        $this->load->view('templates/footer');
    }

    // Filtrer par sous_type
    public function categorie($sous_type) {
        $types_valides = ['photo', 'book', 'texte', 'link', 'other'];
        
        if (!in_array($sous_type, $types_valides)) {
            show_404();
        }
        
        $data['title'] = 'Catégorie : ' . ucfirst($sous_type);
        $data['medias'] = $this->autre_model->get_by_sous_type($sous_type);
        $data['categorie_active'] = $sous_type;
        
        $this->load->view('templates/header', $data);
        $this->load->view('autre/index', $data);
        $this->load->view('templates/footer');
    }

    // ============ ADMIN ============

    // Liste admin avec pagination
    public function admin_liste($offset = 0) {
        // Vérifier connexion admin ici...
        
        $config['base_url'] = base_url('autre/admin_liste');
        $config['total_rows'] = $this->autre_model->count_all();
        $config['per_page'] = 10;
        
        $this->load->library('pagination', $config);
        
        $data['title'] = 'Gestion - Autres Médias';
        $data['medias'] = $this->autre_model->get_all($config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('autre/admin/liste', $data);
        $this->load->view('admin/templates/footer');
    }

    // Formulaire d'ajout
    public function admin_ajouter() {
        // Vérifier connexion admin...
        
        $data['title'] = 'Ajouter un média';
        $data['action'] = 'ajouter';
        
        if ($this->input->post()) {
            $this->_validation_form();
            
            if ($this->form_validation->run()) {
                $insert_data = $this->_prepare_data();
                
                // Gestion de l'upload selon le type
                $sous_type = $this->input->post('sous_type');
                
                if ($sous_type === 'link') {
                    // Lien externe
                    $insert_data['lien'] = $this->input->post('lien');
                    $insert_data['miniature'] = $this->input->post('miniature_externe');
                } else {
                    // Upload fichier
                    if (!empty($_FILES['fichier']['name'])) {
                        $fichier = $this->autre_model->upload_fichier(
                            $_FILES['fichier']['tmp_name'],
                            $_FILES['fichier']['name']
                        );
                        
                        if ($fichier) {
                            $insert_data['fichier'] = $fichier;
                            $insert_data['taille'] = $_FILES['fichier']['size'];
                            $insert_data['mime_type'] = $_FILES['fichier']['type'];
                            
                            // Détecter sous_type si non spécifié
                            if (empty($sous_type)) {
                                $insert_data['sous_type'] = $this->autre_model->detecter_sous_type(
                                    $_FILES['fichier']['name'],
                                    $_FILES['fichier']['type']
                                );
                            }
                        }
                    }
                    
                    // Upload miniature personnalisée (optionnel)
                    if (!empty($_FILES['miniature']['name'])) {
                        $miniature = $this->autre_model->upload_miniature(
                            $_FILES['miniature']['tmp_name'],
                            $_FILES['miniature']['name']
                        );
                        if ($miniature) {
                            $insert_data['miniature'] = $miniature;
                        }
                    }
                }
                
                $id = $this->autre_model->insert($insert_data);
                
                if ($id) {
                    $this->session->set_flashdata('success', 'Média ajouté avec succès');
                    redirect('autre/admin_liste');
                } else {
                    $data['error'] = 'Erreur lors de l\'ajout';
                }
            }
        }
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('autre/admin/form', $data);
        $this->load->view('admin/templates/footer');
    }

    // Formulaire de modification
    public function admin_modifier($id) {
        // Vérifier connexion admin...
        
        $data['media'] = $this->autre_model->get_by_id($id);
        
        if (!$data['media']) {
            show_404();
        }
        
        $data['title'] = 'Modifier : ' . $data['media']->titre;
        $data['action'] = 'modifier';
        
        if ($this->input->post()) {
            $this->_validation_form();
            
            if ($this->form_validation->run()) {
                $update_data = $this->_prepare_data();
                
                // Gestion fichier si nouveau upload
                if (!empty($_FILES['fichier']['name'])) {
                    $fichier = $this->autre_model->upload_fichier(
                        $_FILES['fichier']['tmp_name'],
                        $_FILES['fichier']['name']
                    );
                    
                    if ($fichier) {
                        // Supprimer ancien fichier
                        if (!empty($data['media']->fichier) && file_exists(FCPATH . $data['media']->fichier)) {
                            unlink(FCPATH . $data['media']->fichier);
                        }
                        
                        $update_data['fichier'] = $fichier;
                        $update_data['taille'] = $_FILES['fichier']['size'];
                        $update_data['mime_type'] = $_FILES['fichier']['type'];
                    }
                }
                
                // Gestion nouvelle miniature
                if (!empty($_FILES['miniature']['name'])) {
                    $miniature = $this->autre_model->upload_miniature(
                        $_FILES['miniature']['tmp_name'],
                        $_FILES['miniature']['name']
                    );
                    if ($miniature) {
                        // Supprimer ancienne miniature
                        if (!empty($data['media']->miniature) && file_exists(FCPATH . $data['media']->miniature)) {
                            unlink(FCPATH . $data['media']->miniature);
                        }
                        $update_data['miniature'] = $miniature;
                    }
                }
                
                $this->autre_model->update($id, $update_data);
                $this->session->set_flashdata('success', 'Média modifié avec succès');
                redirect('autre/admin_liste');
            }
        }
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('autre/admin/form', $data);
        $this->load->view('admin/templates/footer');
    }

    // Suppression
    public function admin_supprimer($id) {
        // Vérifier connexion admin...
        
        $media = $this->autre_model->get_by_id($id);
        
        if ($media && $this->autre_model->delete($id)) {
            $this->session->set_flashdata('success', 'Média supprimé');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression');
        }
        
        redirect('autre/admin_liste');
    }

    // Changer statut actif/inactif
    public function admin_toggle_status($id) {
        $media = $this->autre_model->get_by_id($id);
        
        if ($media) {
            $new_status = $media->est_actif ? 0 : 1;
            $this->autre_model->update($id, ['est_actif' => $new_status]);
        }
        
        redirect('autre/admin_liste');
    }

    // ============ MÉTHODES PRIVÉES ============

    private function _validation_form() {
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('categorie', 'Catégorie', 'trim|max_length[100]');
        $this->form_validation->set_rules('credits', 'Crédits', 'trim|max_length[255]');
        $this->form_validation->set_rules('date_media', 'Date', 'trim');
        
        if ($this->input->post('sous_type') === 'link') {
            $this->form_validation->set_rules('lien', 'Lien externe', 'required|valid_url');
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
            'date_media' => $this->input->post('date_media') ?: null,
            'credits' => $this->input->post('credits'),
            'est_actif' => $this->input->post('est_actif') ? 1 : 0,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux'),
            'contenu_texte' => $this->input->post('contenu_texte')
        ];
    }
}