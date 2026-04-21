<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PatientDashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // Vérifier si l'utilisateur est connecté et est un patient
        is_patient();
        
        $this->load->database();
        $this->load->library(['form_validation', 'upload', 'session']);
        $this->load->helper(['url', 'form', 'file', 'download', 'date']);
    }

    /**
     * Tableau de bord principal du patient
     */
    public function index() {
        $user_id = $this->session->userdata('user_id');
        $data = array();

        // Informations de l'utilisateur
        $data['user'] = $this->db->where('id', $user_id)->get('users')->row();
        if (!$data['user']) {
            show_error('Utilisateur non trouvé');
            return;
        }
        
        $data['methodes_paiement'] = $this->Model->read('mode_payement', null, 'id_mode_payement');

        // Statistiques
        $data['stats'] = $this->get_patient_stats($user_id);

        // Consultations à venir
        $data['upcoming_consultations'] = $this->get_upcoming_consultations($user_id);

        $data['all_consultations'] = $this->get_all_consultations($user_id);

        // Historique des consultations
        $data['recent_consultations'] = $this->get_recent_consultations($user_id);

        // Ordonnances récentes (depuis la table prescriptions ET champ JSON)
        $data['recent_prescriptions'] = $this->get_recent_prescriptions($user_id);

        // Messages non lus
        $data['unread_messages'] = $this->get_unread_messages($user_id);

        // Notifications dynamiques
        $data['notifications'] = $this->get_notifications($user_id);

        // Documents médicaux (examens + ordonnances + preuve_paiement)
        $data['medical_documents'] = $this->get_medical_documents($user_id);

        // Historique des paiements
        $data['payment_history'] = $this->get_payment_history($user_id);

        // Médecins consultés
        $data['favorite_doctors'] = $this->get_favorite_doctors($user_id);

        // Commandes du patient (si cette méthode existe dans un parent ou trait)
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

    /**
     * Statistiques complètes du patient
     */
    private function get_patient_stats($user_id) {
        $stats = array();

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


        // Ordonnances actives depuis la table prescriptions
        $active_from_table = $this->db
            ->from('prescriptions')
            ->join('consultations', 'consultations.id = prescriptions.consultation_id')
            ->where('prescriptions.is_active', 1)
            ->where('consultations.patient_id', $user_id)
            ->count_all_results();

        // Ordonnances actives depuis le champ JSON consultations
        $active_from_json = $this->db
            ->where('patient_id', $user_id)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->where('ordonnances IS NOT NULL')
            ->where('ordonnances !=', '')
            ->where('ordonnances !=', '[]')
            ->count_all_results('consultations');

        $stats['active_prescriptions'] = $active_from_table + $active_from_json;

        // Score de santé (calculé)
        $stats['health_score'] = $this->calculate_health_score($user_id);

        return $stats;
    }

    /**
     * Calcule un score de santé basé sur les consultations récentes
     */
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

    /**
     * Consultations à venir avec infos médecin
     */
    private function get_upcoming_consultations($user_id) {
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
    return $this->db
        ->select('c.*, m.specialite, u.nom as medecin_nom,c.ordonnances, u.prenom as medecin_prenom, u.photo as medecin_photo')
        ->from('consultations c')
        ->join('medecins m', 'm.id = c.medecin_id', 'left')
        ->join('users u', 'u.id = m.user_id', 'left')
        ->where('c.patient_id', $user_id)
        // Retrait du WHERE date_souhaitee >= NOW() pour voir les terminées/annulées
        ->where_in('c.statut', array('en_attente', 'confirmee', 'en_cours','terminee','annulee'))
        ->order_by('c.date_souhaitee', 'DESC') // DESC pour voir les plus récentes en haut
        ->limit(20)
        ->get()
        ->result();
}
    /**
     * Historique des consultations terminées
     */
    private function get_recent_consultations($user_id) {
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

    /**
     * Consultations passées (alias pour compatibilité)
     */
    private function get_past_consultations($user_id) {
        return $this->get_recent_consultations($user_id);
    }

    // --------------------------------------------------------------------
    // ORDONNANCES
    // --------------------------------------------------------------------

    /**
     * Ordonnances récentes (table prescriptions + champ JSON consultations)
     */
    private function get_recent_prescriptions($user_id) {
        $prescriptions = array();

        // 1. Ordonnances depuis la table prescriptions
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

        // 2. Ordonnances depuis le champ JSON consultations.ordonnances
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

        // Trier par date décroissante
        usort($prescriptions, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return array_slice($prescriptions, 0, 15);
    }

    // --------------------------------------------------------------------
    // MESSAGES
    // --------------------------------------------------------------------

    /**
     * Messages non lus depuis consultation_chats
     */
    private function get_unread_messages($user_id) {
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
            ->join('users u', 'u.id = cc.sender_id')
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

    /**
     * Notifications dynamiques basées sur les données
     */
    private function get_notifications($user_id) {
        $notifications = array();

        // Notification: Rendez-vous à venir dans moins de 24h
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
            $notif->message = 'Votre consultation avec Dr. ' . $apt->prenom . ' ' . $apt->nom . ' est prévue le ' . date('d/m/Y à H:i', strtotime($apt->date_souhaitee));
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

        return $notifications;
    }

    // --------------------------------------------------------------------
    // DOCUMENTS MÉDICAUX
    // --------------------------------------------------------------------

    /**
     * Récupère tous les documents médicaux du patient
     */
    private function get_medical_documents($user_id) {
        $documents = [];

        // 1. Documents issus des consultations (JSON dans les champs)
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
            $examens = json_decode($c->examens_demandes, true);
            if (is_array($examens)) {
                foreach ($examens as $file) {
                    $documents[] = $this->build_document_object($file, 'examen', $c);
                }
            }
            // Ordonnances
            $ordonnances = json_decode($c->ordonnances, true);
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

        // 2. Documents depuis la table `documents` (téléversements autonomes)
        $db_docs = $this->db
            ->select('*')
            ->from('documents')
            ->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit(50)
            ->get()
            ->result();

        foreach ($db_docs as $d) {
            $documents[] = $this->_build_document_from_db($d);
        }

        // Tri global par date de création décroissante
        usort($documents, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return $documents;
    }

    /**
     * Construit un objet document à partir d'une ligne de la table `documents`
     */
    private function _build_document_from_db($row) {
        $doc = new stdClass();
        $doc->id               = 'db_' . $row->id;
        $doc->filename         = $row->filename;
        $doc->original_name    = $row->original_name ?: $row->filename;
        $doc->type             = $row->type ?: 'document';
        $doc->consultation_id  = $row->consultation_id;
        $doc->consultation_numero = null;
        $doc->created_at       = $row->created_at;
        $doc->file_url         = base_url('attachments/Documents/' . $row->filename);
        $doc->download_url     = base_url('Dashboard/PatientDashboard/download_document_by_id/' . $row->id);
        $doc->view_url         = base_url('Dashboard/PatientDashboard/view_document/' . $row->id);
        return $doc;
    }

    /**
     * Construit un objet document standardisé
     */
    private function build_document_object($filename, $type, $consultation) {
        $doc = new stdClass();
        $doc->id = md5($filename . $consultation->id);
        $doc->filename = $filename;
        $doc->original_name = basename($filename);
        $doc->type = $type;
        $doc->mime_type = $this->get_mime_type($filename);
        $doc->file_size = $this->get_file_size($filename);
        $doc->consultation_id = $consultation->id;
        $doc->consultation_numero = $consultation->numero_consultation;
        $doc->created_at = $consultation->created_at;
        $doc->file_url = base_url('attachments/Consultations/' . $filename);
        $doc->download_url = base_url('patient_dashboard/download/' . $consultation->id . '/' . urlencode($filename));
        $doc->view_url = base_url('patient_dashboard/view/' . $consultation->id . '/' . urlencode($filename));
        
        return $doc;
    }

    /**
     * Détermine le type MIME
     */
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

    /**
     * Récupère la taille du fichier
     */
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

    /**
     * Historique des paiements depuis la table consultations
     */
    private function get_payment_history($user_id) {
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

    /**
     * Médecins consultés avec statistiques
     */
    private function get_favorite_doctors($user_id) {
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
            ->join('medecins m', 'm.id = c.medecin_id')
            ->join('users u', 'u.id = m.user_id')
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
    // ACTIONS PUBLIQUES (API)
    // --------------------------------------------------------------------

    /**
     * API: Données du tableau de bord (AJAX polling)
     */
    public function get_dashboard_data() {
        $user_id = $this->session->userdata('user_id');

        $data = array(
            'stats' => $this->get_patient_stats($user_id),
            'upcoming_count' => count($this->get_upcoming_consultations($user_id)),
            'unread_count' => $this->db
                ->where('receiver_id', $user_id)
                ->where('is_read', 0)
                ->count_all_results('consultation_chats'),
            'notifications_count' => count($this->get_notifications($user_id))
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    // --------------------------------------------------------------------
    // GESTION DES DOCUMENTS (VUE/TÉLÉCHARGEMENT)
    // --------------------------------------------------------------------

    /**
     * Visualiser un document (affichage inline)
     */
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
        
        // Pour images et PDF: affichage inline
        if (in_array($mime, array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'))) {
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . basename($filename) . '"');
            readfile($file_path);
            exit;
        }
        
        // Autres: téléchargement
        force_download(basename($filename), file_get_contents($file_path));
    }

    /**
     * Télécharger un document
     */
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

    /**
     * Vérifie l'accès au document
     */
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

        // Vérifier que le fichier appartient bien à cette consultation
        $files = array_merge(
            json_decode($consultation->ordonnances ?: '[]', true),
            json_decode($consultation->examens_demandes ?: '[]', true),
            array($consultation->preuve_paiement)
        );

        return in_array(urldecode($filename), array_filter($files));
    }

    /**
     * Visualiser un document depuis la table documents
     */
    public function view_document($doc_id) {
        $user_id = (int)$this->session->userdata('user_id');
        $doc = $this->db->where('id', $doc_id)->where('user_id', $user_id)->get('documents')->row();
        if (!$doc) show_404();

        $file_path = FCPATH . 'attachments/Documents/' . $doc->filename;
        if (!file_exists($file_path)) show_404();

        $mime = $doc->mime_type ?: $this->get_mime_type($doc->filename);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . ($doc->original_name ?: $doc->filename) . '"');
        readfile($file_path);
        exit;
    }

    /**
     * Télécharger un document depuis la table documents
     */
    public function download_document_by_id($document_id) {
        $user_id = $this->session->userdata('user_id');
        
        // SÉCURITÉ: Vérifier que le document appartient bien à l'utilisateur connecté
        $doc = $this->db->where('id', $document_id)->where('user_id', $user_id)->get('documents')->row();
        
        if (!$doc) {
            show_404();
        }

        $file_path = FCPATH . 'attachments/Documents/' . $doc->filename;
        if (!file_exists($_file_path)) {
            show_404();
        }

        // Types à afficher dans le navigateur (inline)
        $inline_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $disposition = in_array($doc->mime_type, $inline_types) ? 'inline' : 'attachment';

        // Nettoyer le nom du fichier
        $filename = $doc->original_name ?: $doc->filename;
        $filename = str_replace(['"', "\n", "\r"], '', $filename);

        // Envoyer les en-têtes
        header('Content-Type: ' . $doc->mime_type);
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($file_path);
        exit;
    }

    /**
     * Télécharge un fichier (preuve, examen, ordonnance) associé à une consultation
     * CORRIGÉ: Vérification de propriété ajoutée
     */
    public function download_file($type, $consultation_id, $index = null)
    {
        $user_id = $this->session->userdata('user_id'); // Récupérer l'ID de l'utilisateur connecté
        
        $consultation = $this->Model->readOne('consultations', ['id' => $consultation_id]);
        
        // VÉRIFICATION DE SÉCURITÉ: La consultation doit exister et appartenir au patient connecté
        if (!$consultation || $consultation['patient_id'] != $user_id) {
            show_404();
        }

        $file_path = null;
        $filename = null;

        switch ($type) {
            case 'preuve':
                if (!empty($consultation['preuve_paiement'])) {
                    $file_path = FCPATH . 'attachments/Consultations/' . $consultation['preuve_paiement'];
                    $filename = $consultation['preuve_paiement'];
                }
                break;

            case 'examen':
                if (!empty($consultation['examens_demandes'])) {
                    $files = json_decode($consultation['examens_demandes'], true);
                    if (is_array($files) && isset($files[$index])) {
                        $file_path = FCPATH . 'attachments/Consultations/' . $files[$index];
                        $filename = $files[$index];
                    }
                }
                break;

            case 'ordonnance':
                if (!empty($consultation['ordonnances'])) {
                    $files = json_decode($consultation['ordonnances'], true);
                    if (is_array($files) && isset($files[$index])) {
                        $file_path = FCPATH . 'attachments/Consultations/' . $files[$index];
                        $filename = $files[$index];
                    }
                }
                break;
                
            default:
                show_404();
        }

        if (!$file_path || !file_exists($file_path)) {
            show_404();
        }

        // Déterminer le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);

        // Forcer l'affichage dans le navigateur pour les PDF/images
        $inline_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $disposition = in_array($mime, $inline_types) ? 'inline' : 'attachment';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($file_path);
        exit;
    }

    // --------------------------------------------------------------------
    // MESSAGES ET NOTIFICATIONS
    // --------------------------------------------------------------------

    /**
     * Marquer un message comme lu
     */
    public function mark_message_read($message_id = null) {
        if (!$message_id) {
            echo json_encode(array('success' => false));
            return;
        }

        $user_id = $this->session->userdata('user_id');
        
        $this->db
            ->where('id', $message_id)
            ->where('receiver_id', $user_id) // Vérifie que c'est bien le destinataire
            ->update('consultation_chats', array('is_read' => 1));
            
        echo json_encode(array('success' => true));
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function mark_all_notifications_read() {
        // Implémenter selon votre structure de notifications
        echo json_encode(array('success' => true));
    }

    // --------------------------------------------------------------------
    // GESTION DU PROFIL
    // --------------------------------------------------------------------

    /**
     * Mise à jour du profil patient
     */
    public function update_profile() {
        $user_id = $this->session->userdata('user_id');

        // Validation
        $this->form_validation->set_rules('prenom', 'Prénom', 'required|trim');
        $this->form_validation->set_rules('nom', 'Nom', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'trim');
        $this->form_validation->set_rules('date_naissance', 'Date de naissance', 'trim');
        $this->form_validation->set_rules('genre', 'Genre', 'in_list[M,F,Autre]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($this->current_lang . '/home-patient');
            return;
        }

        $email = $this->input->post('email');
        
        // Vérifier unicité email
        $existing = $this->db
            ->where('email', $email)
            ->where('id !=', $user_id)
            ->get('users')
            ->row();
            
        if ($existing) {
            $this->session->set_flashdata('error', 'Cet email est déjà utilisé.');
            redirect($this->current_lang . '/home-patient');
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
            if ($photo === false) {
                $this->session->set_flashdata('error', 'Erreur upload photo. Formats: jpg, png, gif, webp');
                redirect($this->current_lang . '/home-patient');
                return;
            }
            
            // Supprimer ancienne photo
            $old = $this->db->select('photo')->where('id', $user_id)->get('users')->row();
            if ($old && $old->photo && $old->photo != 'default-avatar.png') {
                $old_path = FCPATH . 'attachments/Users/' . $old->photo;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            
            $data['photo'] = $photo;
        }

        // Changement mot de passe
        $current_password = $this->input->post('current_password');
        $new_password = $this->input->post('new_password');
        
        if (!empty($current_password) && !empty($new_password)) {
            if ($new_password !== $this->input->post('confirm_password')) {
                $this->session->set_flashdata('error', 'Les mots de passe ne correspondent pas.');
                redirect($this->current_lang . '/home-patient');
                return;
            }

            if (strlen($new_password) < 8) {
                $this->session->set_flashdata('error', 'Mot de passe trop court (8 caractères min).');
                redirect($this->current_lang . '/home-patient');
                return;
            }

            $user = $this->db->select('password')->where('id', $user_id)->get('users')->row();
            if (!password_verify($current_password, $user->password)) {
                $this->session->set_flashdata('error', 'Mot de passe actuel incorrect.');
                redirect($this->current_lang . '/home-patient');
                return;
            }

            $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        $this->db->where('id', $user_id)->update('users', $data);

        // Mise à jour session
        $this->session->set_userdata(array(
            'fullname' => $data['prenom'] . ' ' . $data['nom'],
            'email'    => $data['email'],
            'photo'    => $data['photo'] ?? $this->session->userdata('photo')
        ));

        $this->session->set_flashdata('success', 'Profil mis à jour avec succès.');
        redirect($this->current_lang . '/home-patient');
    }

    /**
     * Upload photo de profil
     */
    private function upload_profile_photo($file) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            return false;
        }

        $upload_path = FCPATH . 'attachments/Users/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $new_filename = 'user_' . $this->session->userdata('user_id') . '_' . time() . '.' . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path . $new_filename)) {
            // Redimensionner si nécessaire
            $this->resize_image($upload_path . $new_filename, 400, 400);
            return $new_filename;
        }
        
        return false;
    }

    /**
     * Redimensionne une image
     */
    private function resize_image($path, $max_width, $max_height) {
        if (!extension_loaded('gd')) {
            return;
        }

        list($width, $height, $type) = getimagesize($path);
        
        if ($width <= $max_width && $height <= $max_height) {
            return;
        }

        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);

        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($path); break;
            case IMAGETYPE_PNG: $src = imagecreatefrompng($path); break;
            case IMAGETYPE_GIF: $src = imagecreatefromgif($path); break;
            default: return;
        }

        $dst = imagecreatetruecolor($new_width, $new_height);
        
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($dst, $path, 90); break;
            case IMAGETYPE_PNG: imagepng($dst, $path, 8); break;
            case IMAGETYPE_GIF: imagegif($dst, $path); break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    // --------------------------------------------------------------------
    // COMPATIBILITÉ
    // --------------------------------------------------------------------

    /**
     * @deprecated Utilisez update_profile()
     */
    public function update_home() {
        $this->update_profile();
    }

    /**
     * @deprecated Utilisez download()
     */
    public function download_document($consultation_id, $filename) {
        $this->download($consultation_id, $filename);
    }
}