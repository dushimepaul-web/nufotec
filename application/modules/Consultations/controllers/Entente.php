<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Entente extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
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

    /**
     * Liste des consultations confirmées
     */
    public function confirme()
{
    // Récupérer les consultations confirmées ou en cours
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
    
    // CORRECTION : Utiliser where_in au lieu de deux where()
    $this->db->where_in('c.statut', ['confirmee', 'en_cours']);
    
    $this->db->order_by('c.created_at', 'DESC');
    
    $data['consultations'] = $this->db->get()->result_array();
    
    // Récupérer les patients
    $this->db->where('type_utilisateur', 'patient');
    $this->db->where('is_active', 1);
    $data['patients'] = $this->db->get('users')->result_array();
    
    // Récupérer les médecins
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
     * Traitement de la confirmation - Génère room_id pour votre système vidéo
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
        
        // Génération du room_id pour VOTRE système de vidéoconférence
        $room_id = $this->generateRoomId($consultation);
        $data['room_id'] = $room_id;
        
        // URL de votre propre système de vidéoconférence
        $data['room_url'] = base_url('Videocall?room=' . $room_id);
        
        // Envoi email de confirmation
        return $this->notifyPatientConfirmation($consultation, $room_id, $date_debut);
    }

    /**
     * Génération d'un ID de salle unique (8 caractères format: XXXX-XXXX)
     */
    private function generateRoomId($consultation)
    {
        do {
            // Format: 4 caractères - 4 caractères (ex: AB12-CD34)
            $part1 = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);
            $part2 = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);
            $room_id = $part1 . '-' . $part2;
            
            // Vérifier unicité dans la base
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
        
        // Médecin : vérifier qu'il est le médecin assigné
        if ($user_type === 'medecin' && $consultation['medecin_id'] == $doctor['id']) {
            $is_authorized = true;
        } 
        // Patient : vérifier qu'il est le patient assigné
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
 * Envoyer email de confirmation avec SendGrid
 */
