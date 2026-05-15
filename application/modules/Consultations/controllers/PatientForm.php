<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller PatientForm
 * Handles the online consultation form with custom file upload.
 */
class PatientForm extends Public_Controller {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->library([
            'form_validation', 
            'session',
            'email'
        ]);
        
        $this->load->helper([
            'url', 
            'form', 
            'security',
            'string',
            'file'
        ]);
        
        $this->upload_path = FCPATH . 'attachments/FichierPatient/';
        
        if (!is_dir($this->upload_path)) {
            mkdir($this->upload_path, 0755, TRUE);
        }
    }
    
    public function index()
    {   
        // Check if user is logged in
        if (!$this->session->userdata('user_id')) {
            redirect('Auth');
        }

        // ============================================
        // VÉRIFIER S'IL Y A UNE CONSULTATION EN ATTENTE DE PAIEMENT
        // ============================================
        $patient_id = $this->session->userdata('user_id');
        
        $pending_consultation = $this->Model->getPendingConsultationByPatient($patient_id);
        
        if ($pending_consultation) {
            $this->session->set_flashdata('warning', 'Vous avez une consultation en attente de paiement. Veuillez finaliser votre paiement.');
            redirect('Consultations/Payment/index/' . $pending_consultation['numero_consultation']);
            return;
        }

        // Récupérer l'UUID du médecin depuis GET ou POST
        $doctor_uuid = $this->input->get('doctor_uuid') ?: $this->input->post('selected_doctor_uuid');
        $medecin = null;

        if ($doctor_uuid) {
            $medecin = $this->Model->getDoctorByUUID($doctor_uuid);
            if (!$medecin) {
                $this->session->set_flashdata('error', 'Doctor not found or unavailable.');
                redirect('Medicins');
            }
        } else {
            $doctor_data = $this->session->userdata('pending_doctor');
            if (!$doctor_data || $doctor_data['expires_at'] < time()) {
                $this->session->unset_userdata('pending_doctor');
                $this->session->set_flashdata('error', 'Please select a doctor.');
                redirect('Medicins');
            }
            $medecin = $this->Model->getDoctorByUUID($doctor_data['uuid']);
            if (!$medecin) {
                $this->session->set_flashdata('error', 'Doctor not found or unavailable.');
                redirect('Dashboard/patient_dashboard');
            }
        }

        $taux = $this->config->item('taux_devise');
        $prix_usd = isset($medecin['honoraires_consultation']) ? (float)$medecin['honoraires_consultation'] : 50;
        $prix_eur = $prix_usd * ($taux['USD_TO_EUR'] ?? 0.92);
        $prix_bif = $prix_usd * ($taux['USD_TO_BIF'] ?? 2900);
        $devise = $medecin['currency'] ?? 'USD';

        $data = [
            'title'          => 'Nouvelle consultation - NUFOTEC',
            'pays'           => $this->Model->read('pays', null, 'pays', 'ASC'),
            'products'       => $this->Model->read('advertise_product', null, 'id', 'DESC'),
            'mode_payements' => $this->Model->read('mode_payement', null, 'id_mode_payement'),
            'is_logged_in'   => TRUE,
            'user_id'        => $this->session->userdata('user_id'),
            'medecin'        => $medecin,
            'prix_usd'       => $prix_usd,
            'prix_eur'       => $prix_eur,
            'prix_bif'       => $prix_bif,
            'devise'         => $devise,
            'taux'           => $taux
        ];

        $this->load->view('PatientForm_View', $data);
    }

    public function changeDoctor()
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }
        $this->clearPatientData();
        redirect('Medicins');
    }

    private function clearPatientData()
    {
        $session_data = [
            'pending_doctor',
            'consultation_form_data',
            'temp_files',
            'uploaded_files'
        ];
        foreach ($session_data as $key) {
            $this->session->unset_userdata($key);
        }
        $this->cleanTempFiles();
        log_message('info', 'Patient data cleared due to doctor change');
    }

    private function cleanTempFiles()
    {
        $temp_files = $this->session->userdata('temp_files');
        if (!empty($temp_files) && is_array($temp_files)) {
            foreach ($temp_files as $file) {
                $file_path = $this->upload_path . $file;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
        }
        $this->session->unset_userdata('temp_files');
    }

    public function create()
    {    
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('patient-form');
        }

        $patient_id = $this->session->userdata('user_id');
        if (!$patient_id) {
            $this->session->set_flashdata('error', 'Veuillez vous connecter pour soumettre une consultation.');
            redirect('Auth');
        }

        $this->form_validation->set_rules('full_name', 'Nom complet', 'required|trim|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('age', 'Âge', 'required|integer|greater_than[0]|less_than[121]');
        $this->form_validation->set_rules('country', 'Pays de résidence', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('weight', 'Poids', 'required|numeric|greater_than[0]|less_than[300]');
        $this->form_validation->set_rules('height', 'Taille', 'required|integer|greater_than[50]|less_than[251]');
        $this->form_validation->set_rules('symptoms', 'Symptômes', 'required|trim|min_length[20]|max_length[5000]');
        $this->form_validation->set_rules('symptoms_duration', 'Durée des symptômes', 'trim');
        $this->form_validation->set_rules('previous_consultation', 'Consultation précédente', 'trim|in_list[yes,no]');
        $this->form_validation->set_rules('terms', 'Conditions générales', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors('<div>', '</div>'));
            redirect('patient-form');
        }

        $doctor_id = $this->input->post('doctor_id', TRUE);
        $doctor_uuid = $this->input->post('doctor_uuid', TRUE);
        $consultation_prix = $this->input->post('consultation_prix', TRUE) ?: 50;
        $consultation_devise = $this->input->post('consultation_devise', TRUE) ?: 'USD';

        $medecin = null;
        if ($doctor_uuid) {
            $medecin = $this->Model->getDoctorByUUID($doctor_uuid);
            if (!$medecin) {
                $this->session->set_flashdata('error', 'Le médecin sélectionné n\'est plus disponible.');
                redirect('Medicins');
            }
        }

        $country_id = null;
        $country_name = $this->input->post('country', TRUE);
        if (!empty($country_name)) {
            $pays = $this->Model->getPaysByName($country_name);
            if ($pays) {
                $country_id = $pays['id'];
            }
        }

        $age = $this->input->post('age', TRUE);
        $numero_consultation = $this->_generate_consultation_number();

        $medical_docs = [];
        if (!empty($_FILES['medical_docs']['name'][0])) {
            $medical_docs = $this->_upload_multiple_files_custom('medical_docs');
        }
        $prescriptions = [];
        if (!empty($_FILES['prescriptions']['name'][0])) {
            $prescriptions = $this->_upload_multiple_files_custom('prescriptions');
        }

        $consultation_data = [
            'numero_consultation' => $numero_consultation,
            'patient_id'          => $patient_id,
            'medecin_id'          => $doctor_id ?: NULL,
            'type'                => 'video',
            'poids'               => $this->input->post('weight', TRUE),
            'taille'              => $this->input->post('height', TRUE),
            'country_id'          => $country_id,
            'age'                 => $age,
            'symptomes'           => $this->input->post('symptoms', TRUE),
            'duree_symptomes'     => $this->input->post('symptoms_duration', TRUE),
            'consultation_precedente' => $this->input->post('previous_consultation', TRUE),
            'examens_demandes'    => !empty($medical_docs) ? json_encode($medical_docs) : NULL,
            'ordonnances'         => !empty($prescriptions) ? json_encode($prescriptions) : NULL,
            'diagnostic'          => NULL,
            'traitement'          => NULL,
            'notes_medecin'       => NULL,
            'date_souhaitee'      => date('Y-m-d H:i:s', strtotime('+7 days')),
            'date_confirmee'      => NULL,
            'date_debut'          => NULL,
            'date_fin'            => NULL,
            'duree_minutes'       => 30,
            'room_id'             => NULL,
            'room_url'            => NULL,
            'statut'              => 'en_attente',
            'honoraires_consultation' => 0.00,
            'motif_annulation'    => NULL,
            'prix_ht'             => $consultation_prix,
            'devise'              => $consultation_devise,
            'tva'                 => 0.00,
            'paiement_statut'     => 'en_attente',
            'mode_paiement'       => NULL,
            'preuve_paiement'     => NULL,
            'ip_creation'         => $this->input->ip_address(),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s')
        ];

        $insert_id = $this->Model->create('consultations', $consultation_data);

        if (!$insert_id) {
            $all_files = array_merge($medical_docs, $prescriptions);
            $this->_cleanup_files($all_files);
            $this->session->set_flashdata('error', 'Erreur lors de l\'enregistrement de la consultation.');
            redirect('patient-form');
        }

        $this->session->unset_userdata('pending_doctor');

        $email_data = [
            'consultation_id'     => $insert_id,
            'numero_consultation' => $numero_consultation,
            'patient_id'          => $patient_id,
            'doctor_id'           => $doctor_id,
            'full_name'           => $this->input->post('full_name', TRUE),
            'age'                 => $age,
            'country'             => $country_name,
            'weight'              => $this->input->post('weight', TRUE),
            'height'              => $this->input->post('height', TRUE),
            'symptoms'            => $this->input->post('symptoms', TRUE),
            'symptoms_duration'   => $this->input->post('symptoms_duration', TRUE),
            'previous_consultation' => $this->input->post('previous_consultation', TRUE),
            'consultation_prix'   => $consultation_prix,
            'consultation_devise' => $consultation_devise,
            'medecin'             => $medecin,
            'medical_docs'        => $medical_docs,
            'prescriptions'       => $prescriptions
        ];
        
        $this->_send_consultation_emails($email_data);

        $this->session->set_flashdata('success', 'Votre demande de consultation a été créée avec succès.');
        $this->session->set_flashdata('tracking_number', $numero_consultation);
        
        redirect('Consultations/Payment/index/' . $numero_consultation);
    }

    private function _upload_multiple_files_custom($field_name)
    {
        $uploaded_files = [];
        if (empty($_FILES[$field_name]['name'][0])) {
            return $uploaded_files;
        }

        $file_count = count($_FILES[$field_name]['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES[$field_name]['error'][$i] !== UPLOAD_ERR_OK) {
                log_message('error', 'File upload error ' . $i . ': ' . $_FILES[$field_name]['error'][$i]);
                continue;
            }
            if ($_FILES[$field_name]['size'][$i] > 5 * 1024 * 1024) {
                log_message('error', 'File too large: ' . $_FILES[$field_name]['name'][$i]);
                continue;
            }
            $result = $this->upload_image(
                $_FILES[$field_name]['tmp_name'][$i],
                $_FILES[$field_name]['name'][$i]
            );
            if ($result !== NULL) {
                $uploaded_files[] = $result;
            }
        }
        return $uploaded_files;
    }

    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Consultations/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = strtolower(pathinfo($nom_champ, PATHINFO_EXTENSION));
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg', 'pdf', 'doc', 'docx');

        if (!in_array($file_extension, $valid_ext)) {
            log_message('error', 'Invalid extension: ' . $file_extension);
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        $destination = $ref_folder . $fichier . "." . $file_extension;
        if (move_uploaded_file($nom_file, $destination)) {
            return $fichier . "." . $file_extension;
        } else {
            log_message('error', 'Failed to move file: ' . $nom_champ);
            return NULL;
        }
    }

    private function _cleanup_files($files)
    {
        foreach ($files as $file) {
            if (!empty($file)) {
                $path = $this->upload_path . $file;
                if (file_exists($path)) {
                    unlink($path);
                    log_message('debug', 'File deleted: ' . $path);
                }
            }
        }
    }

    private function _generate_consultation_number()
    {
        return 'NUF-' . date('Ymd') . '-' . date('His');
    }

    public function get_countries()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Unauthorized access', 403);
        }

        $search = $this->input->get('q', TRUE);
        if (strlen($search) < 2) {
            echo json_encode([]);
            return;
        }

        $this->db->like('pays', $search, 'both');
        $this->db->order_by('pays', 'ASC');
        $this->db->limit(10);
        $query = $this->db->get('pays');

        $results = [];
        foreach ($query->result_array() as $row) {
            $results[] = [
                'id'            => $row['id'],
                'pays'          => $row['pays'],
                'code_iso2'     => $row['ISO_3166_1_2_Letter_Code'],
                'phone_code'    => $row['ITU_T_Telephone_Code'],
                'currency_code' => $row['ISO_4217_Currency_Code']
            ];
        }
        echo json_encode($results);
    }

    public function Medicin()
    {    
        // Requête sans multilingue
        $this->db->select("
            medecins.id,
            medecins.uuid,
            medecins.user_id,
            medecins.numero_licence,
            medecins.annees_experience,
            medecins.honoraires_consultation,
            medecins.currency,
            medecins.est_disponible,
            medecins.note_moyenne,
            medecins.nombre_avis,
            medecins.created_at,
            medecins.updated_at,
            medecins.specialite_fr AS specialite,
            medecins.diplomes_fr AS diplomes,
            medecins.langues_parlees_fr AS langues_parlees,
            users.nom,
            users.prenom,
            users.email,
            users.telephone,
            users.photo,
            users.is_active,
            users.est_verifie
        ");
        $this->db->from('medecins');
        $this->db->join('users', 'users.id = medecins.user_id');
        $this->db->where('users.is_active', 1);
        $this->db->order_by('medecins.id', 'DESC');
        $data['medecins'] = $this->db->get()->result_array();

        foreach ($data['medecins'] as &$medecin) {
            $this->db->from('medecin_horaires');
            $this->db->where('medecin_id', $medecin['id']);
            $this->db->where('est_actif', 1);
            $this->db->order_by("FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')");
            $query = $this->db->get();
            $medecin['horaires'] = $query->result_array();
            $medecin['est_disponible_maintenant'] = $this->checkDisponibiliteMaintenant($medecin['id']);
        }

        $data['jours_semaine'] = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        $data['products'] = $this->Model->read('advertise_product', null, 'id', 'DESC');

        $this->load->view('Medecin_present_View', $data);
    }

    private function checkDisponibiliteMaintenant($medecin_id) {
        $jour_actuel = strtolower(date('l'));
        $jours_map = [
            'monday' => 'lundi',
            'tuesday' => 'mardi',
            'wednesday' => 'mercredi',
            'thursday' => 'jeudi',
            'friday' => 'vendredi',
            'saturday' => 'samedi',
            'sunday' => 'dimanche'
        ];
        $jour_semaine = $jours_map[$jour_actuel] ?? '';
        $heure_actuelle = date('H:i:s');
        
        $this->db->where('medecin_id', $medecin_id);
        $this->db->where('jour_semaine', $jour_semaine);
        $this->db->where('heure_debut <=', $heure_actuelle);
        $this->db->where('heure_fin >=', $heure_actuelle);
        $this->db->where('est_actif', 1);
        
        return $this->db->get('medecin_horaires')->num_rows() > 0;
    }

    private function _send_consultation_emails($data)
    {    
        try {
            $this->load->library('Sendgrid_lib');

            $this->db->select('email, nom, prenom');
            $this->db->where('id', $data['patient_id']);
            $this->db->from('users');
            $patient_query = $this->db->get();
            $patient = $patient_query->row_array();

            $doctor = null;
            if (!empty($data['doctor_id'])) {
                $this->db->select('medecins.*, users.email, users.nom, users.prenom');
                $this->db->where('medecins.id', $data['doctor_id']);
                $this->db->from('medecins');
                $this->db->join('users', 'users.id = medecins.user_id');
                $doctor_query = $this->db->get();
                $doctor = $doctor_query->row_array();
            }

            $site_name = $this->Model->get_setting('site_name', 'NUFOTEC');

            if ($patient && !empty($patient['email'])) {
                $subject = 'Your consultation request has been received - ' . $data['numero_consultation'];
                $message = $this->_build_patient_email($data, $patient, $doctor, $site_name);
                $result = $this->sendgrid_lib->send_email($patient['email'], $subject, $message);
                if (!($result['status'] == 202 || $result['status'] == 200)) {
                    log_message('error', 'SendGrid - Email to patient failed: ' . json_encode($result));
                }
            }

            if ($doctor && !empty($doctor['email'])) {
                $subject = 'New consultation request - ' . $data['numero_consultation'];
                $message = $this->_build_doctor_email($data, $patient, $doctor, $site_name);
                $result = $this->sendgrid_lib->send_email($doctor['email'], $subject, $message);
                if (!($result['status'] == 202 || $result['status'] == 200)) {
                    log_message('error', 'SendGrid - Email to doctor failed: ' . json_encode($result));
                }
            }

            $admin_email = $this->Model->get_setting('admin_email', 'admin@nufotec.com');
            if (!empty($admin_email)) {
                $subject = 'New consultation created - ' . $data['numero_consultation'];
                $message = $this->_build_admin_email($data, $patient, $doctor, $site_name);
                $result = $this->sendgrid_lib->send_email($admin_email, $subject, $message);
                if (!($result['status'] == 202 || $result['status'] == 200)) {
                    log_message('error', 'SendGrid - Email to admin failed: ' . json_encode($result));
                }
            }
            return true;
        } catch (Exception $e) {
            log_message('error', 'Exception in consultation email sending: ' . $e->getMessage());
            return false;
        }
    }

    private function _build_patient_email($data, $patient, $doctor, $site_name) {
        $doctor_name = $doctor ? htmlspecialchars($doctor['prenom']) . ' ' . htmlspecialchars($doctor['nom']) : 'Not assigned yet';
        $appointment_date = date('Y-m-d H:i', strtotime('+1 day'));
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; margin: 0;">
            <div style="max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div style="background: #0d6efd; color: white; padding: 30px; text-align: center;">
                    <h1 style="margin:0; font-size: 24px;">Consultation Request Received</h1>
                </div>
                <div style="padding: 30px;">
                    <p style="font-size: 16px; color: #333;">Dear <?= htmlspecialchars($data['full_name']) ?>,</p>
                    <p>Thank you for submitting your consultation request. We have received your information and payment.</p>
                    <div style="background: #e7f3ff; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0d6efd;">
                        <h3 style="margin-top: 0; color: #0d6efd;">Consultation Details</h3>
                        <p style="margin: 5px 0;"><strong>Tracking Number:</strong> <?= $data['numero_consultation'] ?></p>
                        <p style="margin: 5px 0;"><strong>Doctor:</strong> <?= $doctor_name ?></p>
                        <p style="margin: 5px 0;"><strong>Requested Date:</strong> <?= $appointment_date ?></p>
                        <p style="margin: 5px 0;"><strong>Amount Paid:</strong> <?= $data['consultation_prix'] ?> <?= $data['consultation_devise'] ?></p>
                        <p style="margin: 5px 0;"><strong>Status:</strong> <span style="color: #ffc107;">Pending Confirmation</span></p>
                    </div>
                    <p><strong>What happens next?</strong></p>
                    <ul style="line-height: 1.8;">
                        <li>The doctor will review your request within 24 hours</li>
                        <li>You will receive a confirmation email with the exact appointment time</li>
                        <li>A video consultation link will be sent to you before the appointment</li>
                        <li>Please prepare your medical documents for the consultation</li>
                    </ul>
                    <p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                        <strong>Important:</strong> Keep your tracking number <strong><?= $data['numero_consultation'] ?></strong> for future reference.
                    </p>
                    <p>If you have any questions, please contact our support team.</p>
                    <p>Best regards,<br><strong>The <?= htmlspecialchars($site_name) ?> Team</strong></p>
                </div>
                <div style="background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d;">
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function _build_doctor_email($data, $patient, $doctor, $site_name) {
        $patient_email = $patient ? $patient['email'] : 'N/A';
        $attachments_list = '';
        if (!empty($data['medical_docs'])) {
            $attachments_list .= '<li>Medical documents: ' . count($data['medical_docs']) . ' file(s)</li>';
        }
        if (!empty($data['prescriptions'])) {
            $attachments_list .= '<li>Previous prescriptions: ' . count($data['prescriptions']) . ' file(s)</li>';
        }
        if (empty($attachments_list)) {
            $attachments_list = '<li>No attachments</li>';
        }
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; margin: 0;">
            <div style="max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div style="background: #198754; color: white; padding: 30px; text-align: center;">
                    <h1 style="margin:0; font-size: 24px;">New Consultation Request</h1>
                </div>
                <div style="padding: 30px;">
                    <p style="font-size: 16px; color: #333;">Dear <?= htmlspecialchars($doctor['prenom']) ?> <?= htmlspecialchars($doctor['nom']) ?>,</p>
                    <p>You have received a new consultation request that requires your attention.</p>
                    <div style="background: #d1e7dd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #198754;">
                        <h3 style="margin-top: 0; color: #198754;">Patient Information</h3>
                        <p style="margin: 5px 0;"><strong>Name:</strong> <?= htmlspecialchars($data['full_name']) ?></p>
                        <p style="margin: 5px 0;"><strong>Age:</strong> <?= $data['age'] ?> years</p>
                        <p style="margin: 5px 0;"><strong>Country:</strong> <?= htmlspecialchars($data['country']) ?></p>
                        <p style="margin: 5px 0;"><strong>Weight:</strong> <?= $data['weight'] ?> kg</p>
                        <p style="margin: 5px 0;"><strong>Height:</strong> <?= $data['height'] ?> cm</p>
                        <p style="margin: 5px 0;"><strong>Patient Email:</strong> <?= htmlspecialchars($patient_email) ?></p>
                    </div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #333;">Consultation Details</h3>
                        <p style="margin: 5px 0;"><strong>Tracking Number:</strong> <?= $data['numero_consultation'] ?></p>
                        <p style="margin: 5px 0;"><strong>Consultation Fee:</strong> <?= $data['consultation_prix'] ?> <?= $data['consultation_devise'] ?></p>
                        <p style="margin: 5px 0;"><strong>Previous Consultation:</strong> <?= ($data['previous_consultation'] == 'yes' ? 'Yes' : 'No') ?></p>
                    </div>
                    <div style="background: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                        <h3 style="margin-top: 0; color: #856404;">Symptoms Description</h3>
                        <p style="margin: 0; white-space: pre-wrap;"><?= nl2br(htmlspecialchars($data['symptoms'])) ?></p>
                    </div>
                    <div style="background: #e7f3ff; padding: 20px; border-radius: 5px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #0d6efd;">Duration</h3>
                        <p style="margin: 0;"><?= htmlspecialchars($data['symptoms_duration'] ?: 'Not specified') ?></p>
                    </div>
                    <div style="margin: 20px 0;">
                        <h3>Attached Documents:</h3>
                        <ul style="line-height: 1.8;"><?= $attachments_list ?></ul>
                    </div>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="<?= base_url('Dashboard/doctor_dashboard') ?>" style="background: #198754; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">View in Dashboard</a>
                    </div>
                    <p style="background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
                        <strong>Action Required:</strong> Please confirm or propose a new time for this consultation within 24 hours.
                    </p>
                </div>
                <div style="background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d;">
                    <p>This is an automated message from <?= htmlspecialchars($site_name) ?>.</p>
                    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function _build_admin_email($data, $patient, $doctor, $site_name) {
        $doctor_name = $doctor ? htmlspecialchars($doctor['prenom']) . ' ' . htmlspecialchars($doctor['nom']) : 'Not assigned';
        $patient_email = $patient ? $patient['email'] : 'N/A';
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; margin: 0;">
            <div style="max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden;">
                <div style="background: #6c757d; color: white; padding: 20px; text-align: center;">
                    <h2 style="margin:0;">New Consultation Created</h2>
                </div>
                <div style="padding: 25px;">
                    <p><strong>Tracking Number:</strong> <?= $data['numero_consultation'] ?></p>
                    <p><strong>Date:</strong> <?= date('Y-m-d H:i:s') ?></p>
                    <hr style="border: none; border-top: 1px solid #dee2e6; margin: 20px 0;">
                    <p><strong>Patient:</strong> <?= htmlspecialchars($data['full_name']) ?> (<?= htmlspecialchars($patient_email) ?>)</p>
                    <p><strong>Doctor:</strong> <?= $doctor_name ?></p>
                    <p><strong>Amount:</strong> <?= $data['consultation_prix'] ?> <?= $data['consultation_devise'] ?></p>
                    <p><strong>Status:</strong> Pending</p>
                    <p style="text-align: center; margin-top: 30px;">
                        <a href="<?= base_url('admin/consultations') ?>" style="background: #6c757d; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View in Admin Panel</a>
                    </p>
                </div>
                <div style="background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px;">
                    Automated notification from <?= htmlspecialchars($site_name) ?>.
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public function processConfirmation() {
        // Votre logique de traitement
        $email_sent = $this->_send_consultation_emails($data);
        if (!$email_sent) {
            log_message('warning', 'Emails not sent for consultation: ' . $data['numero_consultation']);
        }
        redirect('consultations/entente/confirmation_success');
        exit;
    }
}