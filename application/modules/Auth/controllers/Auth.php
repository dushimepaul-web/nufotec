<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Login');
        $this->load->library('cpanel_email_lib'); // Remplacé sendgrid_lib par cpanel_email_lib
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    public function index() {
        // Charger la liste des pays pour le formulaire d'inscription
        $data['pays_list'] = $this->db->select('id, pays, ITU_T_Telephone_Code')
                                        ->order_by('pays', 'ASC')
                                        ->get('pays')
                                        ->result_array();
        $this->load->view('connexion_inscription', $data);
    }

    public function login() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('Auth');
        }

        $login = trim($this->input->post('email'));
        $password = $this->input->post('password');
        $remember = $this->input->post('remember') ? true : false;

        $errors = [];
        if (empty($login)) $errors[] = 'L\'identifiant est requis.';
        if (empty($password)) $errors[] = 'Le mot de passe est requis.';

        if (!empty($errors)) {
            $this->session->set_flashdata('login_error', implode('<br>', $errors));
            redirect('Auth');
        }

        $result = $this->Login->verify_login($login, $password);
        
        if (!$result['success']) {
            $this->session->set_flashdata('login_error', $result['message']);
            redirect('Auth');
        }

        $user = $result['user'];
        $this->session->set_userdata([
            'user_id' => $user['id'],
            'uuid' => $user['uuid'],
            'email' => $user['email'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'fullname' => trim($user['prenom'] . ' ' . $user['nom']),
            'type_utilisateur' => $user['type_utilisateur'] ?? 'patient',
            'role_id' => $user['role_id'],
            'is_active' => $user['is_active'],
            'logged_in' => TRUE
        ]);

        $this->Login->update_last_login($user['id'], $this->input->ip_address());

        if ($remember) set_cookie('remember_email', $login, 86400 * 30);

        $redirect = $this->session->userdata('login_redirect');
        $this->session->unset_userdata('login_redirect');
        redirect($redirect ?: 'home-patient');
    }





    public function register() {
    if ($this->input->server('REQUEST_METHOD') !== 'POST') {
        redirect('Auth?register=1');
    }

    $nom = trim($this->input->post('nom'));
    $prenom = trim($this->input->post('prenom'));
    $email = trim($this->input->post('email'));
    $telephone = trim($this->input->post('telephone'));
    $password = $this->input->post('password');
    $confirm = $this->input->post('confirm_password');
    $terms = $this->input->post('terms');
    $type_utilisateur = $this->input->post('type_utilisateur') ?? 'patient';
    $nom_entreprise = $this->input->post('nom_entreprise');
        // ⭐ AJOUTER CETTE LIGNE - Récupérer l'ID du pays
    $pays_id = $this->input->post('pays_id') ?: null;

    $errors = [];

    if (strlen($nom) < 2) $errors[] = 'Nom (≥2 caractères)';
    if (strlen($prenom) < 2) $errors[] = 'Prénom (≥2 caractères)';

    // Vérification EMAIL
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    } elseif ($this->Login->email_exists($email)) {
        $this->session->set_flashdata('register_error', 'Cet email est déjà utilisé. Veuillez vous connecter ou utiliser un autre email.');
        redirect('Auth?register=1');
        return;
    }

    // Vérification TÉLÉPHONE
    if (!empty($telephone)) {
        $clean_phone = preg_replace('/[^0-9]/', '', $telephone);
        if (strlen($clean_phone) < 8 || strlen($clean_phone) > 15) {
            $errors[] = 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.';
        } elseif ($this->Login->phone_exists($telephone)) {
            $errors[] = 'Téléphone déjà utilisé.';
        }
    }

    if (strlen($password) < 8) {
        $errors[] = 'Mot de passe (≥8 caractères).';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Mot de passe : une majuscule et un chiffre.';
    }
    
    if ($password !== $confirm) $errors[] = 'Confirmation différente.';
    if (!$terms) $errors[] = 'Acceptez les conditions.';
    if ($type_utilisateur === 'entreprise' && empty($nom_entreprise)) {
        $errors[] = 'Nom de l\'entreprise requis.';
    }

    if (!empty($errors)) {
        $this->session->set_flashdata('register_error', implode('<br>', $errors));
        redirect('Auth?register=1');
    }

    // Générer un token de vérification d'email
    $email_verification_token = bin2hex(random_bytes(32));
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $user_data = [
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'telephone' => !empty($telephone) ? $telephone : null,
        'pays_id' => $pays_id,  // ⭐ AJOUTER CETTE LIGNE
        'password' => $hashed_password,
        'type_utilisateur' => $type_utilisateur,
        'role_id' => $this->get_role_id_by_type($type_utilisateur),
        'is_active' => 0, // Compte inactif jusqu'à vérification email
        'email_verification_token' => $email_verification_token,
        'nom_entreprise' => ($type_utilisateur === 'entreprise') ? $nom_entreprise : null,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $result = $this->Login->create_user($user_data);

    if ($result['success']) {
        // Envoyer le code de vérification par email
        $otp_code = sprintf("%06d", mt_rand(1, 999999));
        $expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Sauvegarder le code OTP
        $this->db->where('user_id', $result['user_id'])->where('type_otp', 'verification_email')->delete('codes_otp');
        $this->db->insert('codes_otp', [
            'user_id' => $result['user_id'],
            'code' => $otp_code,
            'type_otp' => 'verification_email',
            'email' => $email,
            'tentatives' => 0,
            'date_expiration' => $expiration,
            'utilise' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Envoyer l'email de vérification
        $user_name = trim($prenom . ' ' . $nom);
        $email_sent = $this->cpanel_email_lib->send_verification_code($email, $user_name, $otp_code);
        
        if ($email_sent['success']) {
            // Stocker l'email en session pour la vérification
            $this->session->set_tempdata('verification_email', $email, 900);
            $this->session->set_tempdata('verification_user_id', $result['user_id'], 900);
            
            // Rediriger vers la page de vérification
            redirect('auth/verify_email_page');
        } else {
            $this->session->set_flashdata('register_error', 'Compte créé mais erreur lors de l\'envoi de l\'email de vérification. Veuillez contacter l\'administrateur sur nufotecburundi2026@gmail.com.');
            redirect('Auth?register=1');
        }
    } else {
        $this->session->set_flashdata('register_error', $result['message']);
        redirect('Auth?register=1');
    }
}




    public function logout() {
        $this->session->sess_destroy();
        delete_cookie('remember_email');
        redirect('');
    }

    // ============================================
    // MOT DE PASSE OUBLIÉ - ENVOI DU CODE OTP
    // ============================================

    public function send_reset_code() {
        $this->output->set_content_type('application/json');
        $email = trim($this->input->post('email'));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide.']);
            return;
        }

        $user = $this->Login->get_by_email($email);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Aucun compte trouvé avec cet email.']);
            return;
        }
        
        $otp_code = sprintf("%06d", mt_rand(1, 999999));
        $expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Supprimer les anciens codes
        $this->db->where('user_id', $user['id']);
        $this->db->where('type_otp', 'reinitialisation_mdp');
        $this->db->delete('codes_otp');
        
        $otp_data = [
            'user_id' => $user['id'],
            'code' => $otp_code,
            'type_otp' => 'reinitialisation_mdp',
            'email' => $email,
            'tentatives' => 0,
            'date_expiration' => $expiration,
            'utilise' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('codes_otp', $otp_data);
        
        // Utiliser la librairie cPanel
        $user_name = trim($user['prenom'] . ' ' . $user['nom']);
        $result = $this->cpanel_email_lib->send_otp_code($email, $user_name, $otp_code, 'reset');
        
        if ($result['success']) {
            $this->session->set_tempdata('reset_email', $email, 900);
            echo json_encode(['success' => true, 'message' => 'Code envoyé avec succès à votre adresse email.', 'code' => $otp_code]);
        } else {
            // Mode développement - Afficher le code en cas d'erreur
            $this->session->set_tempdata('reset_email', $email, 900);
            echo json_encode([
                'success' => true, 
                'message' => 'Code: ' . $otp_code . ' (Vérifiez votre configuration email)',
                'code' => $otp_code,
                'dev_mode' => true
            ]);
        }
    }

    // ============================================
    // VÉRIFICATION DU CODE OTP
    // ============================================

    public function verify_reset_code() {
        $this->output->set_content_type('application/json');
        $email = $this->session->tempdata('reset_email');
        $code = trim($this->input->post('code'));
        
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez recommencer.']);
            return;
        }
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez entrer le code reçu par email.']);
            return;
        }
        
        $user = $this->Login->get_by_email($email);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            return;
        }
        
        $otp = $this->db->where('user_id', $user['id'])
                        ->where('code', $code)
                        ->where('type_otp', 'reinitialisation_mdp')
                        ->where('utilise', 0)
                        ->where('date_expiration >', date('Y-m-d H:i:s'))
                        ->get('codes_otp')
                        ->row();
        
        if (!$otp) {
            // Incrémenter les tentatives
            $this->db->where('user_id', $user['id'])
                     ->where('type_otp', 'reinitialisation_mdp')
                     ->set('tentatives', 'tentatives+1', FALSE)
                     ->update('codes_otp');
            
            echo json_encode(['success' => false, 'message' => 'Code invalide ou expiré.']);
            return;
        }
        
        $this->db->where('id', $otp->id)->update('codes_otp', ['utilise' => 1]);
        
        $this->session->set_tempdata('code_validated', true, 900);
        $this->session->set_tempdata('reset_user_id', $user['id'], 900);
        
        echo json_encode(['success' => true, 'message' => 'Code valide. Vous pouvez maintenant changer votre mot de passe.', 'action' => 'set_password']);
    }

    // ============================================
    // RÉINITIALISATION DU MOT DE PASSE
    // ============================================

    public function reset_password() {
        $this->output->set_content_type('application/json');
        
        $email = $this->session->tempdata('reset_email');
        $code_validated = $this->session->tempdata('code_validated');
        $user_id = $this->session->tempdata('reset_user_id');
        
        if (!$email || !$code_validated || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez recommencer la procédure.']);
            return;
        }
        
        $new_password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
        
        if (empty($new_password) || strlen($new_password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
            return;
        }
        
        if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins une majuscule et un chiffre.']);
            return;
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
            return;
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updated = $this->Login->update_password($user_id, $hashed_password);
        
        if ($updated) {
            $this->session->unset_tempdata('reset_email');
            $this->session->unset_tempdata('code_validated');
            $this->session->unset_tempdata('reset_user_id');
            
            $this->db->where('user_id', $user_id)->where('type_otp', 'reinitialisation_mdp')->delete('codes_otp');
            
            echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès. Veuillez vous connecter.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du mot de passe.']);
        }
    }

    // ============================================
    // RENVOYER UN NOUVEAU CODE OTP
    // ============================================

    public function resend_otp() {
        $this->output->set_content_type('application/json');
        $email = $this->session->tempdata('reset_email');
        
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez recommencer.']);
            return;
        }
        
        $user = $this->Login->get_by_email($email);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            return;
        }
        
        $otp_code = sprintf("%06d", mt_rand(1, 999999));
        $expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $this->db->where('user_id', $user['id'])->where('type_otp', 'reinitialisation_mdp')->delete('codes_otp');
        
        $otp_data = [
            'user_id' => $user['id'],
            'code' => $otp_code,
            'type_otp' => 'reinitialisation_mdp',
            'email' => $email,
            'tentatives' => 0,
            'date_expiration' => $expiration,
            'utilise' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('codes_otp', $otp_data);
        
        // Utiliser la librairie cPanel
        $user_name = trim($user['prenom'] . ' ' . $user['nom']);
        $result = $this->cpanel_email_lib->send_otp_code($email, $user_name, $otp_code, 'reset');
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Un nouveau code a été envoyé à votre adresse email.']);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Code: ' . $otp_code,
                'code' => $otp_code,
                'dev_mode' => true
            ]);
        }
    }

    // ============================================
    // TEST D'ENVOI D'EMAIL
    // ============================================

    public function test_email() {
        $email = $this->input->get('email') ?: 'dushimepaul51@gmail.com';
        $result = $this->cpanel_email_lib->test_email($email);
        
        if ($result['success']) {
            echo "✅ Email envoyé avec succès à " . $email . "!";
        } else {
            echo "❌ Erreur: " . $result['message'];
        }
    }

    private function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function get_role_id_by_type($type) {
        $roles = [
            'admin' => 1,
            'medecin' => 2,
            'patient' => 8,
            'entreprise' => 8,
            'investisseur' => 8,
            'partenaire' => 8,
            'broker' => 8
        ];
        return $roles[$type] ?? 8;
    }

   // ============================================
