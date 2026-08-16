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
        $role_slug = $user['role_id'] == 1 ? 'admin' : ($user['role_id'] == 2 ? 'medecin' : 'user');
        $this->session->set_userdata([
            'user_id' => $user['id'],
            'uuid' => $user['uuid'],
            'email' => $user['email'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'fullname' => trim($user['prenom'] . ' ' . $user['nom']),
            'type_utilisateur' => $user['type_utilisateur'] ?? 'patient',
            'role_id' => $user['role_id'],
            'role_slug' => $role_slug,
            'is_active' => $user['is_active'],
            'logged_in' => TRUE
        ]);

        $this->Login->update_last_login($user['id'], $this->input->ip_address());

        if ($remember) set_cookie('remember_email', $login, 86400 * 30);

        $redirect = $this->session->userdata('login_redirect');
        $this->session->unset_userdata('login_redirect');

        // Tout utilisateur connecté → routeur Dashboard (admin/medecin ou espace unifié)
        redirect($redirect ?: 'Dashboard');
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
        'is_active' => 1, // Compte actif directement (pas de vérification par code OTP)
        'email_verification_token' => $email_verification_token,
        'nom_entreprise' => ($type_utilisateur === 'entreprise') ? $nom_entreprise : null,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $result = $this->Login->create_user($user_data);

    if ($result['success']) {
        $this->session->set_flashdata('register_success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
        redirect('Auth');
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
    // MOT DE PASSE OUBLIÉ - VÉRIFICATION D'IDENTITÉ (SANS OTP)
    // ============================================

    public function verify_identity() {
        $this->output->set_content_type('application/json');

        $email = trim($this->input->post('email'));
        $telephone = trim($this->input->post('telephone'));
        $nom = trim($this->input->post('nom'));
        $prenom = trim($this->input->post('prenom'));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide.']);
            return;
        }

        if (empty($telephone)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez saisir votre numéro de téléphone.']);
            return;
        }

        if (empty($nom) || empty($prenom)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez saisir votre nom et votre prénom.']);
            return;
        }

        $user = $this->Login->verify_user_identity($email, $telephone, $nom, $prenom);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Les informations fournies ne correspondent à aucun compte. Vérifiez votre email, téléphone, nom et prénom.']);
            return;
        }

        $this->session->set_tempdata('reset_user_id', $user['id'], 900);

        echo json_encode(['success' => true, 'message' => 'Identité vérifiée. Vous pouvez maintenant définir un nouveau mot de passe.']);
    }

    // ============================================
    // RÉINITIALISATION DU MOT DE PASSE
    // ============================================

    public function reset_password() {
        $this->output->set_content_type('application/json');

        $user_id = $this->session->tempdata('reset_user_id');

        if (!$user_id) {
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
            $this->session->unset_tempdata('reset_user_id');
            $this->session->unset_tempdata('reset_email');
            $this->session->unset_tempdata('code_validated');
            
            echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès. Veuillez vous connecter.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du mot de passe.']);
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




}