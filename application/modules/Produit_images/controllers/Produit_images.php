<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produit_images extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index($id_produit = null)
    {
        // Si un produit est spécifié, afficher seulement ses images
        if ($id_produit) {
            $data['produit'] = $this->Model->readOne('produits', ['id_produit' => $id_produit]);
            $data['images'] = $this->Model->read('produit_images', ['id_produit' => $id_produit], 'ordre_affichage', 'ASC');
            $data['mode'] = 'single';
        } else {
            // Sinon, afficher toutes les images avec pagination
            $data['images'] = $this->Model->read('produit_images', null, 'created_at', 'DESC');
            $data['produits'] = $this->Model->read('produits', null, 'nom_produit', 'ASC');
            $data['mode'] = 'all';
        }
        
        // Calcul des statistiques
        $data['stats'] = $this->calculate_stats();
        
        $this->load->view('Produit_images_View', $data);
    }

    private function calculate_stats() {
        $all_images = $this->Model->read('produit_images');
        
        $stats = [
            'total_images' => count($all_images),
            'images_principales' => 0,
            'images_galerie' => 0,
            'produits_avec_images' => [],
            'moyenne_par_produit' => 0
        ];
        
        $images_par_produit = [];
        
        foreach ($all_images as $img) {
            if ($img['est_principale']) {
                $stats['images_principales']++;
            } else {
                $stats['images_galerie']++;
            }
            
            if (!in_array($img['id_produit'], $stats['produits_avec_images'])) {
                $stats['produits_avec_images'][] = $img['id_produit'];
            }
            
            if (!isset($images_par_produit[$img['id_produit']])) {
                $images_par_produit[$img['id_produit']] = 0;
            }
            $images_par_produit[$img['id_produit']]++;
        }
        
        if (count($images_par_produit) > 0) {
            $stats['moyenne_par_produit'] = round(array_sum($images_par_produit) / count($images_par_produit), 1);
        }
        
        return $stats;
    }

    function SetPrincipale(){
        $id = $this->input->post('id');
        $id_produit = $this->input->post('id_produit');
        
        // Désactiver toutes les autres images principales pour ce produit
        $this->Model->update('produit_images', 
            ['id_produit' => $id_produit, 'est_principale' => 1], 
            ['est_principale' => 0]
        );
        
        // Définir celle-ci comme principale
        $rsp = $this->Model->update('produit_images', ['id_image' => $id], ['est_principale' => 1]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Image principale définie avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('Produit_images/index/' . $id_produit));
    }

    function UpdateOrder(){
        $orders = $this->input->post('ordre');
        
        if (is_array($orders)) {
            foreach ($orders as $id => $ordre) {
                $this->Model->update('produit_images', ['id_image' => $id], ['ordre_affichage' => $ordre]);
            }
            $this->session->set_flashdata('success', 'Ordre des images mis à jour.');
        }
        
        redirect($this->input->server('HTTP_REFERER'));
    }

    function Create(){
        $this->form_validation->set_rules('id_produit', 'Produit', 'required|integer');
        
        if (empty($_FILES['images']['name'][0]) && $this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'Veuillez sélectionner au moins une image.');
            redirect(base_url('Produit_images'));
            return;
        }

        $id_produit = $this->input->post('id_produit');
        $produit = $this->Model->readOne('produits', ['id_produit' => $id_produit]);
        
        if (!$produit) {
            $this->session->set_flashdata('error', 'Produit non trouvé.');
            redirect(base_url('Produit_images'));
            return;
        }

        $upload_count = 0;
        $errors = [];
        
        // Traitement des fichiers multiples
        if (!empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $file_count = count($files['name']);
            
            // Vérifier si une image principale existe déjà
            $existing_main = $this->Model->readOne('produit_images', [
                'id_produit' => $id_produit, 
                'est_principale' => 1
            ]);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] === 0) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    $filename = $this->upload_image($file);
                    
                    if ($filename) {
                        // Première image = principale si aucune n'existe
                        $is_main = ($i === 0 && !$existing_main) ? 1 : 0;
                        
                        $data = [
                            'id_produit' => $id_produit,
                            'nom_fichier' => $filename,
                            'legende' => $this->input->post('legende') ?: null,
                            'alt_text' => $this->input->post('alt_text') ?: $produit['nom_produit'],
                            'ordre_affichage' => $this->getNextOrder($id_produit),
                            'est_principale' => $is_main,
                            'est_active' => 1
                        ];
                        
                        $this->Model->create('produit_images', $data);
                        $upload_count++;
                    } else {
                        $errors[] = $file['name'] . ' : format invalide';
                    }
                }
            }
        }

        if ($upload_count > 0) {
            $this->session->set_flashdata('success', $upload_count . ' image(s) uploadée(s) avec succès.');
        }
        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode('<br>', $errors));
        }
        
        redirect(base_url('Produit_images/index/' . $id_produit));
    }

    function Update(){
        $id = $this->input->post('id_image');
        
        $data = [
            'legende' => $this->input->post('legende') ?: null,
            'alt_text' => $this->input->post('alt_text') ?: null,
            'ordre_affichage' => $this->input->post('ordre_affichage') ?: 0,
            'est_active' => $this->input->post('est_active') ? 1 : 0
        ];

        // Upload nouvelle image si fournie
        if (!empty($_FILES['image']['name'])) {
            $new_image = $this->upload_image($_FILES['image']);
            if ($new_image) {
                // Supprimer l'ancienne image
                $old = $this->Model->readOne('produit_images', ['id_image' => $id]);
                if ($old && file_exists(FCPATH . 'attachments/Produits/Images/' . $old['nom_fichier'])) {
                    unlink(FCPATH . 'attachments/Produits/Images/' . $old['nom_fichier']);
                }
                $data['nom_fichier'] = $new_image;
            }
        }

        $rsp = $this->Model->update('produit_images', ['id_image' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Image mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la mise à jour.');
        }
        
        redirect($this->input->server('HTTP_REFERER'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $image = $this->Model->readOne('produit_images', ['id_image' => $id]);
        
        $rsp = $this->Model->delete('produit_images', ['id_image' => $id]);

        if ($rsp) {
            // Supprimer le fichier physique
            if ($image && file_exists(FCPATH . 'attachments/Produits/Images/' . $image['nom_fichier'])) {
                unlink(FCPATH . 'attachments/Produits/Images/' . $image['nom_fichier']);
            }
            
            // Si c'était l'image principale, définir une nouvelle principale
            if ($image && $image['est_principale']) {
                $next = $this->Model->readOne('produit_images', [
                    'id_produit' => $image['id_produit'],
                    'est_active' => 1
                ], 'ordre_affichage', 'ASC');
                
                if ($next) {
                    $this->Model->update('produit_images', ['id_image' => $next['id_image']], ['est_principale' => 1]);
                }
            }
            
            $this->session->set_flashdata('success', 'Image supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        
        redirect($this->input->server('HTTP_REFERER'));
    }

    function ToggleActive(){
        $id = $this->input->post('id');
        $current = $this->Model->readOne('produit_images', ['id_image' => $id]);
        
        if ($current) {
            $new_status = $current['est_active'] ? 0 : 1;
            $this->Model->update('produit_images', ['id_image' => $id], ['est_active' => $new_status]);
            $this->session->set_flashdata('success', 'Statut mis à jour.');
        }
        
        redirect($this->input->server('HTTP_REFERER'));
    }

    private function getNextOrder($id_produit) {
        $last = $this->Model->read(
            'produit_images', 
            ['id_produit' => $id_produit], 
            'ordre_affichage', 
            'DESC',
            1
        );
        return $last ? ($last[0]['ordre_affichage'] + 1) : 0;
    }

    private function upload_image($file) {
        $folder = FCPATH . 'attachments/Produits/Images/';
        
        if (!is_dir($folder)) {
            mkdir($folder, 0777, TRUE);
        }

        $valid_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $valid_types)) {
            return false;
        }

        if ($file['size'] > $max_size) {
            return false;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = date('YmdHis') . uniqid() . '.' . strtolower($ext);

        if (move_uploaded_file($file['tmp_name'], $folder . $filename)) {
            return $filename;
        }
        
        return false;
    }

    // API pour galerie frontend
    function getProductImages($id_produit) {
        $images = $this->Model->read(
            'produit_images',
            ['id_produit' => $id_produit, 'est_active' => 1],
            'ordre_affichage',
            'ASC'
        );
        echo json_encode($images);
    }
}