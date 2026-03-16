<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prescriptions extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        // is_admin(); // Décommentez si nécessaire
    }

    // Liste toutes les prescriptions
    public function index()
    {
        $this->db->select('
            prescriptions.*,
            consultations.patient_id,
            consultations.medecin_id,
            consultations.numero_consultation,
            patient.nom as patient_nom,
            patient.prenom as patient_prenom,
            medecin.nom as medecin_nom,
            medecin.prenom as medecin_prenom
        ');
        $this->db->from('prescriptions');
        $this->db->join('consultations', 'consultations.id = prescriptions.consultation_id');
        $this->db->join('users as patient', 'patient.id = consultations.patient_id');
        $this->db->join('users as medecin', 'medecin.id = consultations.medecin_id');
        $this->db->order_by('prescriptions.date_prescription', 'DESC');
        $data['prescriptions'] = $this->db->get()->result_array();

        // Consultations terminées pour le formulaire de création
        $data['consultations'] = $this->Model->read('consultations', ['statut' => 'terminee'], 'date_souhaitee', 'DESC');

        $this->load->view('Prescriptions_View', $data);
    }

    // Change le statut (actif/inactif)
    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        $new_status = ($is_active == 1) ? 0 : 1;

        if ($this->Model->update('prescriptions', ['id' => $id], ['is_active' => $new_status])) {
            $this->session->set_flashdata('success', 'Statut mis à jour.');
        } else {
            $this->session->set_flashdata('error', 'Échec de la mise à jour.');
        }
        redirect(base_url('Prescriptions'));
    }

    // Détail d'une prescription (vue dédiée)
    public function Detail($id)
    {
        $data['detail'] = $this->db->select('
                prescriptions.*,
                consultations.numero_consultation,
                patient.nom as patient_nom,
                patient.prenom as patient_prenom,
                medecin.nom as medecin_nom,
                medecin.prenom as medecin_prenom
            ')
            ->from('prescriptions')
            ->join('consultations', 'consultations.id = prescriptions.consultation_id')
            ->join('users as patient', 'patient.id = consultations.patient_id')
            ->join('users as medecin', 'medecin.id = consultations.medecin_id')
            ->where('prescriptions.id', $id)
            ->get()
            ->row_array();

        $this->load->view('PrescriptionDetail_View', $data);
    }

    // Création d'une prescription
    public function Create()
    {
        $this->form_validation->set_rules('consultation_id', 'Consultation', 'required|numeric');
        $this->form_validation->set_rules('medicament', 'Médicament', 'required|max_length[255]');
        $this->form_validation->set_rules('dosage', 'Dosage', 'max_length[100]');
        $this->form_validation->set_rules('instructions', 'Instructions', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Prescriptions'));
            return;
        }

        $data = [
            'consultation_id'   => $this->input->post('consultation_id'),
            'medicament'        => $this->input->post('medicament'),
            'dosage'            => $this->input->post('dosage') ?: null,
            'instructions'      => $this->input->post('instructions') ?: null,
            'date_prescription' => date('Y-m-d H:i:s'),
            'is_active'         => 1,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        if ($this->Model->create('prescriptions', $data)) {
            $this->session->set_flashdata('success', 'Prescription ajoutée.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de l\'ajout.');
        }
        redirect(base_url('Prescriptions'));
    }

    // Mise à jour d'une prescription
    public function Update()
    {
        $id = $this->input->post('id');
        $this->form_validation->set_rules('medicament', 'Médicament', 'required|max_length[255]');
        $this->form_validation->set_rules('dosage', 'Dosage', 'max_length[100]');
        $this->form_validation->set_rules('instructions', 'Instructions', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Prescriptions'));
            return;
        }

        $data = [
            'medicament'   => $this->input->post('medicament'),
            'dosage'       => $this->input->post('dosage') ?: null,
            'instructions' => $this->input->post('instructions') ?: null,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($this->Model->update('prescriptions', ['id' => $id], $data)) {
            $this->session->set_flashdata('success', 'Prescription mise à jour.');
        } else {
            $this->session->set_flashdata('error', 'Échec de la mise à jour.');
        }
        redirect(base_url('Prescriptions'));
    }

    // Suppression définitive
    public function Delete()
    {
        $id = $this->input->post('id');
        if ($this->Model->delete('prescriptions', ['id' => $id])) {
            $this->session->set_flashdata('success', 'Prescription supprimée.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('Prescriptions'));
    }

    // ==================== GESTION DES DOCUMENTS ====================

    /**
     * AJAX : Liste des documents liés à une prescription
     */
    public function documents_list($prescription_id)
    {
        $prescription = $this->Model->readOne('prescriptions', ['id' => $prescription_id]);
        if (!$prescription) {
            $this->output->set_content_type('application/json')->set_output(json_encode([]));
            return;
        }

        $this->db->select('id, original_name, filename, mime_type, type, created_at');
        $this->db->from('documents');
        $this->db->where('consultation_id', $prescription['consultation_id']);
        $this->db->order_by('created_at', 'DESC');
        $docs = $this->db->get()->result_array();

        $this->output->set_content_type('application/json')->set_output(json_encode($docs));
    }

    /**
     * Upload d'un document pour une prescription
     */
    public function upload_document()
    {
        $prescription_id = $this->input->post('prescription_id');
        $type = $this->input->post('type'); // 'prescription', 'analyse', etc.

        $prescription = $this->Model->readOne('prescriptions', ['id' => $prescription_id]);
        if (!$prescription) {
            $this->session->set_flashdata('error', 'Prescription introuvable.');
            redirect(base_url('Prescriptions'));
            return;
        }

        $consultation = $this->Model->readOne('consultations', ['id' => $prescription['consultation_id']]);
        if (!$consultation) {
            $this->session->set_flashdata('error', 'Consultation associée introuvable.');
            redirect(base_url('Prescriptions'));
            return;
        }

        if (empty($_FILES['document_file']['name'])) {
            $this->session->set_flashdata('error', 'Veuillez sélectionner un fichier.');
            redirect(base_url('Prescriptions'));
            return;
        }

        $upload_result = $this->_upload_file($_FILES['document_file']);
        if ($upload_result === NULL) {
            $this->session->set_flashdata('error', 'Format de fichier non autorisé.');
            redirect(base_url('Prescriptions'));
            return;
        }

        $data = [
            'user_id'          => $consultation['patient_id'],
            'consultation_id'  => $consultation['id'],
            'filename'         => $upload_result['filename'],
            'original_name'    => $upload_result['original_name'],
            'mime_type'        => $upload_result['mime_type'],
            'type'             => $type ?: 'prescription',
            'created_at'       => date('Y-m-d H:i:s')
        ];

        if ($this->Model->create('documents', $data)) {
            $this->session->set_flashdata('success', 'Document ajouté à la prescription.');
        } else {
            if (file_exists(FCPATH . 'attachments/Documents/' . $data['filename'])) {
                unlink(FCPATH . 'attachments/Documents/' . $data['filename']);
            }
            $this->session->set_flashdata('error', 'Erreur lors de l\'enregistrement en base.');
        }

        redirect(base_url('Prescriptions'));
    }

    /**
     * Téléchargement / Affichage d'un document
     */
    public function download($document_id)
    {
        $doc = $this->Model->readOne('documents', ['id' => $document_id]);
        if (!$doc) {
            show_404();
        }

        $file_path = FCPATH . 'attachments/Documents/' . $doc['filename'];
        if (!file_exists($file_path)) {
            show_404();
        }

        // Types à afficher dans le navigateur (inline)
        $inline_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $disposition = in_array($doc['mime_type'], $inline_types) ? 'inline' : 'attachment';

        // Nettoyer le nom du fichier
        $filename = $doc['original_name'] ?: $doc['filename'];
        $filename = str_replace(['"', "\n", "\r"], '', $filename);

        // Envoyer les en-têtes
        header('Content-Type: ' . $doc['mime_type']);
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($file_path);
        exit;
    }

    // -------------------- Upload interne --------------------
    private function _upload_file($file)
    {
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            return NULL;
        }

        $upload_path = FCPATH . 'attachments/Documents/';
        $new_filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, TRUE);
        }

        if (move_uploaded_file($file['tmp_name'], $upload_path . $new_filename)) {
            return [
                'filename'      => $new_filename,
                'original_name' => $file['name'],
                'mime_type'     => $file['type']
            ];
        }
        return NULL;
    }
}