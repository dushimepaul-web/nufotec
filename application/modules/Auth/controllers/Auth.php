<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Login');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    public function index() {
        $this->load->view('connexion_inscription');
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

        // Mise à jour last_login
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
        $type_utilisateur = $this->input->post('type_utilisateur');
        $nom_entreprise = $this->input->post('nom_entreprise');

        $errors = [];

        // Validation nom/prénom
        if (strlen($nom) < 2) $errors[] = 'Nom (≥2 caractères)';
        if (strlen($prenom) < 2) $errors[] = 'Prénom (≥2 caractères)';

        // Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        elseif ($this->Login->email_exists($email)) $errors[] = 'Email déjà utilisé.';

        // Téléphone format international
        if (!empty($telephone) && !preg_match('/^\+\d{8,15}$/', $telephone)) $errors[] = 'Téléphone au format +257XXXXXXXXX.';
        if (!empty($telephone) && $this->Login->phone_exists($telephone)) $errors[] = 'Téléphone déjà utilisé.';

        // Mot de passe
        if (strlen($password) < 8) $errors[] = 'Mot de passe (≥8 caractères).';
        elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password))
            $errors[] = 'Mot de passe : une majuscule et un chiffre.';
        if ($password !== $confirm) $errors[] = 'Confirmation différente.';

        if (!$terms) $errors[] = 'Acceptez les conditions.';

        // Validation spécifique entreprise
        if ($type_utilisateur === 'entreprise' && empty($nom_entreprise)) {
            $errors[] = 'Nom de l\'entreprise requis.';
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('register_error', implode('<br>', $errors));
            redirect('Auth?register=1');
        }

        // Hash du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $user_data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => !empty($telephone) ? $telephone : null,
            'password' => $hashed_password,
            'type_utilisateur' => $type_utilisateur,
            'role_id' => $this->get_role_id_by_type($type_utilisateur),
            'is_active' => 1,
            'nom_entreprise' => ($type_utilisateur === 'entreprise') ? $nom_entreprise : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->Login->create_user($user_data);

        if ($result['success']) {
            // Message de succès et redirection vers la page de connexion
            $this->session->set_flashdata('register_success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
            redirect('Auth');
        } else {
            $this->session->set_flashdata('register_error', $result['message']);
            redirect('Auth?register=1');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        delete_cookie('remember_email');
        redirect('Auth');
    }

    // AJAX : Envoi du code de réinitialisation
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

        // Génération code 6 chiffres
        $code = sprintf("%06d", mt_rand(1, 999999));
        // Stockage temporaire en session
        $this->session->set_tempdata('reset_code', $code, 600); // 10 minutes
        $this->session->set_tempdata('reset_email', $email, 600);

        // En production : envoyer un vrai email
        // Ici simulation
        log_message('info', "Code réinitialisation pour $email : $code");

        echo json_encode(['success' => true, 'message' => 'Code envoyé', 'code' => $code]);
    }

    // AJAX : Réinitialisation du mot de passe
    public function reset_password() {
        $this->output->set_content_type('application/json');
        $email = trim($this->input->post('email'));
        $new_password = $this->input->post('password');

        if (empty($email) || empty($new_password)) {
            echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
            return;
        }

        if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            echo json_encode(['success' => false, 'message' => 'Mot de passe : 8+ caractères, 1 majuscule, 1 chiffre.']);
            return;
        }

        $user = $this->Login->get_by_email($email);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
            return;
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $updated = $this->Login->update_password($user['id'], $hashed);

        if ($updated) {
            $this->session->unset_tempdata('reset_code');
            $this->session->unset_tempdata('reset_email');
            echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
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
}