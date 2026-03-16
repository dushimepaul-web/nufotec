<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Publication extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
        // IMPORTANT : Charger le modèle
        $this->load->model('Model');
    }

    /**
     * Liste publique de tous les documents actifs
     */
    public function index()
    {
        $data['documents'] = $this->Model->readQuery("
            SELECT d.*, u.nom, u.prenom, u.email, u.type_utilisateur, u.photo as user_photo 
            FROM publication_documents d 
            JOIN users u ON d.published_by = u.id 
            WHERE d.statut = 1 
            ORDER BY d.id_document DESC
        ");
        
        $this->load->view('Publication_View', $data);
    }

    /**
     * Documents de l'utilisateur connecté
     */
    public function MyDocuments()
    {
        $user_id = $this->session->userdata('id');
        $data['documents'] = $this->Model->read('publication_documents', 
            ['published_by' => $user_id, 'statut' => 1], 
            'id_document', 'DESC'
        );
        $this->load->view('MyDocuments_View', $data);
    }

    /**
     * Changement de statut (actif/inactif) - CORRIGÉ
     */
    function ChangeStatus()
    {
        // Vérifier que c'est bien une requête POST
        if ($this->input->method() !== 'post') {
            show_error('Méthode non autorisée', 405);
        }

        $id = $this->input->post('id');
        $statut = $this->input->post('statut');
        
        // Validation des données
        if (empty($id)) {
            $this->session->set_flashdata('error', 'ID du document manquant.');
            redirect(base_url('Publication'));
            return;
        }

        $new_status = ($statut == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('publication_documents', ['id_document' => $id], ['statut' => $new_status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut du document mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Publication'));
    }

    /**
     * Création d'un document (upload classique) - CORRIGÉ
     */
    function Create()
    {
        // Vérifier que c'est bien une requête POST
        if ($this->input->method() !== 'post') {
            show_error('Méthode non autorisée', 405);
        }

        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $this->form_validation->set_rules('type_document', 'Type', 'required');
        
        $type_document = $this->input->post('type_document');
        
        if ($type_document == 'LIEN_WEB') {
            $this->form_validation->set_rules('url_source', 'URL', 'required|valid_url');
        } else {
            // Pour les uploads, le fichier est requis
            if (empty($_FILES['fichier']['name'])) {
                $this->form_validation->set_rules('fichier', 'Fichier', 'required');
            }
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Publication'));
            return;
        }

        $user_id = $this->session->userdata('idUser');
        
        // Vérifier que l'utilisateur est connecté
        if (empty($user_id)) {
            $this->session->set_flashdata('error', 'Vous devez être connecté pour ajouter un document.');
            redirect(base_url('Admin'));
            return;
        }
        
        // Upload fichier si nécessaire
        $chemin_fichier = $format_fichier = $taille_octets = null;
        
        if ($type_document != 'LIEN_WEB' && !empty($_FILES['fichier']['name'])) {
            $upload = $this->upload_document($_FILES['fichier'], $type_document);
            if (!$upload) {
                $this->session->set_flashdata('error', 'Upload échoué ou format invalide. Vérifiez l\'extension du fichier.');
                redirect(base_url('Publication'));
                return;
            }
            $chemin_fichier = $upload['chemin'];
            $format_fichier = $upload['format'];
            $taille_octets = $upload['taille'];
        }

        $data = [
            'titre' => $this->input->post('titre'),
            'type_document' => $type_document,
            'chemin_fichier' => $chemin_fichier,
            'url_source' => $type_document == 'LIEN_WEB' ? $this->input->post('url_source') : null,
            'format_fichier' => $format_fichier,
            'taille_octets' => $taille_octets,
            'description' => $this->input->post('description') ?: null,
            'tags' => $this->input->post('tags') ?: null,
            'annee_publication' => $this->input->post('annee_publication') ?: null,
            'note_personnelle' => $this->input->post('note_personnelle') ?: null,
            'published_by' => $user_id,
            'statut' => 1
        ];

        $rsp = $this->Model->create('publication_documents', $data);
        
        if ($rsp) {
            $this->session->set_flashdata('success', 'Document ajouté avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de l\'ajout du document. Vérifiez les données.');
        }
        redirect(base_url('Publication'));
    }

    /**
     * Mise à jour d'un document - CORRIGÉ
     */
    function Update()
    {
        // Vérifier que c'est bien une requête POST
        if ($this->input->method() !== 'post') {
            show_error('Méthode non autorisée', 405);
        }

        $id_document = $this->input->post('id_document');
        
        if (empty($id_document)) {
            $this->session->set_flashdata('error', 'ID du document manquant.');
            redirect(base_url('Publication'));
            return;
        }

        $document = $this->Model->readOne('publication_documents', ['id_document' => $id_document]);
        
        if (!$document) {
            $this->session->set_flashdata('error', 'Document non trouvé.');
            redirect(base_url('Publication'));
            return;
        }

        // Vérification des droits
        $user_id = $this->session->userdata('id');
        $user_role = $this->session->userdata('role');
        
        if ($document['published_by'] != $user_id && $user_role != 'admin') {
            $this->session->set_flashdata('error', 'Vous n\'avez pas les droits pour modifier ce document.');
            redirect(base_url('Publication'));
            return;
        }

        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $this->form_validation->set_rules('type_document', 'Type', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Publication'));
            return;
        }

        $type_document = $this->input->post('type_document');
        
        $data = [
            'titre' => $this->input->post('titre'),
            'type_document' => $type_document,
            'description' => $this->input->post('description') ?: null,
            'tags' => $this->input->post('tags') ?: null,
            'annee_publication' => $this->input->post('annee_publication') ?: null,
            'note_personnelle' => $this->input->post('note_personnelle') ?: null,
            'statut' => $this->input->post('statut') ? 1 : 0
        ];

        // Gestion URL pour lien web
        if ($type_document == 'LIEN_WEB') {
            $data['url_source'] = $this->input->post('url_source');
            // Supprimer ancien fichier si existant
            if (!empty($document['chemin_fichier']) && file_exists(FCPATH . $document['chemin_fichier'])) {
                unlink(FCPATH . $document['chemin_fichier']);
                $data['chemin_fichier'] = null;
                $data['format_fichier'] = null;
                $data['taille_octets'] = null;
            }
        } 
        // Upload nouveau fichier si fourni
        elseif (!empty($_FILES['fichier']['name'])) {
            // Supprimer ancien fichier
            if (!empty($document['chemin_fichier']) && file_exists(FCPATH . $document['chemin_fichier'])) {
                unlink(FCPATH . $document['chemin_fichier']);
            }
            
            $upload = $this->upload_document($_FILES['fichier'], $type_document);
            if ($upload) {
                $data['chemin_fichier'] = $upload['chemin'];
                $data['format_fichier'] = $upload['format'];
                $data['taille_octets'] = $upload['taille'];
                $data['url_source'] = null;
            } else {
                $this->session->set_flashdata('error', 'Erreur lors de l\'upload du nouveau fichier.');
                redirect(base_url('Publication'));
                return;
            }
        }

        $rsp = $this->Model->update('publication_documents', ['id_document' => $id_document], $data);
        
        if ($rsp) {
            $this->session->set_flashdata('success', 'Document mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la mise à jour du document.');
        }
        redirect(base_url('Publication'));
    }

    /**
     * Suppression logique (soft delete) - CORRIGÉ
     */
    function Delete()
    {
        // Vérifier que c'est bien une requête POST
        if ($this->input->method() !== 'post') {
            show_error('Méthode non autorisée', 405);
        }

        $id = $this->input->post('id');
        
        if (empty($id)) {
            $this->session->set_flashdata('error', 'ID du document manquant.');
            redirect(base_url('Publication'));
            return;
        }

        $document = $this->Model->readOne('publication_documents', ['id_document' => $id]);
        
        if (!$document) {
            $this->session->set_flashdata('error', 'Document non trouvé.');
            redirect(base_url('Publication'));
            return;
        }

        // Vérification droits
        $user_id = $this->session->userdata('id');
        $user_role = $this->session->userdata('role');
        
        if ($document['published_by'] != $user_id && $user_role != 'admin') {
            $this->session->set_flashdata('error', 'Vous n\'avez pas les droits pour supprimer ce document.');
            redirect(base_url('Publication'));
            return;
        }

        // Soft delete (statut = 0)
        $rsp = $this->Model->update('publication_documents', ['id_document' => $id], ['statut' => 0]);
        
        if ($rsp) {
            // Optionnel : suppression physique du fichier
            if (!empty($document['chemin_fichier']) && file_exists(FCPATH . $document['chemin_fichier'])) {
                @unlink(FCPATH . $document['chemin_fichier']);
            }
            $this->session->set_flashdata('success', 'Document supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression du document.');
        }
        redirect(base_url('Publication'));
    }

    /**
     * Upload de fichier avec validation - CORRIGÉ
     */
    private function upload_document($file, $type_document)
    {
        // Vérifier que le fichier est valide
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return null;
        }

        $base_path = FCPATH . 'attachments/Publication/';
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Extensions par type
        $allowed = [
            'VIDEO' => ['mp4', 'avi', 'mov', 'mkv', 'webm', 'flv'],
            'AUDIO' => ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'webm'],
            'PHOTO' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'],
            'PDF' => ['pdf'],
            'EBOOK' => ['pdf', 'epub', 'mobi', 'azw3'],
            'DOCUMENT_TEXTE' => ['doc', 'docx', 'txt', 'rtf', 'odt'],
            'TEXTE' => ['txt', 'md', 'rtf'],
            'ARCHIVE' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'AUTRE' => ['*']
        ];

        $valid_exts = $allowed[$type_document] ?? ['*'];
        
        // Vérification de l'extension
        if ($valid_exts[0] !== '*' && !in_array($ext, $valid_exts)) {
            log_message('error', 'Extension non autorisée: ' . $ext . ' pour type: ' . $type_document);
            return null;
        }

        // Créer le dossier principal s'il n'existe pas
        if (!is_dir($base_path)) {
            if (!mkdir($base_path, 0777, true)) {
                log_message('error', 'Impossible de créer le dossier: ' . $base_path);
                return null;
            }
        }

        // Créer sous-dossier par type
        $type_folder = $base_path . strtolower($type_document) . '/';
        if (!is_dir($type_folder)) {
            if (!mkdir($type_folder, 0777, true)) {
                log_message('error', 'Impossible de créer le dossier: ' . $type_folder);
                return null;
            }
        }

        $filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $filepath = $type_folder . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'chemin' => 'attachments/Publication/' . strtolower($type_document) . '/' . $filename,
                'format' => $ext,
                'taille' => $file['size']
            ];
        }

        log_message('error', 'Échec du move_uploaded_file pour: ' . $file['name']);
        return null;
    }

    /**
     * Téléchargement sécurisé - CORRIGÉ
     */
    function Download($id_document)
    {
        // Vérifier que l'ID est valide
        if (empty($id_document) || !is_numeric($id_document)) {
            show_404();
        }

        $document = $this->Model->readOne('publication_documents', ['id_document' => $id_document]);
        
        if (!$document || empty($document['chemin_fichier'])) {
            show_404();
        }

        $file_path = FCPATH . $document['chemin_fichier'];
        
        if (!file_exists($file_path)) {
            show_404();
        }

        // Générer un nom de fichier sûr
        $safe_name = preg_replace('/[^a-zA-Z0-9-_\.]+/', '-', strtolower($document['titre']));
        $safe_name = trim($safe_name, '-');
        if (empty($safe_name)) {
            $safe_name = 'document';
        }
        $ext = $document['format_fichier'] ?: 'bin';

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $safe_name . '.' . $ext . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        // Nettoyer le tampon de sortie
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        readfile($file_path);
        exit;
    }
}