<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Slides extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // Vérifier admin
       // if ($this->session->userdata('logged_in') !== TRUE || $this->session->userdata('role') !== 'admin') {
        //    redirect('Admin');
       // }
        $this->load->helper('form');
        $this->load->library('upload');
    }

    /**
     * Liste des slides
     */
    public function index()
    {
        $data['slides'] = $this->Model->read('hero_slides', [], 'slide_order', 'ASC');
        $this->load->view('Slides_View', $data);
    }

    /**
     * Créer un nouveau slide
     */
    public function Create()
    {
        if ($this->input->post()) {
            // Upload image
            $image = $this->uploadImage();
            
            if (!$image['success']) {
                $this->session->set_flashdata('error', $image['message']);
                redirect('Slides');
                return;
            }

            $data = [
                'subtitle' => $this->input->post('subtitle'),
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'background_image' => $image['file_name'],
                'button_primary_text' => $this->input->post('button_primary_text') ?: null,
                'button_primary_icon' => $this->input->post('button_primary_icon') ?: null,
                'button_primary_link' => $this->input->post('button_primary_link') ?: null,
                'button_secondary_text' => $this->input->post('button_secondary_text') ?: null,
                'button_secondary_icon' => $this->input->post('button_secondary_icon') ?: null,
                'button_secondary_link' => $this->input->post('button_secondary_link') ?: null,
                'slide_order' => $this->input->post('slide_order') ?: 1,
                'is_active' => $this->input->post('is_active') ? 1 : 0
            ];

            $this->Model->create('hero_slides', $data);
            $this->session->set_flashdata('success', 'Slide créé avec succès');
            redirect('Slides');
        }
    }

    /**
     * Modifier un slide
     */
    public function Update($id)
    {
        if ($this->input->post()) {
            $data = [
                'subtitle' => $this->input->post('subtitle'),
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'button_primary_text' => $this->input->post('button_primary_text') ?: null,
                'button_primary_icon' => $this->input->post('button_primary_icon') ?: null,
                'button_primary_link' => $this->input->post('button_primary_link') ?: null,
                'button_secondary_text' => $this->input->post('button_secondary_text') ?: null,
                'button_secondary_icon' => $this->input->post('button_secondary_icon') ?: null,
                'button_secondary_link' => $this->input->post('button_secondary_link') ?: null,
                'slide_order' => $this->input->post('slide_order') ?: 1,
                'is_active' => $this->input->post('is_active') ? 1 : 0
            ];

            // Nouvelle image ?
            if (!empty($_FILES['background_image']['name'])) {
                $image = $this->uploadImage();
                if ($image['success']) {
                    // Supprimer ancienne image
                    $old = $this->Model->readOne('hero_slides', ['id' => $id]);
                    if ($old && file_exists('./attachments/heros/' . $old['background_image'])) {
                        unlink('./attachments/heros/' . $old['background_image']);
                    }
                    $data['background_image'] = $image['file_name'];
                }
            }

            $this->Model->update('hero_slides', ['id' => $id], $data);
            $this->session->set_flashdata('success', 'Slide mis à jour');
            redirect('Slides');
        }
    }

    /**
     * Supprimer un slide
     */
    public function Delete($id)
    {
        $slide = $this->Model->readOne('hero_slides', ['id' => $id]);
        
        if ($slide) {
            // Supprimer l'image
            if (file_exists('./attachments/heros/' . $slide['background_image'])) {
                unlink('./attachments/heros/' . $slide['background_image']);
            }
            
            $this->Model->delete('hero_slides', ['id' => $id]);
            $this->session->set_flashdata('success', 'Slide supprimé');
        }
        
        redirect('Slides');
    }

    /**
     * Changer l'ordre des slides (AJAX)
     */
    public function Reorder()
    {
        $orders = $this->input->post('order');
        
        foreach ($orders as $id => $position) {
            $this->Model->update('hero_slides', ['id' => $id], ['slide_order' => $position]);
        }
        
        echo json_encode(['success' => true]);
    }

    /**
     * Activer/Désactiver un slide (AJAX)
     */
    public function ToggleActive($id)
    {
        $slide = $this->Model->readOne('hero_slides', ['id' => $id]);
        
        if ($slide) {
            $new_status = $slide['is_active'] ? 0 : 1;
            $this->Model->update('hero_slides', ['id' => $id], ['is_active' => $new_status]);
            echo json_encode(['success' => true, 'is_active' => $new_status]);
        }
    }

    // ==================== PRIVÉ ====================

    private function uploadImage()
    {
        $config['upload_path'] = './attachments/heros/';
        $config['allowed_types'] = 'jpg|jpeg|png|webp';
        $config['max_size'] = 2048; // 2MB
        $config['encrypt_name'] = TRUE;

        // Créer le dossier si inexistant
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('background_image')) {
            return ['success' => false, 'message' => $this->upload->display_errors()];
        }

        return ['success' => true, 'file_name' => $this->upload->data('file_name')];
    }
}