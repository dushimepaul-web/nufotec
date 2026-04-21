<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Login');
        $this->load->helper('url');
    }

    public function index() {
        $this->load->view('login');
    }

    public function login() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect($this->current_lang . '/Auth');
        }

        $email = trim($this->input->post('email'));
        $password = $this->input->post('password');
        $remember = $this->input->post('remember') ? true : false;

        $errors = [];
        if (empty($email)) $errors[] = 'L\'email est requis.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        if (empty($password)) $errors[] = 'Le mot de passe est requis.';

        if (!empty($errors)) {
            $this->session->set_flashdata('login_error', implode('<br>', $errors));
            redirect($this->current_lang . '/Auth');
        }

        $result = $this->Login->verify_login($email, $password);
        if (!$result['success']) {
            $this->session->set_flashdata('login_error', $result['message']);
            redirect($this->current_lang . '/Auth');
        }

        $user = $result['user'];
        $this->session->set_userdata([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'fullname' => $user['fullname'],
            'type_utilisateur' => $user['type_utilisateur'] ?? 'patient',
            'logged_in' => TRUE
        ]);

        if ($remember) set_cookie('remember_email', $email, 86400 * 30);

        $redirect = $this->session->userdata('login_redirect');
        $this->session->unset_userdata('login_redirect');
        redirect($redirect ?: $this->current_lang);
    }

    public function register() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect($this->current_lang . '/Auth?register=1');
        }

        $fullname = trim($this->input->post('fullname'));
        $email = trim($this->input->post('email'));
        $phone = trim($this->input->post('phone'));
        $password = $this->input->post('password');
        $confirm = $this->input->post('confirm_password');
        $terms = $this->input->post('terms');

        $errors = [];
        if (strlen($fullname) < 2) $errors[] = 'Nom complet (≥2 caractères).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        elseif ($this->Login->email_exists($email)) $errors[] = 'Email déjà utilisé.';
        if (!preg_match('/^\+\d{8,15}$/', $phone)) $errors[] = 'Téléphone au format +257XXXXXXXXX.';
        elseif ($this->Login->phone_exists($phone)) $errors[] = 'Téléphone déjà utilisé.';
        if (strlen($password) < 8) $errors[] = 'Mot de passe (≥8 caractères).';
        elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password))
            $errors[] = 'Mot de passe : une majuscule et un chiffre.';
        if ($password !== $confirm) $errors[] = 'Confirmation différente.';
        if (!$terms) $errors[] = 'Acceptez les conditions.';

        if (!empty($errors)) {
            $this->session->set_flashdata('register_error', implode('<br>', $errors));
            redirect($this->current_lang . '/Auth?register=1');
        }

        $name_parts = explode(' ', $fullname, 2);
        $user_data = [
            'nom' => $name_parts[0] ?? '',
            'prenom' => $name_parts[1] ?? '',
            'email' => $email,
            'telephone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'type_utilisateur' => 'patient',
            'is_active' => 1
        ];
        $result = $this->Login->create_user($user_data);

        if ($result['success']) {
            $this->session->set_flashdata('register_success', 'Compte créé ! Connectez-vous.');
            redirect($this->current_lang . '/Auth');
        } else {
            $this->session->set_flashdata('register_error', $result['message']);
            redirect($this->current_lang . '/Auth?register=1');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        delete_cookie('remember_email');
        redirect($this->current_lang);
    }
}