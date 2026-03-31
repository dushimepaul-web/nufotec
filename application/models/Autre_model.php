<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre_model extends CI_Model {

    protected $table = 'galerie_medias';

    public function __construct() {
        parent::__construct();
    }

    // Récupérer tous les médias de type "autre"
    public function get_all($limit = null, $offset = 0) {
        $this->db->where('type', 'autre');
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table)->result();
    }

    // Récupérer un média par ID
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id_media' => $id, 'type' => 'autre'])->row();
    }

    // Récupérer par sous_type (image, link, document, texte, book, photo, other)
    public function get_by_sous_type($sous_type, $limit = null) {
        $this->db->where(['type' => 'autre', 'sous_type' => $sous_type]);
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get($this->table)->result();
    }

    // Insérer un nouveau média
    public function insert($data) {
        $data['type'] = 'autre';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    // Mettre à jour un média
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id_media', $id);
        return $this->db->update($this->table, $data);
    }

    // Supprimer un média
    public function delete($id) {
        // Récupérer le fichier avant suppression
        $media = $this->get_by_id($id);
        
        if ($media) {
            // Supprimer le fichier physique
            if (!empty($media->fichier) && file_exists(FCPATH . $media->fichier)) {
                unlink(FCPATH . $media->fichier);
            }
            // Supprimer la miniature
            if (!empty($media->miniature) && file_exists(FCPATH . $media->miniature)) {
                unlink(FCPATH . $media->miniature);
            }
            
            return $this->db->delete($this->table, ['id_media' => $id]);
        }
        
        return false;
    }

    // Compter le nombre total
    public function count_all() {
        $this->db->where('type', 'autre');
        return $this->db->count_all_results($this->table);
    }

    // Générer un slug unique
    public function generate_slug($titre, $id = null) {
        $slug = url_title($titre, 'dash', TRUE);
        $original_slug = $slug;
        $count = 1;
        
        while ($this->slug_exists($slug, $id)) {
            $slug = $original_slug . '-' . $count;
            $count++;
        }
        
        return $slug;
    }

    private function slug_exists($slug, $id = null) {
        $this->db->where('slug', $slug);
        if ($id) {
            $this->db->where('id_media !=', $id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Upload d'image (utilise ta fonction existante)
    public function upload_fichier($nom_file, $nom_champ) {
        $ref_folder = FCPATH . 'attachments/autre/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg', 'pdf', 'doc', 'docx', 'txt', 'mp4', 'mp3');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        $chemin_complet = $ref_folder . $fichier . "." . $file_extension;
        
        if (move_uploaded_file($nom_file, $chemin_complet)) {
            return 'attachments/autre/' . $fichier . "." . $file_extension;
        }
        
        return NULL;
    }

    // Upload miniature personnalisée
    public function upload_miniature($nom_file, $nom_champ) {
        $ref_folder = FCPATH . 'attachments/autre/thumbnails/';
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

        $chemin_complet = $ref_folder . $fichier . "." . $file_extension;
        
        if (move_uploaded_file($nom_file, $chemin_complet)) {
            return 'attachments/autre/thumbnails/' . $fichier . "." . $file_extension;
        }
        
        return NULL;
    }

    // Déterminer le sous_type selon le fichier
    public function detecter_sous_type($filename, $mime_type = null) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $images = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $documents = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'];
        
        if (in_array($ext, $images)) {
            return 'photo';
        } elseif (in_array($ext, $documents)) {
            return 'book';
        } elseif ($ext === 'txt') {
            return 'texte';
        } else {
            return 'other';
        }
    }

    // Récupérer les statistiques par sous_type
    public function get_stats() {
        $this->db->select('sous_type, COUNT(*) as total');
        $this->db->where('type', 'autre');
        $this->db->group_by('sous_type');
        return $this->db->get($this->table)->result();
    }
}