<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Login');
        $this->load->model('Model'); // Pour récupérer médecin par UUID
        $this->load->model('user_session_model');
        $this->load->model('user_activity_model');
        $this->load->library(['email', 'form_validation', 'session']);
        $this->load->helper(['url', 'security', 'cookie']);
    }

    /**
     * Page de connexion
     * Gère les redirections et le stockage du médecin sélectionné
     */
    public function index() {
        // Si déjà connecté, rediriger vers la page appropriée
        if ($this->session->userdata('user_id')) {
            $redirect = $this->input->get('redirect', TRUE);
            if ($redirect && $this->_is_safe_redirect($redirect)) {
                redirect($redirect);
            } else {
                // Vérifier s'il y a un médecin en attente
                $pending_doctor = $this->session->userdata('pending_doctor');
                if ($pending_doctor && !empty($pending_doctor['uuid'])) {
                    redirect('Consultations/PatientForm?doctor_uuid=' . $pending_doctor['uuid']);
                } else {
                    redirect('Consultations/PatientForm');
                }
            }
            return;
        }

        // Traitement du POST (sélection d'un médecin)
        $doctor_uuid = $this->input->post('selected_doctor_uuid', TRUE);
        if ($doctor_uuid) {
            $medecin = $this->Model->getDoctorByUUID($doctor_uuid);
            if ($medecin) {
                $doctor_data = [
                    'uuid' => $medecin['uuid'],
                    'timestamp' => time(),
                    'expires_at' => time() + 1800 // 30 minutes
                ];
                $this->session->set_userdata('pending_doctor', $doctor_data);
            }
        } else {
            // Vérifier si un médecin est déjà en session et non expiré
            $doctor_data = $this->session->userdata('pending_doctor');
            if ($doctor_data && $doctor_data['expires_at'] < time()) {
                $this->session->unset_userdata('pending_doctor');
            }
        }

        // Récupérer la redirection depuis l'URL (GET)
        $redirect = $this->input->get('redirect', TRUE);
        if ($redirect && $this->_is_safe_redirect($redirect)) {
            $this->session->set_userdata('login_redirect', $redirect);
        }

        // Si aucune redirection explicite, utiliser le referer HTTP (si sûr)
        if (!$this->session->userdata('login_redirect') && isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            if ($this->_is_safe_redirect($referer)) {
                $this->session->set_userdata('login_redirect', $referer);
            }
        }

        // Passer les données à la vue
        $data['pending_doctor'] = $this->session->userdata('pending_doctor');
        $data['redirect'] = $this->session->userdata('login_redirect');
        $data['title'] = 'Connexion - AGF PHYTOMED';
        $this->load->view('login', $data);
    }

    // ==================== TRAITEMENT DE LA CONNEXION ====================
    public function login() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Validation via form_validation
        $this->form_validation->set_data($this->input->post());
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Mot de passe', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->json_response(false, validation_errors());
            return;
        }

        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);
        $remember = $this->input->post('remember') ? true : false;
        $redirect = $this->input->post('redirect', TRUE);

        $result = $this->Login->verify_login($email, $password);
        if (!$result['success']) {
            $this->json_response(false, $result['message']);
            return;
        }

        $user = $result['user'];

        // Données de session
        $session_data = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'photo' => $user['photo'] ?? 'default-avatar.png',
            'fullname' => $user['fullname'] ?? ($user['prenom'] . ' ' . $user['nom']),
            'type_utilisateur' => $user['type_utilisateur'] ?? 'patient',
            'logged_in' => TRUE
        ];
        $this->session->set_userdata($session_data);

        // Cookie "Se souvenir de moi" (sans sauvegarde en base)
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            set_cookie([
                'name' => 'remember_token',
                'value' => $token,
                'expire' => 86400 * 30,
                'secure' => TRUE,
                'httponly' => TRUE
            ]);
            // Ne pas sauvegarder en base pour l'instant
        }

        // --- Enregistrement de la session dans user_sessions ---
        $this->user_session_model->create_user_session($user['id'], session_id());

        // --- Journalisation de la connexion réussie ---
        $this->user_activity_model->log_action([
            'user_id'    => $user['id'],
            'action'     => 'login',
            'module'     => 'auth',
            'item_id'    => $user['id'],
            'item_name'  => $user['email'],
            'description' => 'Connexion réussie',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ]);

        // Déterminer l'URL de redirection
        $redirect_url = $this->_get_redirect_url($redirect);

        $this->json_response(true, 'Connexion réussie', [
            'redirect' => $redirect_url,
            'user' => [
                'fullname' => $session_data['fullname'],
                'email' => $session_data['email']
            ]
        ]);
    }

    /**
     * Retourne une URL de redirection valide et sécurisée
     */
    private function _get_redirect_url($redirect = null) {
        // 1. Priorité à la session (stockée avant redirection)
        $session_redirect = $this->session->userdata('login_redirect');
        if ($session_redirect) {
            $this->session->unset_userdata('login_redirect');
            if ($this->_is_safe_redirect($session_redirect)) {
                return $session_redirect;
            }
        }

        // 2. Redirection fournie en POST
        if (!empty($redirect) && $this->_is_safe_redirect($redirect)) {
            if (strpos($redirect, '://') === false && strpos($redirect, '//') !== 0) {
                return base_url($redirect);
            }
            return $redirect;
        }

        // 3. Redirection par défaut (avec médecin éventuel)
        $pending_doctor = $this->session->userdata('pending_doctor');
        $default = 'Consultations/PatientForm';
        if ($pending_doctor && !empty($pending_doctor['uuid'])) {
            $default .= '?doctor_uuid=' . $pending_doctor['uuid'];
        }
        return base_url($default);
    }

    /**
     * Vérifie si une URL de redirection est sûre (même domaine)
     */
    private function _is_safe_redirect($url) {
        if (empty($url)) return false;
        $base_url = base_url();
        if (strpos($url, $base_url) === 0) return true;
        if (strpos($url, '://') === false && strpos($url, '//') !== 0) {
            return true;
        }
        return false;
    }

    // ==================== API INSCRIPTION ====================
    public function register() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fullname = trim($this->input->post('fullname', TRUE));
        $email = strtolower($this->input->post('email', TRUE));
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');

        $errors = [];

        if (empty($fullname) || strlen($fullname) < 2) {
            $errors[] = 'Le nom complet doit contenir au moins 2 caractères';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Veuillez entrer une adresse email valide';
        }
        if (empty($password) || strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule et un chiffre';
        }
        if ($password !== $confirm_password) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }

        if (!empty($errors)) {
            $this->json_response(false, implode(', ', $errors));
            return;
        }

        $name_parts = explode(' ', $fullname, 2);
        $prenom = $name_parts[0];
        $nom = $name_parts[1] ?? '';

        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'password' => $password,
            'type_utilisateur' => 'patient',
            'date_creation' => date('Y-m-d H:i:s')
        ];

        $result = $this->Login->create_user($data);

        if (!$result['success']) {
            $this->json_response(false, $result['message']);
            return;
        }

        $this->json_response(true, 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.', [
            'redirect' => base_url('Auth'),
            'email' => $email
        ]);
    }

    // ==================== VÉRIFICATION EMAIL ====================
    public function check_email() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $email = $this->input->post('email', TRUE);
        if (empty($email)) {
            $this->json_response(false, 'Email requis');
            return;
        }

        $exists = $this->Login->email_exists($email);
        $this->json_response(true, '', [
            'exists' => $exists,
            'available' => !$exists
        ]);
    }

    // ==================== DÉCONNEXION ====================
    public function logout() {
        $this->session->sess_destroy();
        delete_cookie('remember_token');
        redirect('/');
    }

    // ==================== MOT DE PASSE OUBLIÉ ====================
    public function forgot_password() {
        if ($this->input->is_ajax_request()) {
            $email = $this->input->post('email', TRUE);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json_response(false, 'Veuillez entrer une adresse email valide');
                return;
            }

            $user = $this->Login->get_by_email($email);
            if (!$user) {
                $this->json_response(true, 'Si cette adresse existe, un email de réinitialisation a été envoyé.');
                return;
            }

            $token = bin2hex(random_bytes(32));
            $this->Login->set_reset_token($email, $token);
            // Envoyer l'email (à implémenter)

            $this->json_response(true, 'Si cette adresse existe, un email de réinitialisation a été envoyé.');
            return;
        }

        $data['title'] = 'Mot de passe oublié';
        $this->load->view('forgot_password', $data);
    }

    // ==================== UTILITAIRES ====================
    private function json_response($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(
            ['success' => $success, 'message' => $message],
            $data
        ));
        exit;
    }
}