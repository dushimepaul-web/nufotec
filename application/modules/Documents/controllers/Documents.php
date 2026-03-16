<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documents extends MY_Controller {

    private $upload_path = 'attachments/Documents/';

    function __construct()
    {
        parent::__construct();
        $this->load->library('email');
        $this->load->helper('download');
        // is_admin(); // Décommentez si nécessaire
    }

    // Liste tous les documents
    public function index()
    {
        // Jointure avec users et consultations
        $this->db->select('
            documents.*,
            users.nom as user_nom,
            users.prenom as user_prenom,
            consultations.numero_consultation
        ');
        $this->db->from('documents');
        $this->db->join('users', 'users.id = documents.user_id');
        $this->db->join('consultations', 'consultations.id = documents.consultation_id', 'left');
        $this->db->order_by('documents.created_at', 'DESC');
        $data['documents'] = $this->db->get()->result_array();

        // Pour les dropdowns dans le formulaire d'upload
        $data['users'] = $this->Model->read('users', 
            ['deleted_at' => NULL, 'type_utilisateur' => 'patient'], 
            'nom', 
            'ASC'
        );
        
        $data['consultations'] = $this->Model->read('consultations', null, 'date_souhaitee', 'DESC');

        $this->load->view('Documents_View', $data);
    }

    // Upload d'un nouveau document
    public function Upload()
    {
        $this->form_validation->set_rules('user_id', 'Propriétaire', 'required|numeric');
        $this->form_validation->set_rules('type', 'Type de document', 'required|in_list[consultation,analyse,prescription,autre]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Documents'));
            return;
        }

        // Gestion de l'upload de fichier
        if (empty($_FILES['document_file']['name'])) {
            $this->session->set_flashdata('error', 'Veuillez sélectionner un fichier.');
            redirect(base_url('Documents'));
            return;
        }

        $upload_result = $this->_upload_file($_FILES['document_file']);
        if ($upload_result === NULL) {
            $this->session->set_flashdata('error', 'Format de fichier non autorisé. Types acceptés: pdf, jpg, jpeg, png, gif, doc, docx, xls, xlsx, txt.');
            redirect(base_url('Documents'));
            return;
        }

        $data = [
            'user_id'          => $this->input->post('user_id'),
            'consultation_id'  => $this->input->post('consultation_id') ?: NULL,
            'filename'         => $upload_result['filename'],
            'original_name'    => $upload_result['original_name'],
            'mime_type'        => $upload_result['mime_type'],
            'type'             => $this->input->post('type'),
            'created_at'       => date('Y-m-d H:i:s')
        ];

        if ($this->Model->create('documents', $data)) {
            $this->session->set_flashdata('success', 'Document téléchargé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de l\'enregistrement en base.');
            // Supprimer le fichier uploadé si l'insertion en DB échoue
            if (file_exists(FCPATH . $this->upload_path . $data['filename'])) {
                unlink(FCPATH . $this->upload_path . $data['filename']);
            }
        }
        redirect(base_url('Documents'));
    }

    // Mise à jour des métadonnées du document
    public function Update()
    {
        $id = $this->input->post('id');
        $this->form_validation->set_rules('type', 'Type de document', 'required|in_list[consultation,analyse,prescription,autre]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Documents'));
            return;
        }

        $data = [
            'consultation_id' => $this->input->post('consultation_id') ?: NULL,
            'type'            => $this->input->post('type')
        ];

        if ($this->Model->update('documents', ['id' => $id], $data)) {
            $this->session->set_flashdata('success', 'Document mis à jour.');
        } else {
            $this->session->set_flashdata('error', 'Échec de la mise à jour.');
        }
        redirect(base_url('Documents'));
    }

    // Téléchargement d'un document
    public function Download($id)
    {
        $doc = $this->Model->readOne('documents', ['id' => $id]);
        if (!$doc) {
            show_404();
        }

        $file_path = FCPATH . $this->upload_path . $doc['filename'];
        if (!file_exists($file_path)) {
            show_404();
        }

        // Force le téléchargement
        force_download($doc['original_name'] ?: $doc['filename'], file_get_contents($file_path));
    }

    // Suppression d'un document (fichier physique + enregistrement DB)
    public function Delete()
    {
        $id = $this->input->post('id');
        $doc = $this->Model->readOne('documents', ['id' => $id]);

        if (!$doc) {
            $this->session->set_flashdata('error', 'Document introuvable.');
            redirect(base_url('Documents'));
            return;
        }

        // Suppression du fichier physique
        $file_path = FCPATH . $this->upload_path . $doc['filename'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Suppression de l'enregistrement en DB
        if ($this->Model->delete('documents', ['id' => $id])) {
            $this->session->set_flashdata('success', 'Document supprimé.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('Documents'));
    }

    /**
     * Envoie le document par email au patient
     */
    public function send_email($document_id)
    {
        $doc = $this->Model->readOne('documents', ['id' => $document_id]);
        if (!$doc) {
            $this->session->set_flashdata('error', 'Document introuvable.');
            redirect(base_url('Documents'));
            return;
        }

        // Récupérer l'utilisateur (patient) pour obtenir son email
        $user = $this->Model->readOne('users', ['id' => $doc['user_id']]);
        if (!$user || empty($user['email'])) {
            $this->session->set_flashdata('error', 'Adresse email du patient introuvable.');
            redirect(base_url('Documents'));
            return;
        }

        $file_path = FCPATH . $this->upload_path . $doc['filename'];
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'Fichier physique introuvable.');
            redirect(base_url('Documents'));
            return;
        }

        // Configuration SMTP depuis la base de données ou valeurs par défaut
        $smtp_pass = $this->Model->get_setting('smtp_password', '');
        $smtp_email = $this->Model->get_setting('smtp_email', 'noreply@nufotec.com');
        $site_name = $this->Model->get_setting('site_name', 'NUFOTEC');

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
        $this->email->from($smtp_email, $site_name);
        $this->email->to($user['email']);
        $this->email->subject('Document médical - ' . ucfirst($doc['type']));
        $this->email->message('
            <html>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #2563eb;">Document médical</h2>
                    <p>Bonjour <strong>' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . '</strong>,</p>
                    <p>Veuillez trouver ci-joint votre document médical : <strong>' . ucfirst($doc['type']) . '</strong>.</p>
                    <p>Nom du fichier : ' . htmlspecialchars($doc['original_name'] ?: $doc['filename']) . '</p>
                    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
                    <p style="color: #6b7280; font-size: 0.9em;">
                        Cordialement,<br>
                        <strong>' . htmlspecialchars($site_name) . '</strong>
                    </p>
                </div>
            </body>
            </html>
        ');
        $this->email->attach($file_path);

        if ($this->email->send()) {
            $this->session->set_flashdata('success', 'Email envoyé avec succès à ' . $user['email']);
        } else {
            log_message('error', 'Email failed: ' . $this->email->print_debugger());
            $this->session->set_flashdata('error', 'Erreur lors de l\'envoi de l\'email.');
        }

        redirect(base_url('Documents'));
    }

    // -------------------- Gestionnaire d'upload privé --------------------
    private function _upload_file($file)
    {
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            return NULL;
        }

        // Générer un nom de fichier unique
        $new_filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;

        // S'assurer que le répertoire d'upload existe
        if (!is_dir(FCPATH . $this->upload_path)) {
            mkdir(FCPATH . $this->upload_path, 0777, TRUE);
        }

        if (move_uploaded_file($file['tmp_name'], FCPATH . $this->upload_path . $new_filename)) {
            return [
                'filename'      => $new_filename,
                'original_name' => $file['name'],
                'mime_type'     => $file['type']
            ];
        }

        return NULL;
    }
}