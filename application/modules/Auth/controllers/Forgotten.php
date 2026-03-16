<?php
// controllers/Auth.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Login');
        $this->load->library('email');
        $this->load->helper('url');
    }

    // ==================== PAGE MOT DE PASSE OUBLIÉ (Étape 1) ====================
    public function forgot_password() {
        $data['title'] = 'Mot de passe oublié';
        $this->load->view('forgot_password', $data);
    }

    // ==================== TRAITEMENT DEMANDE RÉINITIALISATION ====================
    public function request_reset() {
        header('Content-Type: application/json');
        
        $email = $this->input->post('email', true);
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez entrer un email valide']);
            return;
        }

        // Vérifier si l'email existe
        $patient = $this->Login->get_by_email($email);
        
        if (!$patient) {
            // Pour la sécurité, ne pas révéler si l'email existe ou non
            echo json_encode(['success' => true, 'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé']);
            return;
        }

        // Générer token unique
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Sauvegarder le token
        $this->Login->save_reset_token($patient['id'], $token, $expires);

        // Envoyer l'email
        $reset_link = base_url("Auth/reset_password/{$token}");
        $email_sent = $this->send_reset_email($email, $patient['fullname'], $reset_link);

        if ($email_sent) {
            echo json_encode(['success' => true, 'message' => 'Un email de réinitialisation a été envoyé à votre adresse']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email. Réessayez.']);
        }
    }

    // ==================== PAGE RÉINITIALISATION MOT DE PASSE (Étape 2) ====================
    public function reset_password($token = null) {
        if (empty($token)) {
            show_404();
        }

        // Vérifier le token
        $reset_data = $this->Login->verify_reset_token($token);
        
        if (!$reset_data || strtotime($reset_data['expires']) < time()) {
            $data['error'] = 'Ce lien de réinitialisation a expiré ou est invalide. Veuillez faire une nouvelle demande.';
            $this->load->view('reset_password_invalid', $data);
            return;
        }

        $data['title'] = 'Réinitialiser le mot de passe';
        $data['token'] = $token;
        $data['email'] = $reset_data['email'];
        $this->load->view('reset_password_form', $data);
    }

    // ==================== TRAITEMENT NOUVEAU MOT DE PASSE ====================
    public function update_password() {
        header('Content-Type: application/json');
        
        $token = $this->input->post('token', true);
        $password = $this->input->post('password', true);
        $confirm_password = $this->input->post('confirm_password', true);

        // Validation
        if (empty($token) || empty($password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
            return;
        }

        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
            return;
        }

        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
            return;
        }

        // Vérifier token encore valide
        $reset_data = $this->Login->verify_reset_token($token);
        
        if (!$reset_data || strtotime($reset_data['expires']) < time()) {
            echo json_encode(['success' => false, 'message' => 'Lien expiré. Veuillez refaire une demande.']);
            return;
        }

        // Mettre à jour le mot de passe
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $updated = $this->Login->update_password($reset_data['patient_id'], $hashed_password);

        if ($updated) {
            // Supprimer le token utilisé
            $this->Login->delete_reset_token($token);
            
            echo json_encode(['success' => true, 'message' => 'Mot de passe réinitialisé avec succès !']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour. Réessayez.']);
        }
    }

    // ==================== PAGE CONFIRMATION SUCCÈS ====================
    public function reset_success() {
        $data['title'] = 'Mot de passe réinitialisé';
        $this->load->view('reset_password_success', $data);
    }

    // ==================== ENVOI EMAIL ====================
    private function send_reset_email($to, $name, $reset_link) {
        $this->email->from('noreply@agf-phytomed.com', 'AGF PHYTOMED');
        $this->email->to($to);
        $this->email->subject('Réinitialisation de votre mot de passe');
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0f4c3a; padding: 30px; text-align: center; color: #d4af37; }
                .content { padding: 30px; background: #f8f9fa; }
                .btn { display: inline-block; padding: 15px 30px; background: #d4af37; color: #0f4c3a; 
                       text-decoration: none; border-radius: 50px; font-weight: bold; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; color: #6c757d; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>AGF PHYTOMED</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour {$name},</h2>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                    <center><a href='{$reset_link}' class='btn'>Réinitialiser mon mot de passe</a></center>
                    <p>Ce lien expirera dans 1 heure.</p>
                    <p>Si vous n'avez pas fait cette demande, ignorez simplement cet email.</p>
                </div>
                <div class='footer'>
                    <p>© 2024 AGF PHYTOMED Industries - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>";
        
        $this->email->message($message);
        
        return $this->email->send();
    }
}