private function notifyPatientConfirmation($consultation, $room_id, $date_confirmee)
{
    try {
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
        
        // NOUVELLE URL : Joinconsultation avec paramètres room et user
        $join_url = base_url('Joinconsultation/index?room=' . $room_id . '&user=' . $consultation['patient_id']);
        
        $subject = 'Votre téléconsultation est confirmée - ' . $consultation['numero_consultation'];
        $message = $this->buildConfirmationEmail($patient_nom, $medecin_nom, $date_formatee, $join_url, $room_id, $consultation);
        
        // Charger et utiliser SendGrid
        $this->load->library('Sendgrid_lib');
        $result = $this->sendgrid_lib->send_email($patient_email, $subject, $message);
        
        $sent = ($result['status'] == 202 || $result['status'] == 200);
        
        if (!$sent) {
            log_message('error', 'Échec envoi email confirmation SendGrid: ' . json_encode($result));
        }
        
        return $sent;
        
    } catch (Exception $e) {
        log_message('error', 'Erreur envoi email confirmation: ' . $e->getMessage());
        return false;
    }
}

    /**
     * Envoyer email de refus avec SendGrid
     */
    private function notifyPatientRefusal($consultation, $motif)
    {
        try {
            $patient = $this->getPatientEmail($consultation['patient_id']);
            
            if (!$patient || empty($patient['email'])) {
                throw new Exception('Email patient non trouvé pour l\'ID: ' . $consultation['patient_id']);
            }
            
            $patient_email = $patient['email'];
            if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email patient invalide: ' . $patient_email);
            }
            
            $patient_nom = $consultation['patient_prenom'] . ' ' . $consultation['patient_nom'];
            
            $subject = 'Demande de téléconsultation - ' . $consultation['numero_consultation'];
            $message = $this->buildRefusalEmail($patient_nom, $motif, $consultation);
            
            // Charger et utiliser SendGrid
            $this->load->library('Sendgrid_lib');
            $result = $this->sendgrid_lib->send_email($patient_email, $subject, $message);
            
            $sent = ($result['status'] == 202 || $result['status'] == 200);
            
            if (!$sent) {
                log_message('error', 'Échec envoi email refus SendGrid: ' . json_encode($result));
            }
            
            return $sent;
            
        } catch (Exception $e) {
            log_message('error', 'Erreur envoi email refus: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Construction de l'email de confirmation
     */
    private function buildConfirmationEmail($patient_nom, $medecin_nom, $date_formatee, $join_url, $room_id, $consultation)
    {
        $site_name = $this->Model->get_setting('site_name', 'AGF');
        $site_logo = $this->Model->get_setting('site_logo', 'logo.png');
        $logo_url = base_url('attachments/Configurations/' . $site_logo);
        $site_phone = $this->Model->get_setting('site_phone', '+243 XXX XXX XXX');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmation de téléconsultation</title>
            <style>
                body {
                    font-family: 'Inter', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background: #f4f7fb;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .logo {
                    max-width: 120px;
                    margin-bottom: 15px;
                }
                .content {
                    padding: 30px;
                }
                .success-badge {
                    background: #e6f7e6;
                    color: #28a745;
                    padding: 10px 20px;
                    border-radius: 50px;
                    display: inline-block;
                    font-weight: 600;
                    margin-bottom: 20px;
                }
                .info-card {
                    background: #f8f9fa;
                    border-radius: 12px;
                    padding: 20px;
                    margin: 20px 0;
                    border-left: 4px solid #667eea;
                }
                .info-row {
                    display: flex;
                    margin-bottom: 10px;
                    padding: 8px 0;
                    border-bottom: 1px solid #dee2e6;
                }
                .info-label {
                    width: 120px;
                    font-weight: 600;
                    color: #555;
                }
                .info-value {
                    flex: 1;
                    color: #333;
                }
                .join-button {
                    display: inline-block;
                    padding: 15px 40px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 600;
                    margin: 20px 0;
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                }
                .room-code {
                    background: #2d3748;
                    color: #48bb78;
                    font-family: 'Courier New', monospace;
                    padding: 15px;
                    border-radius: 8px;
                    font-size: 18px;
                    letter-spacing: 2px;
                    margin: 15px 0;
                }
                .tips-box {
                    background: #fff3cd;
                    border: 1px solid #ffeeba;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    background: #f8f9fa;
                    color: #6c757d;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='{$logo_url}' alt='{$site_name}' class='logo'>
                    <h1 style='margin: 10px 0;'>Téléconsultation Confirmée</h1>
                </div>
                
                <div class='content'>
                    <div style='text-align: center;'>
                        <span class='success-badge'>✓ RENDEZ-VOUS CONFIRMÉ</span>
                    </div>
                    
                    <p>Bonjour <strong>" . htmlspecialchars($patient_nom) . "</strong>,</p>
                    
                    <p>Votre téléconsultation avec le <strong>Dr " . htmlspecialchars($medecin_nom) . "</strong> a été confirmée.</p>
                    
                    <div class='info-card'>
                        <h3 style='margin-top: 0; color: #667eea;'>Détails du rendez-vous</h3>
                        
                        <div class='info-row'>
                            <div class='info-label'>Date et heure</div>
                            <div class='info-value'><strong>" . $date_formatee . "</strong></div>
                        </div>
                        
                        <div class='info-row'>
                            <div class='info-label'>Médecin</div>
                            <div class='info-value'>Dr " . htmlspecialchars($medecin_nom) . "</div>
                        </div>
                        
                        <div class='info-row'>
                            <div class='info-label'>N° Consultation</div>
                            <div class='info-value'>" . htmlspecialchars($consultation['numero_consultation']) . "</div>
                        </div>
                        
                        <div class='info-row'>
                            <div class='info-label'>Code salle</div>
                            <div class='info-value'><strong>" . $room_id . "</strong></div>
                        </div>
                    </div>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . $join_url . "' class='join-button'>
                            🎥 REJOINDRE LA TÉLÉCONSULTATION
                        </a>
                        
                        <div class='room-code'>
                            <span style='color: #a0aec0;'>Code à partager : </span>
                            <strong>" . $room_id . "</strong>
                        </div>
                    </div>
                    
                    <div class='tips-box'>
                        <h4 style='margin-top: 0; color: #856404;'>Conseils pour une consultation réussie</h4>
                        <ul style='margin-bottom: 0; padding-left: 20px;'>
                            <li>Testez votre caméra et microphone avant le rendez-vous</li>
                            <li>Utilisez Chrome, Firefox ou Safari pour une meilleure expérience</li>
                            <li>Assurez-vous d'avoir une connexion internet stable</li>
                            <li>Connectez-vous 5 minutes avant l'heure prévue</li>
                            <li>Choisissez un endroit calme et bien éclairé</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 20px;'>
                        Si vous rencontrez des difficultés techniques, contactez-nous au 
                        <strong>" . $site_phone . "</strong>.
                    </p>
                </div>
                
                <div class='footer'>
                    <p>© " . date('Y') . " " . $site_name . " - Tous droits réservés</p>
                    <p>Ce message est automatique, merci de ne pas y répondre directement.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Construction de l'email de refus
     */
    private function buildRefusalEmail($patient_nom, $motif, $consultation)
    {
        $site_name = $this->Model->get_setting('site_name', 'AGF');
        $site_phone = $this->Model->get_setting('site_phone', '+243 XXX XXX XXX');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Demande de téléconsultation</title>
            <style>
                body {
                    font-family: 'Inter', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background: #f4f7fb;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .content {
                    padding: 30px;
                }
                .warning-box {
                    background: #fff3cd;
                    border: 1px solid #ffeeba;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .motif-box {
                    background: #f8f9fa;
                    border-left: 4px solid #e53e3e;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 0 8px 8px 0;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    background: #f8f9fa;
                    color: #6c757d;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin: 0;'>Demande de Téléconsultation</h1>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>" . htmlspecialchars($patient_nom) . "</strong>,</p>
                    
                    <p>Nous regrettons de vous informer que votre demande de téléconsultation 
                    <strong>N° " . htmlspecialchars($consultation['numero_consultation']) . "</strong> 
                    n'a pas pu être acceptée.</p>
                    
                    <div class='motif-box'>
                        <h3 style='margin-top: 0; color: #e53e3e;'>Motif du refus</h3>
                        <p style='margin-bottom: 0;'>" . nl2br(htmlspecialchars($motif)) . "</p>
                    </div>
                    
                    <div class='warning-box'>
                        <h4 style='margin-top: 0; color: #856404;'>Prochaines étapes</h4>
                        <p>Vous pouvez :</p>
                        <ul style='margin-bottom: 0;'>
                            <li>Prendre un nouveau rendez-vous à une autre date</li>
                            <li>Nous contacter par téléphone pour plus d'informations</li>
                            <li>Consulter un autre médecin disponible</li>
                        </ul>
                    </div>
                    
                    <p>Pour toute question, n'hésitez pas à nous contacter au <strong>" . $site_phone . "</strong>.</p>
                    
                    <p>Cordialement,<br><strong>" . $site_name . "</strong></p>
                </div>
                
                <div class='footer'>
                    <p>© " . date('Y') . " " . $site_name . " - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>";
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
        
        // Calculer durée
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