// EFFACER LES MESSAGES FLASH (AJAX)
// ============================================

public function clear_flash() {
    $this->output->set_content_type('application/json');
    $key = $this->input->post('key');
    
    if ($key) {
        // Supprimer le flashdata
        $this->session->unset_userdata($key);
        $this->session->set_flashdata($key, null);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No key provided']);
    }
}

public function clear_flash_data() {
    $this->output->set_content_type('application/json');
    $keys = $this->input->post('keys');
    
    if ($keys && is_array($keys)) {
        foreach ($keys as $key) {
            $this->session->unset_userdata($key);
            $this->session->set_flashdata($key, null);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}




// Page de vérification d'email
public function verify_email_page() {
    $email = $this->session->tempdata('verification_email');
    
    if (!$email) {
        redirect('Auth');
    }
    
    $data['email'] = $email;
    $this->load->view('verify_email_view', $data);
}



public function verify_email_code() {
    $this->output->set_content_type('application/json');
    
    $email = $this->session->tempdata('verification_email');
    $user_id = $this->session->tempdata('verification_user_id');
    $code = trim($this->input->post('code'));
    
    if (!$email || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez recommencer l\'inscription.']);
        return;
    }
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez entrer le code de vérification.']);
        return;
    }
    
    // Utiliser la méthode du modèle
    $otp = $this->Login->verify_email_otp($user_id, $code);
    
    if (!$otp) {
        echo json_encode(['success' => false, 'message' => 'Code invalide ou expiré.']);
        return;
    }
    
    // Marquer le code comme utilisé
    $this->Login->mark_otp_as_used($otp->id);
    
    // Activer le compte utilisateur
    $this->db->where('id', $user_id)->update('users', [
        'is_active' => 1,
        'email_verified_at' => date('Y-m-d H:i:s'),
        'email_verification_token' => null
    ]);
    
    // Nettoyer la session
    $this->session->unset_tempdata('verification_email');
    $this->session->unset_tempdata('verification_user_id');
    
    echo json_encode(['success' => true, 'message' => 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.']);
}



// Renvoyer le code de vérification
public function resend_verification_code() {
    $this->output->set_content_type('application/json');
    
    $email = $this->session->tempdata('verification_email');
    $user_id = $this->session->tempdata('verification_user_id');
    
    if (!$email || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez recommencer l\'inscription.']);
        return;
    }
    
    $user = $this->Login->get_user_by_id($user_id);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
        return;
    }
    
    // Générer nouveau code
    $otp_code = sprintf("%06d", mt_rand(1, 999999));
    $expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Supprimer ancien code
    $this->db->where('user_id', $user_id)->where('type_otp', 'verification_email')->delete('codes_otp');
    
    // Sauvegarder nouveau code
    $this->db->insert('codes_otp', [
        'user_id' => $user_id,
        'code' => $otp_code,
        'type_otp' => 'verification_email',
        'email' => $email,
        'tentatives' => 0,
        'date_expiration' => $expiration,
        'utilise' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Envoyer l'email
    $user_name = trim($user['prenom'] . ' ' . $user['nom']);
    $result = $this->cpanel_email_lib->send_verification_code($email, $user_name, $otp_code);
    
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'Un nouveau code a été envoyé à votre adresse email.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du code.']);
    }
}
}