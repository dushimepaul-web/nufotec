<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author: Dushime Paul
 * Email: dushimeyesupaulin@gmail.com
 * Date: 20/01/2026
 */

class Contact extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->database();
        
        // Charger le modèle pour les settings si nécessaire
        if (!isset($this->Model)) {
            $this->load->model('Model');
        }
    }

    /**
     * Page de contact
     */
    public function index()
    {
        $data['csrf'] = [
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        ];
        $this->load->view('contact_us_view',$data);
    }

    /**
     * Traitement du formulaire de contact (AJAX/JSON)
     */
    public function sendMessage()
    {
        // Forcer le retour JSON
        header('Content-Type: application/json');
        
        // Vérifier la méthode HTTP
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Vérifier si c'est une requête AJAX
        $is_ajax = $this->input->is_ajax_request();
        
        // Récupérer les données (JSON ou POST standard)
        $input_data = $this->input->raw_input_stream;
        $json_data = json_decode($input_data, true);
        
        if ($json_data) {
            // Si données JSON, les fusionner avec POST
            $_POST = array_merge($_POST, $json_data);
        }

        // Vérification du token CSRF
        if ($this->config->item('csrf_protection')) {
            $csrf_token = $this->input->post($this->security->get_csrf_token_name());
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur de sécurité. Veuillez rafraîchir la page.',
                    'csrf_token' => $this->security->get_csrf_hash()
                ]);
                return;
            }
        }

        // Configuration des règles de validation
        $this->form_validation->set_rules('fullname', 'Nom complet', 'required|trim|min_length[2]|max_length[250]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[250]');
        $this->form_validation->set_rules('phone', 'Téléphone', 'required|trim|max_length[12]|regex_match[/^[0-9+\-\s]+$/]');
        $this->form_validation->set_rules('location', 'Localisation', 'trim|max_length[200]');
        $this->form_validation->set_rules('subject', 'Sujet', 'required|trim|min_length[3]|max_length[250]');
        $this->form_validation->set_rules('message', 'Message', 'required|trim|min_length[10]');
        $this->form_validation->set_rules('consent', 'Consentement', 'required');

        // Messages personnalisés
        $this->form_validation->set_message('required', 'Le champ {field} est obligatoire.');
        $this->form_validation->set_message('valid_email', 'Veuillez entrer une adresse email valide.');
        $this->form_validation->set_message('min_length', 'Le champ {field} doit contenir au moins {param} caractères.');
        $this->form_validation->set_message('max_length', 'Le champ {field} ne peut pas dépasser {param} caractères.');
        $this->form_validation->set_message('regex_match', 'Le format du téléphone est invalide.');

        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            
            // Formater les erreurs pour le frontend
            $formatted_errors = [];
            foreach ($errors as $field => $message) {
                $formatted_errors[$field] = $message;
            }

            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $formatted_errors,
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Nettoyer les données avec XSS filter
        $fullname = $this->security->xss_clean(trim($this->input->post('fullname')));
        $email = $this->security->xss_clean(trim($this->input->post('email')));
        $phone = $this->security->xss_clean(trim($this->input->post('phone')));
        $location = $this->security->xss_clean(trim($this->input->post('location')));
        $subject = $this->security->xss_clean(trim($this->input->post('subject')));
        $message = $this->security->xss_clean(trim($this->input->post('message')));

        // Vérifier les doublons récents (même email dans les dernières 24h)
        $this->db->where('Email', $email);
        $this->db->where('Date_creation >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $recent = $this->db->get('contact_us')->row();

        if ($recent) {
            http_response_code(429); // Too Many Requests
            echo json_encode([
                'success' => false,
                'message' => 'Vous avez déjà envoyé un message récemment. Nous y répondrons bientôt.',
                'type' => 'warning',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Préparer les données pour insertion
        $contact_data = [
            'FullName'      => $fullname,
            'Email'         => $email,
            'Subject'       => $subject,
            'Message'       => $message,
            'PhoneNumber'   => $phone,
            'Location'      => $location,
            'ip_address'    => $this->input->ip_address(),
            'user_agent'    => $this->input->user_agent(),
            'Date_creation' => date('Y-m-d H:i:s'),
            'is_readed'     => 0
        ];

        // Démarrer une transaction
        $this->db->trans_start();
        $inserted = $this->db->insert('contact_us', $contact_data);

        if (!$inserted) {
            $this->db->trans_rollback();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement en base de données.',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $insert_id = $this->db->insert_id();
        
        // Envoi des emails (ne bloque pas la réponse en cas d'échec)
        $email_sent = $this->_send_notification_emails($contact_data);
        
        if (!$email_sent) {
            log_message('error', 'Contact: Échec envoi email pour le message ID: ' . $insert_id);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur de transaction. Veuillez réessayer.',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Succès - Retourner les données pour le frontend
        echo json_encode([
            'success' => true,
            'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.',
            'data' => [
                'fullname' => $fullname,
                'email' => $email,
                'insert_id' => $insert_id
            ],
            'email_sent' => $email_sent,
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
    }

    /**
     * Envoyer les emails de notification
     */
    private function _send_notification_emails($data)
    {    
        try {
            $smtp_pass = $this->Model->get_setting('smtp_password', '');
            $smtp_email = $this->Model->get_setting('smtp_email', 'noreply@nufotec.com');
            $site_name = $this->Model->get_setting('site_name', 'Nufotec');

            // Configuration SMTP
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

            // 1. Email à l'administrateur
            $this->email->from($smtp_email, $site_name);
            $this->email->reply_to($data['Email'], $data['FullName']);
            $this->email->to($smtp_email);
            $this->email->subject("Nouveau message - " . $data['Subject']);
            
            $admin_message = "
                <h2>Nouveau message de contact</h2>
                <p><strong>De:</strong> {$data['FullName']} ({$data['Email']})</p>
                <p><strong>Téléphone:</strong> {$data['PhoneNumber']}</p>
                <p><strong>Sujet:</strong> {$data['Subject']}</p>
                <p><strong>Message:</strong></p>
                <blockquote style='background:#f5f5f5;padding:15px;border-left:4px solid #0B4F2E;'>
                    " . nl2br($data['Message']) . "
                </blockquote>
                <p><strong>IP:</strong> {$data['ip_address']}</p>
                <p><strong>Date:</strong> {$data['Date_creation']}</p>
            ";
            
            $this->email->message($admin_message);
            $admin_sent = $this->email->send();

            // Réinitialiser pour le prochain email
            $this->email->clear();

            // 2. Email de confirmation au visiteur
            $this->email->from($smtp_email, $site_name);
            $this->email->to($data['Email']);
            $this->email->subject('Confirmation - Votre message a bien été reçu');
            
            $user_message = "
                <div style='max-width:600px;margin:0 auto;font-family:Arial,sans-serif;color:#333;'>
                    <div style='background:linear-gradient(135deg, #0B4F2E, #1B7B4B);padding:30px;text-align:center;color:white;border-radius:16px 16px 0 0;'>
                        <h1 style='margin:0;font-size:24px;'>Merci de nous avoir contactés !</h1>
                    </div>
                    <div style='padding:30px;background:#f8faf9;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 16px 16px;'>
                        <p>Bonjour <strong>{$data['FullName']}</strong>,</p>
                        <p>Nous avons bien reçu votre message concernant : <em>{$data['Subject']}</em></p>
                        <p>Notre équipe vous répondra dans les plus brefs délais, généralement sous 24 à 48 heures ouvrées.</p>
                        
                        <div style='background:white;padding:20px;border-radius:12px;margin:20px 0;border-left:4px solid #0B4F2E;'>
                            <p style='margin:0;color:#666;'><strong>Récapitulatif de votre message :</strong></p>
                            <p style='margin:10px 0 0 0;color:#333;'>" . nl2br(substr($data['Message'], 0, 200)) . (strlen($data['Message']) > 200 ? '...' : '') . "</p>
                        </div>
                        
                        <p style='color:#666;font-size:14px;'>Si vous n'avez pas envoyé ce message, veuillez ignorer cet email.</p>
                        
                        <hr style='border:none;border-top:1px solid #e2e8f0;margin:30px 0;'>
                        <p style='margin:0;color:#666;font-size:14px;'>
                            Cordialement,<br>
                            <strong style='color:#0B4F2E;'>L'équipe {$site_name}</strong>
                        </p>
                    </div>
                </div>
            ";
            
            $this->email->message($user_message);
            $user_sent = $this->email->send();

            return ($admin_sent && $user_sent);

        } catch (Exception $e) {
            log_message('error', 'Contact: Erreur envoi email - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rafraîchir le token CSRF (endpoint optionnel)
     */
    public function refreshCsrf()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'csrf_name' => $this->security->get_csrf_token_name(),
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
    }
}