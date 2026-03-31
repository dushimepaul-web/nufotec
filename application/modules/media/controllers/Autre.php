<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre extends MX_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('media/Model_media', 'Model');
        
        // Désactiver CSRF pour les requêtes AJAX
        if ($this->input->is_ajax_request()) {
            $this->config->set_item('csrf_protection', FALSE);
        }
    }

    // ==================== INDEX ====================
    public function index()
    {
        $items = $this->db->query("
            SELECT * FROM galerie_medias 
            WHERE type IN ('autre', 'link', 'image') 
            ORDER BY id_media DESC
        ")->result_array();
        
        foreach ($items as &$item) {
            $item['taille_formatee'] = !empty($item['taille']) ? $this->formatBytes($item['taille']) : '-';
        }

        $data = [
            'items'      => $items,
            'categories' => $this->getCategories()
        ];
        
        $this->load->view('Autre_View', $data);
    }

    // ==================== UPLOAD IMAGE (MINIATURE) ====================
    public function upload_image()
    {
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['thumbnail_file'])) {
            echo json_encode(['success' => false, 'message' => 'Aucun fichier']);
            return;
        }
        
        $file = $_FILES['thumbnail_file'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');
        
        if (!in_array($file_extension, $valid_ext)) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté']);
            return;
        }
        
        $ref_folder = FCPATH . 'attachments/Autre/Thumbnails/';
        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }
        
        $code = date("YmdHis") . uniqid();
        $filename = $code . "." . $file_extension;
        
        if (move_uploaded_file($file['tmp_name'], $ref_folder . $filename)) {
            echo json_encode([
                'success' => true,
                'file_path' => 'attachments/Autre/Thumbnails/' . $filename,
                'preview_url' => base_url('attachments/Autre/Thumbnails/' . $filename)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur upload']);
        }
    }

    // ==================== CRUD ====================
    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim');
        $sous_type = $this->input->post('sous_type');
        
        if ($sous_type == 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = [
            'titre'       => $this->input->post('titre'),
            'type'        => ($sous_type == 'link') ? 'link' : (($sous_type == 'photo') ? 'image' : 'autre'),
            'sous_type'   => $sous_type,
            'description' => $this->input->post('description'),
            'categorie'   => $this->input->post('categorie'),
            'credits'     => $this->input->post('credits'),
            'est_actif'   => $this->input->post('est_actif') ? 1 : 1,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website'  => $this->input->post('is_for_website') ? 1 : 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        // Gestion selon le type
        if ($sous_type == 'link') {
            $data['lien'] = $this->input->post('lien');
            $data['miniature'] = $this->extractLinkThumb($data['lien']);
        } elseif ($sous_type == 'texte') {
            $data['contenu_texte'] = $this->input->post('contenu_texte');
            $data['miniature'] = 'assets/images/text-default.png';
        } else {
            // photo, book, other
            $data['fichier'] = $this->input->post('uploaded_file_path');
            $thumbnail = $this->input->post('thumbnail');
            if (!empty($thumbnail)) {
                $data['miniature'] = $thumbnail;
            } elseif ($sous_type == 'photo' && !empty($data['fichier'])) {
                $data['miniature'] = $data['fichier'];
            }
        }

        // Générer le slug
        $data['slug'] = $this->generateSlug($data['titre']);

        $rsp = $this->Model->create('galerie_medias', $data);
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Créé avec succès' : 'Erreur création');
        redirect(base_url('autre'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = [
            'titre'       => $this->input->post('titre'),
            'description' => $this->input->post('description'),
            'categorie'   => $this->input->post('categorie'),
            'credits'     => $this->input->post('credits'),
            'est_actif'   => $this->input->post('est_actif') ? 1 : 0,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website'  => $this->input->post('is_for_website') ? 1 : 0,
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        $thumbnail = $this->input->post('thumbnail');
        if (!empty($thumbnail)) {
            $data['miniature'] = $thumbnail;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Mis à jour' : 'Erreur mise à jour');
        redirect(base_url('autre'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => 0]);
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Supprimé' : 'Erreur suppression');
        redirect(base_url('autre'));
    }

    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('est_actif');
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => $status]);
        echo json_encode(['success' => (bool)$rsp]);
    }

    public function toggleField()
    {
        $id = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        $allowed = ['is_for_whatsapp', 'is_for_website', 'est_actif'];
        if (!in_array($field, $allowed)) {
            echo json_encode(['success' => false]);
            return;
        }
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [$field => $value]);
        echo json_encode(['success' => (bool)$rsp]);
    }

    // ==================== HELPERS ====================
    private function generateSlug($title)
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Vérifier l'unicité
        $original = $slug;
        $counter = 1;
        while ($this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ?", [$slug])->num_rows() > 0) {
            $slug = $original . '-' . $counter;
            $counter++;
        }
        return $slug;
    }

    private function extractLinkThumb($url)
    {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return "https://vumbnail.com/{$m[1]}.jpg";
        }
        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain) {
            return "https://www.google.com/s2/favicons?domain={$domain}&sz=128";
        }
        return 'assets/images/link-default.png';
    }

    private function getCategories()
    {
        $result = $this->db->query("SELECT DISTINCT categorie FROM galerie_medias WHERE categorie IS NOT NULL AND categorie != ''")->result_array();
        return array_column($result, 'categorie');
    }

    private function formatBytes($bytes)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}