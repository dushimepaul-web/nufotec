<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PatientDashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // Vérifier si l'utilisateur est connecté et est un patient
        if (!function_exists('is_patient')) {
            redirect('Auth/login');
            return;
        }
        is_patient();
        
        $this->load->database();
        $this->load->library(['form_validation', 'upload', 'session']);
        $this->load->helper(['url', 'form', 'file', 'download', 'date']);
        
        // Vérifier que l'utilisateur existe en session
        if (!$this->session->userdata('user_id')) {
            redirect('Auth/logout');
            return;
        }
    }

    /**
     * Tableau de bord principal du patient
     */
    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        if (!$user_id) {
            redirect('Auth/logout');
            return;
        }
        
        $data = array();

        // Informations de l'utilisateur - avec gestion d'erreur
        $user_query = $this->db->where('id', $user_id)->get('users');
        $data['user'] = $user_query->row();
        
        if (!$data['user']) {
            // Créer un utilisateur temporaire pour éviter les erreurs
            $data['user'] = new stdClass();
            $data['user']->id = $user_id;
            $data['user']->nom = '';
            $data['user']->prenom = '';
            $data['user']->email = '';
            $data['user']->telephone = '';
            $data['user']->photo = null;
            $data['user']->date_naissance = null;
            $data['user']->genre = null;
        }
        
        // Configuration du site
        $data['settings'] = array();
        if ($this->db->table_exists('configurations')) {
            $settings_query = $this->db->get('configurations');
            foreach ($settings_query->result() as $setting) {
                $data['settings'][$setting->cle] = $setting->valeur;
            }
        }
        
        $data['methodes_paiement'] = array();
        if ($this->db->table_exists('mode_payement')) {
            $data['methodes_paiement'] = $this->Model->read('mode_payement', null, 'id_mode_payement');
        }

        // Statistiques
        $data['stats'] = $this->get_patient_stats($user_id);

        // Consultations à venir
        $data['upcoming_consultations'] = $this->get_upcoming_consultations($user_id);

        // Toutes les consultations
        $data['all_consultations'] = $this->get_all_consultations($user_id);

        // Historique des consultations
        $data['recent_consultations'] = $this->get_recent_consultations($user_id);

        // Ordonnances récentes
        $data['recent_prescriptions'] = $this->get_recent_prescriptions($user_id);

        // Messages non lus
        $data['unread_messages'] = $this->get_unread_messages($user_id);

        // Notifications
        $data['notifications'] = $this->get_notifications($user_id);

        // Documents médicaux
        $data['medical_documents'] = $this->get_medical_documents($user_id);

        // Historique des paiements
        $data['payment_history'] = $this->get_payment_history($user_id);

        // Médecins favoris
        $data['favorite_doctors'] = $this->get_favorite_doctors($user_id);

        // Commandes du patient
        if (method_exists($this, 'get_commande_fait')) {
            $data['commandefaite'] = $this->get_commande_fait($user_id);
        } else {
            $data['commandefaite'] = array();
        }

        $this->load->view('patient', $data);
    }

    // --------------------------------------------------------------------
    // STATISTIQUES
    // --------------------------------------------------------------------

    private function get_patient_stats($user_id) {
        $stats = array(
            'total_consultations' => 0,
            'consultations_this_month' => 0,
            'upcoming_appointments' => 0,
            'active_prescriptions' => 0,
            'health_score' => 85
        );

        // Vérifier que la table existe
        if (!$this->db->table_exists('consultations')) {
            return $stats;
        }

        // Total consultations
        $stats['total_consultations'] = $this->db
            ->where('patient_id', $user_id)
            ->count_all_results('consultations');

        // Consultations ce mois
        $stats['consultations_this_month'] = $this->db
            ->where('patient_id', $user_id)
            ->where('MONTH(created_at)', date('m'))
            ->where('YEAR(created_at)', date('Y'))
            ->count_all_results('consultations');

        // Rendez-vous à venir
        $stats['upcoming_appointments'] = $this->db
            ->where('patient_id', $user_id)
            ->where('date_souhaitee >=', date('Y-m-d H:i:s'))
            ->where_in('statut', array('en_attente', 'confirmee'))
            ->count_all_results('consultations');

        // Ordonnances actives (stockées en JSON dans la table consultations)
        $active_from_json = $this->db
            ->where('patient_id', $user_id)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->where('ordonnances IS NOT NULL')
            ->where('ordonnances !=', '')
            ->where('ordonnances !=', '[]')
            ->count_all_results('consultations');

        $stats['active_prescriptions'] = $active_from_json;
        $stats['health_score'] = $this->calculate_health_score($user_id);

        return $stats;
    }

    private function calculate_health_score($user_id) {
        $base_score = 85;
        
        $recent_consultations = $this->db
            ->where('patient_id', $user_id)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-6 months')))
            ->count_all_results('consultations');
        
        $score = min(100, $base_score + ($recent_consultations * 2));
        
        return $score;
    }

    // --------------------------------------------------------------------
    // CONSULTATIONS
    // --------------------------------------------------------------------

    private function get_upcoming_consultations($user_id) {
        if (!$this->db->table_exists('consultations')) {
            return array();
        }
        
        return $this->db
            ->select('
                c.id, 
                c.numero_consultation, 
                c.date_souhaitee, 
                c.type, 
                c.statut, 
                c.symptomes, 
                c.room_id, 
                c.room_url, 
                c.duree_minutes, 
                m.id as medecin_id, 
                m.specialite, 
                u.nom as medecin_nom, 
                u.prenom as medecin_prenom, 
                u.photo as medecin_photo
            ')
            ->from('consultations c')
            ->join('medecins m', 'm.id = c.medecin_id', 'left')
            ->join('users u', 'u.id = m.user_id', 'left')
            ->where('c.patient_id', $user_id)
            ->where('c.date_souhaitee >=', date('Y-m-d H:i:s'))
            ->where_in('c.statut', array('en_attente', 'confirmee', 'en_cours'))
            ->order_by('c.date_souhaitee', 'ASC')
            ->limit(10)
            ->get()
            ->result();
    }

    private function get_all_consultations($user_id) {
        if (!$this->db->table_exists('consultations')) {
            return array();
        }
        
        return $this->db
            ->select('c.*, m.specialite, u.nom as medecin_nom, c.ordonnances, u.prenom as medecin_prenom, u.photo as medecin_photo')
            ->from('consultations c')
            ->join('medecins m', 'm.id = c.medecin_id', 'left')
            ->join('users u', 'u.id = m.user_id', 'left')
            ->where('c.patient_id', $user_id)
            ->where_in('c.statut', array('en_attente', 'confirmee', 'en_cours', 'terminee', 'annulee'))
            ->order_by('c.date_souhaitee', 'DESC')
            ->limit(20)
            ->get()
            ->result();
    }

    private function get_recent_consultations($user_id) {
        if (!$this->db->table_exists('consultations')) {
            return array();
        }
        
        return $this->db
            ->select('
                c.id,
                c.numero_consultation,
                c.date_souhaitee,
                c.date_fin,
                c.type,
                c.statut,
                c.duree_minutes,
                c.ordonnances,
                c.diagnostic,
                c.traitement,
                m.id as medecin_id,
                m.specialite,
                u.nom as medecin_nom,
                u.prenom as medecin_prenom,
                u.photo as medecin_photo,
                (CASE 
                    WHEN c.ordonnances IS NOT NULL AND c.ordonnances != "" AND c.ordonnances != "[]" 
                    THEN 1 ELSE 0 
                END) as has_ordonnance
            ', FALSE)
            ->from('consultations c')
            ->join('medecins m', 'm.id = c.medecin_id', 'left')
            ->join('users u', 'u.id = m.user_id', 'left')
            ->where('c.patient_id', $user_id)
            ->where_in('c.statut', array('terminee', 'annulee'))
            ->order_by('c.date_fin', 'DESC')
            ->limit(10)
            ->get()
            ->result();
    }

    // --------------------------------------------------------------------
    // ORDONNANCES
    // --------------------------------------------------------------------

    private function get_recent_prescriptions($user_id) {
        $prescriptions = array();

        // 1. Ordonnances depuis la table prescriptions
        if ($this->db->table_exists('prescriptions')) {
            $from_table = $this->db
                ->select('
                    p.id,
                    p.medicament,
                    p.dosage,
                    p.instructions,
                    p.date_prescription,
                    p.is_active,
                    c.id as consultation_id,
                    c.date_souhaitee as consultation_date,
                    u.nom as medecin_nom,
                    u.prenom as medecin_prenom
                ')
                ->from('prescriptions p')
                ->join('consultations c', 'c.id = p.consultation_id')
                ->join('medecins m', 'm.id = c.medecin_id', 'left')
                ->join('users u', 'u.id = m.user_id', 'left')
                ->where('c.patient_id', $user_id)
                ->order_by('p.date_prescription', 'DESC')
                ->limit(10)
                ->get()
                ->result();

            foreach ($from_table as $p) {
                $pres = new stdClass();
                $pres->id = 'pres_' . $p->id;
                $pres->medicament = $p->medicament;
                $pres->dosage = $p->dosage;
                $pres->instructions = $p->instructions;
                $pres->medecin_nom = $p->medecin_nom;
                $pres->medecin_prenom = $p->medecin_prenom;
                $pres->consultation_date = $p->consultation_date;
                $pres->created_at = $p->date_prescription;
                $pres->is_active = $p->is_active;
                $pres->source = 'table';
                $prescriptions[] = $pres;
            }
        }

        // 2. Ordonnances depuis le champ JSON
        $from_json = $this->db
            ->select('
                c.id,
                c.numero_consultation,
                c.ordonnances,
                c.created_at,
                c.date_souhaitee as consultation_date,
                u.nom as medecin_nom,
                u.prenom as medecin_prenom
            ')
            ->from('consultations c')
            ->join('medecins m', 'm.id = c.medecin_id', 'left')
            ->join('users u', 'u.id = m.user_id', 'left')
            ->where('c.patient_id', $user_id)
            ->where('c.ordonnances IS NOT NULL')
            ->where('c.ordonnances !=', '')
            ->where('c.ordonnances !=', '[]')
            ->order_by('c.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->result();

        foreach ($from_json as $c) {
            $files = json_decode($c->ordonnances, true);
            if (is_array($files) && !empty($files)) {
                foreach ($files as $index => $file) {
                    $pres = new stdClass();
                    $pres->id = 'file_' . $c->id . '_' . $index;
                    $pres->medicament = 'Ordonnance fichier #' . ($index + 1);
                    $pres->dosage = '';
                    $pres->instructions = '';
                    $pres->medecin_nom = $c->medecin_nom;
                    $pres->medecin_prenom = $c->medecin_prenom;
                    $pres->consultation_date = $c->consultation_date;
                    $pres->created_at = $c->created_at;
                    $pres->is_active = 1;
                    $pres->source = 'json';
                    $pres->filename = $file;
                    $pres->consultation_id = $c->id;
                    $pres->file_url = base_url('attachments/Consultations/' . $file);
                    $prescriptions[] = $pres;
                }
            }
        }

        // Trier
        usort($prescriptions, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return array_slice($prescriptions, 0, 15);
    }

    // --------------------------------------------------------------------
    // MESSAGES
    // --------------------------------------------------------------------

    private function get_unread_messages($user_id) {
        if (!$this->db->table_exists('consultation_chats')) {
            return array();
        }
        
        return $this->db
            ->select('
                cc.id,
                cc.message,
                cc.created_at,
                cc.is_read,
                u.nom as sender_nom,
                u.prenom as sender_prenom,
                u.photo as sender_photo,
                m.id as medecin_id,
                m.specialite
            ')
            ->from('consultation_chats cc')
            ->join('users u', 'u.id = cc.sender_id', 'left')
            ->join('medecins m', 'm.user_id = cc.sender_id', 'left')
            ->where('cc.receiver_id', $user_id)
            ->where('cc.is_read', 0)
            ->order_by('cc.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->result();
    }

    // --------------------------------------------------------------------
    // NOTIFICATIONS
    // --------------------------------------------------------------------

    private function get_notifications($user_id) {
        $notifications = array();

        // Notification: Rendez-vous à venir
        if ($this->db->table_exists('consultations')) {
            $soon = $this->db
                ->select('c.*, u.prenom, u.nom')
                ->from('consultations c')
                ->join('medecins m', 'm.id = c.medecin_id')
                ->join('users u', 'u.id = m.user_id')
                ->where('c.patient_id', $user_id)
                ->where('c.date_souhaitee >=', date('Y-m-d H:i:s'))
                ->where('c.date_souhaitee <=', date('Y-m-d H:i:s', strtotime('+24 hours')))
                ->where('c.statut', 'confirmee')
                ->get()
                ->result();

            foreach ($soon as $apt) {
                $notif = new stdClass();
                $notif->id = 'apt_' . $apt->id;
                $notif->titre = 'Rendez-vous imminent';
                $notif->message = 'Votre consultation avec Dr. ' . ($apt->prenom ?? '') . ' ' . ($apt->nom ?? '') . ' est prévue le ' . date('d/m/Y à H:i', strtotime($apt->date_souhaitee));
                $notif->created_at = date('Y-m-d H:i:s');
                $notif->type = 'appointment';
                $notif->icon = 'calendar';
                $notifications[] = $notif;
            }

            // Notification: Paiement en attente
            $unpaid = $this->db
                ->where('patient_id', $user_id)
                ->where('paiement_statut', 'en_attente')
                ->where('statut', 'confirmee')
                ->count_all_results('consultations');

            if ($unpaid > 0) {
                $notif = new stdClass();
                $notif->id = 'pay_pending';
                $notif->titre = 'Paiement en attente';
                $notif->message = 'Vous avez ' . $unpaid . ' consultation(s) en attente de paiement.';
                $notif->created_at = date('Y-m-d H:i:s');
                $notif->type = 'payment';
                $notif->icon = 'credit-card';
                $notifications[] = $notif;
            }

            // Notification: Nouvelles ordonnances
            $new_prescriptions = $this->db
                ->where('patient_id', $user_id)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->where('ordonnances IS NOT NULL')
                ->where('ordonnances !=', '')
                ->count_all_results('consultations');

            if ($new_prescriptions > 0) {
                $notif = new stdClass();
                $notif->id = 'new_pres';
                $notif->titre = 'Nouvelle ordonnance';
                $notif->message = 'Une nouvelle ordonnance est disponible dans votre dossier médical.';
                $notif->created_at = date('Y-m-d H:i:s');
                $notif->type = 'prescription';
                $notif->icon = 'capsule';
                $notifications[] = $notif;
            }
        }

        return $notifications;
    }

    // --------------------------------------------------------------------
    // DOCUMENTS MÉDICAUX
    // --------------------------------------------------------------------

    private function get_medical_documents($user_id) {
        $documents = array();

        // 1. Documents issus des consultations
        if ($this->db->table_exists('consultations')) {
            $consultations = $this->db
                ->select('
                    c.id,
                    c.numero_consultation,
                    c.date_souhaitee,
                    c.examens_demandes,
                    c.ordonnances,
                    c.preuve_paiement,
                    c.created_at
                ')
                ->from('consultations c')
                ->where('c.patient_id', $user_id)
                ->order_by('c.created_at', 'DESC')
                ->limit(20)
                ->get()
                ->result();

            foreach ($consultations as $c) {
                // Examens demandés
                $examens = json_decode($c->examens_demandes ?? '[]', true);
                if (is_array($examens)) {
                    foreach ($examens as $file) {
                        $documents[] = $this->build_document_object($file, 'examen', $c);
                    }
                }
                // Ordonnances
                $ordonnances = json_decode($c->ordonnances ?? '[]', true);
                if (is_array($ordonnances)) {
                    foreach ($ordonnances as $file) {
                        $documents[] = $this->build_document_object($file, 'ordonnance', $c);
                    }
                }
                // Preuve de paiement
                if (!empty($c->preuve_paiement)) {
                    $documents[] = $this->build_document_object($c->preuve_paiement, 'paiement', $c);
                }
            }
        }

        // Tri
        usort($documents, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return $documents;
    }

    private function build_document_object($filename, $type, $consultation) {
        $doc = new stdClass();
        $doc->id = md5($filename . ($consultation->id ?? ''));
        $doc->filename = $filename;
        $doc->original_name = basename($filename);
        $doc->type = $type;
        $doc->mime_type = $this->get_mime_type($filename);
        $doc->file_size = $this->get_file_size($filename);
        $doc->consultation_id = $consultation->id ?? null;
        $doc->consultation_numero = $consultation->numero_consultation ?? null;
        $doc->created_at = $consultation->created_at ?? date('Y-m-d H:i:s');
        $doc->file_url = base_url('attachments/Consultations/' . $filename);
        $doc->download_url = base_url('Dashboard/PatientDashboard/download/' . ($consultation->id ?? '') . '/' . urlencode($filename));
        $doc->view_url = base_url('Dashboard/PatientDashboard/view/' . ($consultation->id ?? '') . '/' . urlencode($filename));
        return $doc;
    }

    private function get_mime_type($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime_types = array(
            'pdf'   => 'application/pdf',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'webp'  => 'image/webp',
            'doc'   => 'application/msword',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt'   => 'text/plain'
        );
        return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
    }

    private function get_file_size($filename) {
        $path = FCPATH . 'attachments/Consultations/' . $filename;
        if (file_exists($path)) {
            return filesize($path);
        }
        return 0;
    }

    // --------------------------------------------------------------------
    // PAIEMENTS
    // --------------------------------------------------------------------

    private function get_payment_history($user_id) {
        if (!$this->db->table_exists('consultations')) {
            return array();
        }
        
        return $this->db
            ->select('
                c.id,
                c.numero_consultation,
                c.prix_ttc,
                c.devise,
                c.paiement_statut,
                c.mode_paiement,
                c.created_at as payment_date,
                u.nom as medecin_nom,
                u.prenom as medecin_prenom
            ')
            ->from('consultations c')
            ->join('medecins m', 'm.id = c.medecin_id', 'left')
            ->join('users u', 'u.id = m.user_id', 'left')
            ->where('c.patient_id', $user_id)
            ->where('c.paiement_statut', 'paye')
            ->order_by('c.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->result();
    }

    // --------------------------------------------------------------------
    // MÉDECINS
    // --------------------------------------------------------------------

    private function get_favorite_doctors($user_id) {
        if (!$this->db->table_exists('consultations')) {
            return array();
        }
        
        return $this->db
            ->select('
                m.id as medecin_id,
                m.specialite,
                m.honoraires_consultation,
                m.note_moyenne,
                u.nom,
                u.prenom,
                u.photo,
                COUNT(c.id) as consultation_count,
                MAX(c.created_at) as last_consultation
            ')
            ->from('consultations c')
            ->join('medecins m', 'm.id = c.medecin_id', 'left')
            ->join('users u', 'u.id = m.user_id', 'left')
            ->where('c.patient_id', $user_id)
            ->where('c.medecin_id IS NOT NULL')
            ->group_by('c.medecin_id')
            ->order_by('consultation_count', 'DESC')
            ->order_by('last_consultation', 'DESC')
            ->limit(5)
            ->get()
            ->result();
    }

    // --------------------------------------------------------------------
    // ACTIONS PUBLIQUES
    // --------------------------------------------------------------------

    public function get_dashboard_data() {
        $user_id = $this->session->userdata('user_id');

        $unread_count = 0;

        $data = array(
            'stats' => $this->get_patient_stats($user_id),
            'upcoming_count' => count($this->get_upcoming_consultations($user_id)),
            'unread_count' => $unread_count,
            'notifications_count' => count($this->get_notifications($user_id))
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    // --------------------------------------------------------------------
    // GESTION DES DOCUMENTS
    // --------------------------------------------------------------------

    public function view($consultation_id, $filename) {
        $user_id = $this->session->userdata('user_id');
        
        if (!$this->check_document_access($user_id, $consultation_id, $filename)) {
            show_404();
        }

        $file_path = FCPATH . 'attachments/Consultations/' . urldecode($filename);
        
        if (!file_exists($file_path)) {
            show_404();
        }

        $mime = $this->get_mime_type($filename);
        
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        readfile($file_path);
        exit;
    }

    public function download($consultation_id, $filename) {
        $user_id = $this->session->userdata('user_id');
        
        if (!$this->check_document_access($user_id, $consultation_id, $filename)) {
            show_404();
        }

        $file_path = FCPATH . 'attachments/Consultations/' . urldecode($filename);
        
        if (!file_exists($file_path)) {
            show_404();
        }

        force_download(basename($filename), file_get_contents($file_path));
    }

    private function check_document_access($user_id, $consultation_id, $filename) {
        $consultation = $this->db
            ->select('patient_id, ordonnances, examens_demandes, preuve_paiement')
            ->from('consultations')
            ->where('id', $consultation_id)
            ->get()
            ->row();

        if (!$consultation || $consultation->patient_id != $user_id) {
            return false;
        }

        $files = array_merge(
            json_decode($consultation->ordonnances ?? '[]', true),
            json_decode($consultation->examens_demandes ?? '[]', true),
            array($consultation->preuve_paiement)
        );

        return in_array(urldecode($filename), array_filter($files));
    }

    public function view_document($doc_id) {
        show_404();
    }

    public function download_document_by_id($document_id) {
        show_404();
    }

    // --------------------------------------------------------------------
    // GESTION DU PROFIL
    // --------------------------------------------------------------------

    public function update_profile() {
        $user_id = $this->session->userdata('user_id');

        $this->form_validation->set_rules('prenom', 'Prénom', 'required|trim');
        $this->form_validation->set_rules('nom', 'Nom', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Dashboard/PatientDashboard');
            return;
        }

        $email = $this->input->post('email');
        
        $existing = $this->db
            ->where('email', $email)
            ->where('id !=', $user_id)
            ->get('users')
            ->row();
            
        if ($existing) {
            $this->session->set_flashdata('error', 'Cet email est déjà utilisé.');
            redirect('Dashboard/PatientDashboard');
            return;
        }

        $data = array(
            'prenom' => $this->input->post('prenom'),
            'nom'    => $this->input->post('nom'),
            'email'  => $email,
            'telephone' => $this->input->post('telephone'),
            'date_naissance' => $this->input->post('date_naissance') ?: null,
            'genre'  => $this->input->post('genre') ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Upload photo
        if (!empty($_FILES['photo']['name'])) {
            $photo = $this->upload_profile_photo($_FILES['photo']);
            if ($photo !== false) {
                $data['photo'] = $photo;
            }
        }

        // Changement mot de passe
        $current_password = $this->input->post('current_password');
        $new_password = $this->input->post('new_password');
        
        if (!empty($current_password) && !empty($new_password)) {
            if ($new_password !== $this->input->post('confirm_password')) {
                $this->session->set_flashdata('error', 'Les mots de passe ne correspondent pas.');
                redirect('Dashboard/PatientDashboard');
                return;
            }

            if (strlen($new_password) < 8) {
                $this->session->set_flashdata('error', 'Mot de passe trop court (8 caractères min).');
                redirect('Dashboard/PatientDashboard');
                return;
            }

            $user = $this->db->select('password')->where('id', $user_id)->get('users')->row();
            if (!password_verify($current_password, $user->password)) {
                $this->session->set_flashdata('error', 'Mot de passe actuel incorrect.');
                redirect('Dashboard/PatientDashboard');
                return;
            }

            $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        $this->db->where('id', $user_id)->update('users', $data);

        $this->session->set_flashdata('success', 'Profil mis à jour avec succès.');
        redirect('Dashboard/PatientDashboard');
    }

    private function upload_profile_photo($file) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $this->session->set_flashdata('error', 'Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP.');
            return false;
        }

        $upload_path = FCPATH . 'attachments/Users/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $new_filename = 'user_' . $this->session->userdata('user_id') . '_' . time() . '.' . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path . $new_filename)) {
            return $new_filename;
        }
        
        return false;
    }

    // --------------------------------------------------------------------
    // MESSAGES
    // --------------------------------------------------------------------

    public function mark_all_notifications_read() {
        $this->output->set_content_type('application/json');
        echo json_encode(array('success' => true));
    }
}