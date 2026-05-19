<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Entente extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
        
        // ============================================
        // CHARGER LA LIBRAIRIE EMAIL cPanel
        // ============================================
        $this->load->library('cpanel_email_lib');
        $this->load->model('Model');
    }
    
    /**
     * Liste des consultations en attente
     */
    public function index()
    {    
        $statut_recherche = 'en_attente';
        
        $this->db->select('
            c.*, 
            p.nom as patient_nom, 
            p.prenom as patient_prenom, 
            p.email as patient_email, 
            p.telephone as patient_telephone,
            u.nom as medecin_nom, 
            u.prenom as medecin_prenom,
            m.specialite as medecin_specialite,
            m.id as medecin_id
        ');
        $this->db->from('consultations c');
        $this->db->join('users p', 'p.id = c.patient_id', 'left');
        $this->db->join('medecins m', 'm.id = c.medecin_id', 'left');
        $this->db->join('users u', 'u.id = m.user_id', 'left');
        $this->db->where('c.statut', $statut_recherche);
        $this->db->order_by('c.created_at', 'DESC');
        
        $data['consultations'] = $this->db->get()->result_array();
        
        // Récupérer patients et médecins
        $this->db->where('type_utilisateur', 'patient');
        $this->db->where('is_active', 1);
        $this->db->order_by('nom', 'ASC');
        $data['patients'] = $this->db->get('users')->result_array();
        
        $this->db->select('m.*, u.nom, u.prenom, u.email, u.photo');
        $this->db->from('medecins m');
        $this->db->join('users u', 'u.id = m.user_id');
        $this->db->where('u.is_active', 1);
        $this->db->order_by('u.nom', 'ASC');
        $data['medecins'] = $this->db->get()->result_array();
        
        $this->load->view('Entente_View', $data);
    }

    //Liste des consultations confirmées
    public function confirme()
    {
        // Récupérer les consultations confirmées OU en cours
        $this->db->select('
            c.*, 
            p.nom as patient_nom, 
            p.prenom as patient_prenom, 
            p.email as patient_email, 
            p.telephone as patient_telephone,
            u.nom as medecin_nom, 
            u.prenom as medecin_prenom,
            m.specialite as medecin_specialite,
            m.id as medecin_id
        ');
        $this->db->from('consultations c');
        $this->db->join('users p', 'p.id = c.patient_id', 'left');
        $this->db->join('medecins m', 'm.id = c.medecin_id', 'left');
        $this->db->join('users u', 'u.id = m.user_id', 'left');
        
        // Afficher les consultations confirmées ou en cours
        $this->db->where_in('c.statut', ['confirmee', 'en_cours']);
        $this->db->order_by('c.date_confirmee', 'DESC');
        
        $consultations = $this->db->get()->result_array();
        
        // Filtrer selon les permissions de l'utilisateur connecté
        $user_id = $this->session->userdata('user_id');
        $user_role = $this->session->userdata('role');
        
        $filtered_consultations = [];
        foreach ($consultations as $consultation) {
            if ($user_role == 'patient' && $consultation['patient_id'] != $user_id) {
                continue;
            }
            if ($user_role == 'medecin' && $consultation['medecin_id'] != $user_id) {
                continue;
            }
            $filtered_consultations[] = $consultation;
        }
        
        $data['consultations'] = $filtered_consultations;
        
        // Récupérer les patients pour le formulaire de création
        $this->db->where('type_utilisateur', 'patient');
        $this->db->where('is_active', 1);
        $data['patients'] = $this->db->get('users')->result_array();
        
        // Récupérer les médecins pour le formulaire de création
        $this->db->select('m.*, u.nom, u.prenom, u.email, u.photo');
        $this->db->from('medecins m');
        $this->db->join('users u', 'u.id = m.user_id');
        $this->db->where('u.is_active', 1);
        $data['medecins'] = $this->db->get()->result_array();
        
        $this->load->view('allowed_View', $data);
    }
    
    /**
     * Changer le statut d'une consultation
     */
    public function changeStatus()
    {
        // Validation des entrées
        $id = $this->input->post('id');
        $statut = $this->input->post('statut');
        
        if (empty($id) || empty($statut)) {
            $this->session->set_flashdata('error', 'Données manquantes.');
            redirect(base_url('Consultations/Entente'));
            return;
        }

        // Récupérer les détails de la consultation
        $consultation = $this->getConsultationDetails($id);
        if (!$consultation) {
            $this->session->set_flashdata('error', 'Consultation introuvable.');
            redirect(base_url('Consultations/Entente'));
            return;
        }

        $data = ['statut' => $statut];
        $email_sent = false;

        try {
            // Traitement selon le statut
            switch ($statut) {
                case 'confirmee':
                    $email_sent = $this->processConfirmation($consultation, $data);
                    break;
                    
                case 'refusee':
                    $email_sent = $this->processRefusal($consultation, $data);
                    break;
                    
                case 'en_cours':
                    $data['date_debut'] = date('Y-m-d H:i:s');
                    break;
                    
                case 'terminee':
                    $this->processTermination($consultation, $data);
                    break;
            }

            // Mise à jour en base de données
            $rsp = $this->Model->update('consultations', ['id' => $id], $data);

            if ($rsp) {
                $this->handleSuccessResponse($statut, $email_sent);
            } else {
                throw new Exception('Erreur lors de la mise à jour en base de données.');
            }

        } catch (Exception $e) {
            log_message('error', 'Erreur dans changeStatus: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Une erreur est survenue: ' . $e->getMessage());
        }

        redirect(base_url('Consultations/Entente'));
    }

    /**
     * Traitement de la confirmation - Génère room_id
     */
    private function processConfirmation($consultation, &$data)
    {
        $date_debut = $this->input->post('date_debut');
        $date_fin = $this->input->post('date_fin');
        
        if (empty($date_debut) || empty($date_fin)) {
            throw new Exception('Les dates de début et de fin de téléconsultation sont requises.');
        }
        
        // Validation des dates
        if (strtotime($date_fin) <= strtotime($date_debut)) {
            throw new Exception('La date de fin doit être postérieure à la date de début.');
        }
        
        $data['date_confirmee'] = date('Y-m-d H:i:s');
        $data['date_debut'] = $date_debut;
        $data['date_fin'] = $date_fin;
        
        // Génération du room_id
        $room_id = $this->generateRoomId($consultation);
        $data['room_id'] = $room_id;
        $data['room_url'] = base_url('Videocall?room=' . $room_id);
        
        // Envoi email de confirmation avec cPanel
        return $this->notifyPatientConfirmation($consultation, $room_id, $date_debut);
    }

    /**
     * Génération d'un ID de salle unique
     */
    private function generateRoomId($consultation)
    {
        do {
            $part1 = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);
            $part2 = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);
            $room_id = $part1 . '-' . $part2;
            
            $exists = $this->Model->readOne('consultations', ['room_id' => $room_id]);
        } while ($exists);
        
        return $room_id;
    }

    /**
     * Traitement du refus
     */
    private function processRefusal($consultation, &$data)
    {
        $motif_annulation = $this->input->post('motif_annulation');
        if (empty(trim($motif_annulation))) {
            throw new Exception('Le motif du refus est requis.');
        }
        
        $data['motif_annulation'] = trim($motif_annulation);
        
        // Envoi email de refus
        return $this->notifyPatientRefusal($consultation, $motif_annulation);
    }

    /**
     * Traitement de la terminaison
     */
    private function processTermination($consultation, &$data)
    {
        $data['date_fin'] = date('Y-m-d H:i:s');
        
        if (!empty($consultation['date_debut'])) {
            $debut = strtotime($consultation['date_debut']);
            $fin = time();
            $duree = round(($fin - $debut) / 60);
            $data['duree_minutes'] = max(1, $duree);
        }
    }

    /**
     * Gestion de la réponse succès
     */
    private function handleSuccessResponse($statut, $email_sent)
    {
        $messages = [
            'confirmee' => 'Consultation confirmée avec succès.',
            'refusee' => 'Consultation refusée.',
            'en_cours' => 'Consultation démarrée.',
            'terminee' => 'Consultation terminée.',
            'annulee' => 'Consultation annulée.',
            'en_attente' => 'Statut remis en attente.'
        ];
        
        $msg = $messages[$statut] ?? 'Statut mis à jour avec succès.';
        
        if ($statut == 'confirmee' && $email_sent) {
            $msg .= ' Un email de confirmation a été envoyé au patient.';
        } elseif ($statut == 'refusee' && $email_sent) {
            $msg .= ' Un email de notification a été envoyé au patient.';
        }
        
        $this->session->set_flashdata('success', $msg);
    }

    /**
     * Rejoindre la salle de vidéoconférence
     */
    public function joinMeet($room_id = null)
    {
        if (empty($room_id)) {
            show_404();
        }
        
        // Vérifier que la consultation existe et est active
        $consultation = $this->db->where('room_id', $room_id)
                                  ->where_in('statut', ['confirmee', 'en_cours'])
                                  ->get('consultations')
                                  ->row_array();
        
        if (!$consultation) {
            $this->session->set_flashdata('error', 'Cette consultation n\'est pas disponible ou a expiré.');
            redirect(base_url('Consultations/Entente'));
            return;
        }
        
        // Vérifier les autorisations
        $user_id = $this->session->userdata('user_id');
        $user_type = $this->session->userdata('type_utilisateur');

        $doctor = $this->db->where('user_id', $user_id)
                              ->get('medecins')
                              ->row_array();
       
        $is_authorized = false;
        
        if ($user_type === 'medecin' && $consultation['medecin_id'] == $doctor['id']) {
            $is_authorized = true;
        } 
        elseif ($user_type === 'patient' && $consultation['patient_id'] == $user_id) {
            $is_authorized = true;
        }
        
        if (!$is_authorized) {
            $this->session->set_flashdata('error', 'Vous n\'êtes pas autorisé à rejoindre cette consultation.');
            redirect(base_url('Consultations/Entente/confirme'));
            return;
        }
        
        // Si médecin et consultation confirmée -> démarrer la consultation
        if ($consultation['statut'] === 'confirmee' && $user_type === 'medecin') {
            $this->db->where('id', $consultation['id'])
                     ->update('consultations', [
                         'statut' => 'en_cours',
                         'date_debut' => date('Y-m-d H:i:s')
                     ]);
        }
        
        // Redirection vers la salle vidéo
        redirect('VideoCall?room=' . $room_id);
    }

    /**
     * Récupérer les détails complets d'une consultation
     */
    private function getConsultationDetails($id)
    {
        return $this->db->select('
                c.*,
                p.nom as patient_nom,
                p.prenom as patient_prenom,
                p.email as patient_email,
                p.telephone as patient_telephone,
                u.nom as medecin_nom,
                u.prenom as medecin_prenom,
                m.specialite as medecin_specialite
            ')
            ->from('consultations c')
            ->join('users p', 'p.id = c.patient_id', 'left')
            ->join('medecins m', 'm.id = c.medecin_id', 'left')
            ->join('users u', 'u.id = m.user_id', 'left')
            ->where('c.id', $id)
            ->get()
            ->row_array();
    }

    /**
     * Récupérer l'email du patient
     */
    private function getPatientEmail($patient_id)
    {
        $this->db->select('email, nom, prenom');
        $this->db->from('users');
        $this->db->where('id', $patient_id);
        $this->db->where('is_active', 1);
        $patient = $this->db->get()->row_array();
        
        return $patient;
    }

    /**
     * Envoyer email de confirmation avec cPanel_email_lib
     */
    private function notifyPatientConfirmation($consultation, $room_id, $date_confirmee)
    {
        try {
            // Vérifier la librairie
            if (!isset($this->cpanel_email_lib) || !is_object($this->cpanel_email_lib)) {
                log_message('error', 'cpanel_email_lib non disponible pour l\'envoi de confirmation');
                return false;
            }
            
            $patient = $this->getPatientEmail($consultation['patient_id']);
            
            if (!$patient || empty($patient['email'])) {
                throw new Exception('Email patient non trouvé pour l\'ID: ' . $consultation['patient_id']);
            }
            
            $patient_email = $patient['email'];
            if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email patient invalide: ' . $patient_email);
            }
            
            $patient_nom = $consultation['patient_prenom'] . ' ' . $consultation['patient_nom'];
            $medecin_nom = $consultation['medecin_prenom'] . ' ' . $consultation['medecin_nom'];
            $date_formatee = date('d/m/Y à H:i', strtotime($date_confirmee));
            
            $join_url = base_url('Joinconsultation/index?room=' . $room_id . '&user=' . $consultation['patient_id']);
            
            // Récupérer les informations du site
            $site_name = $this->Model->get_setting('site_name', 'NUFOTEC');
            $site_logo = $this->Model->get_setting('site_logo');
            $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo) : '';
            
            $subject = 'Votre téléconsultation est confirmée - ' . $consultation['numero_consultation'];
            $message = $this->buildConfirmationEmailCpanel($patient_nom, $medecin_nom, $date_formatee, $join_url, $room_id, $consultation, $site_name, $logo_url);
            
            $result = $this->cpanel_email_lib->send_email($patient_email, $subject, $message);
            
            if (!$result['success']) {
                log_message('error', 'Échec envoi email confirmation cPanel: ' . json_encode($result));
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Erreur envoi email confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer email de refus avec cPanel_email_lib
     */
    private function notifyPatientRefusal($consultation, $motif)
    {
        try {
            if (!isset($this->cpanel_email_lib) || !is_object($this->cpanel_email_lib)) {
                log_message('error', 'cpanel_email_lib non disponible pour l\'envoi de refus');
                return false;
            }
            
            $patient = $this->getPatientEmail($consultation['patient_id']);
            
            if (!$patient || empty($patient['email'])) {
                throw new Exception('Email patient non trouvé pour l\'ID: ' . $consultation['patient_id']);
            }
            
            $patient_email = $patient['email'];
            if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email patient invalide: ' . $patient_email);
            }
            
            $patient_nom = $consultation['patient_prenom'] . ' ' . $consultation['patient_nom'];
            $site_name = $this->Model->get_setting('site_name', 'NUFOTEC');
            
            $subject = 'Demande de téléconsultation - ' . $consultation['numero_consultation'];
            $message = $this->buildRefusalEmailCpanel($patient_nom, $motif, $consultation, $site_name);
            
            $result = $this->cpanel_email_lib->send_email($patient_email, $subject, $message);
            
            if (!$result['success']) {
                log_message('error', 'Échec envoi email refus cPanel: ' . json_encode($result));
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Erreur envoi email refus: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Construction de l'email de confirmation version cPanel
     */
    private function buildConfirmationEmailCpanel($patient_nom, $medecin_nom, $date_formatee, $join_url, $room_id, $consultation, $site_name, $logo_url)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmation de téléconsultation</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                    background-color: #f4f6f9;
                    margin: 0;
                    padding: 20px;
                    line-height: 1.5;
                }
                .container {
                    max-width: 560px;
                    margin: 0 auto;
                    background: #ffffff;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
                }
                .header {
                    background: linear-gradient(135deg, #0a2540, #0f4c3a);
                    padding: 30px 24px;
                    text-align: center;
                }
                .header-logo { max-width: 100px; margin-bottom: 15px; }
                .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; }
                .content { padding: 28px; }
                .success-badge { background: #e8f5e9; color: #2e7d32; padding: 8px 16px; border-radius: 50px; display: inline-block; font-weight: 600; margin-bottom: 20px; }
                .info-box { background: #f7f9fc; border-radius: 12px; padding: 20px; margin: 20px 0; border: 1px solid #e8ecf0; }
                .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eef2f6; }
                .info-row:last-child { border-bottom: none; }
                .info-label { font-weight: 600; color: #1a2a3a; }
                .info-value { color: #5a6a7a; }
                .btn { display: inline-block; background: #0a66c2; color: white; padding: 12px 28px; text-decoration: none; border-radius: 40px; font-weight: 600; margin: 15px 0; }
                .room-code { background: #0a2540; color: #48bb78; font-family: monospace; padding: 15px; border-radius: 8px; text-align: center; font-size: 18px; letter-spacing: 2px; margin: 15px 0; }
                .tips-box { background: #fff8e1; border-left: 4px solid #ffc107; padding: 16px; margin: 20px 0; border-radius: 8px; }
                .footer { background: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #eef2f6; }
                .footer-text { font-size: 12px; color: #9aaab9; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    ' . (!empty($logo_url) ? '<img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="header-logo">' : '') . '
                    <h1>✓ Téléconsultation Confirmée</h1>
                </div>
                <div class="content">
                    <div style="text-align: center;"><span class="success-badge">RENDEZ-VOUS CONFIRMÉ</span></div>
                    <p>Bonjour <strong>' . htmlspecialchars($patient_nom) . '</strong>,</p>
                    <p>Votre téléconsultation avec le <strong>Dr ' . htmlspecialchars($medecin_nom) . '</strong> a été confirmée.</p>
                    <div class="info-box">
                        <div class="info-row"><span class="info-label">Date et heure</span><span class="info-value"><strong>' . $date_formatee . '</strong></span></div>
                        <div class="info-row"><span class="info-label">Médecin</span><span class="info-value">Dr ' . htmlspecialchars($medecin_nom) . '</span></div>
                        <div class="info-row"><span class="info-label">N° Consultation</span><span class="info-value">' . htmlspecialchars($consultation['numero_consultation']) . '</span></div>
                        <div class="info-row"><span class="info-label">Code salle</span><span class="info-value"><strong>' . $room_id . '</strong></span></div>
                    </div>
                    <div style="text-align: center;"><a href="' . $join_url . '" class="btn">🎥 REJOINDRE LA TÉLÉCONSULTATION</a></div>
                    <div class="room-code"><span style="color:#a0aec0;">Code à partager : </span><strong>' . $room_id . '</strong></div>
                    <div class="tips-box">
                        <strong>📋 Conseils pour une consultation réussie</strong>
                        <ul style="margin-top: 10px; padding-left: 20px;">
                            <li>Testez votre caméra et microphone avant le rendez-vous</li>
                            <li>Utilisez Chrome, Firefox ou Safari pour une meilleure expérience</li>
                            <li>Assurez-vous d\'avoir une connexion internet stable</li>
                            <li>Connectez-vous 5 minutes avant l\'heure prévue</li>
                        </ul>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-text">© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Tous droits réservés</div>
                    <div class="footer-text"><a href="' . base_url() . '" style="color:#9aaab9;">Visitez notre site</a></div>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Construction de l'email de refus version cPanel
     */
    private function buildRefusalEmailCpanel($patient_nom, $motif, $consultation, $site_name)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Demande de téléconsultation</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
                .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); }
                .header { background: linear-gradient(135deg, #c62828, #d32f2f); padding: 30px 24px; text-align: center; }
                .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; }
                .content { padding: 28px; }
                .motif-box { background: #f7f9fc; border-left: 4px solid #d32f2f; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .footer { background: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #eef2f6; }
                .footer-text { font-size: 12px; color: #9aaab9; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header"><h1>Demande de Téléconsultation</h1></div>
                <div class="content">
                    <p>Bonjour <strong>' . htmlspecialchars($patient_nom) . '</strong>,</p>
                    <p>Nous regrettons de vous informer que votre demande de téléconsultation <strong>N° ' . htmlspecialchars($consultation['numero_consultation']) . '</strong> n\'a pas pu être acceptée.</p>
                    <div class="motif-box"><strong>Motif du refus</strong><br>' . nl2br(htmlspecialchars($motif)) . '</div>
                    <p>Vous pouvez prendre un nouveau rendez-vous ou nous contacter pour plus d\'informations.</p>
                    <p>Cordialement,<br><strong>' . htmlspecialchars($site_name) . '</strong></p>
                </div>
                <div class="footer"><div class="footer-text">© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Tous droits réservés</div></div>
            </div>
        </body>
        </html>';
    }

    /**
     * API pour terminer la consultation
     */
    public function endConsultationApi($id = null)
    {
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }
        
        $consultation = $this->Model->readOne('consultations', ['id' => $id]);
        if (!$consultation) {
            echo json_encode(['success' => false, 'message' => 'Consultation non trouvée']);
            return;
        }
        
        $debut = strtotime($consultation['date_debut']);
        $fin = time();
        $duree_minutes = round(($fin - $debut) / 60);
        
        $this->Model->update('consultations', ['id' => $id], [
            'statut' => 'terminee',
            'date_fin' => date('Y-m-d H:i:s'),
            'duree_minutes' => $duree_minutes
        ]);
        
        echo json_encode(['success' => true, 'duration' => $duree_minutes]);
    }
}