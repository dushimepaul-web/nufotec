<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur Admin - Gestion de l'authentification
 * 
 * Ce contrôleur gère l'authentification des utilisateurs avec:
 * - Validation des entrées
 * - Protection CSRF
 * - Rate limiting
 * - Sessions sécurisées
 * - Journalisation des tentatives
 * - Double authentification (prête à l'emploi)
 * 
 * @package     AfricanGreenFarmers
 * @subpackage  Controllers
 * @category    Auth
 * @author      Dushime Paul
 * @version     2.0.0
 */
class Admin extends MY_Controller {

    /**
     * Nombre maximum de tentatives de connexion
     */
    const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Durée du blocage en minutes
     */
    const LOCKOUT_TIME = 15;

    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();

        $this->load->driver('cache', ['adapter' => 'file']);
        
        
        // Protection CSRF globale pour ce contrôleur
        if ($this->config->item('csrf_protection')) {
            $this->csrf_token = $this->security->get_csrf_hash();
        }
    }

    /**
     * Page de connexion
     * Affiche le formulaire de login
     */
    public function index() {

    // Vérifier si une session partielle existe (problème de session)
    $logged_in = $this->session->userdata('logged_in');
    $idUser = $this->session->userdata('idUser');
    
    // Si les deux existent, rediriger vers le dashboard
    if ($logged_in && $idUser) {
        redirect(base_url('Dashboard'));
    }
    
    // Si une donnée existe mais pas l'autre, nettoyer la session
    if ($logged_in || $idUser) {
        $this->session->sess_destroy();
    }
    
    $data = [
        'csrf_token' => $this->security->get_csrf_hash(),
        'csrf_name' => $this->security->get_csrf_token_name(),
        'site_name' => $this->Model->get_setting('site_name', 'AGF'),
        'site_logo' => $this->Model->get_setting('site_logo', 'logo.png')
    ];
    
    $this->load->view('Login_View', $data);
}
    /**
     * Traitement de la connexion
     * Sécurisé avec validation, rate limiting, et logs
     */
    public function do_login() {
        // Vérification CSRF
        if ($this->config->item('csrf_protection')) {
            if (!$this->check_csrf()) {
                $this->session->set_flashdata('sms', $this->get_alert('danger', 'Erreur de sécurité. Veuillez réessayer.'));
                redirect(base_url('Admin'));
                return;
            }
        }

        // Rate limiting - Vérifier les tentatives
        if (!$this->check_rate_limit()) {
            $this->session->set_flashdata('sms', $this->get_alert('warning', 
                'Trop de tentatives. Veuillez patienter ' . self::LOCKOUT_TIME . ' minutes.'
            ));
            redirect(base_url('Admin'));
            return;
        }

        // Validation des entrées
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|max_length[100]');
        $this->form_validation->set_rules('password', 'Mot de passe', 'required|min_length[6]|trim');
        
        if ($this->form_validation->run() == FALSE) {
            // Échec de validation
            $errors = validation_errors();
            $this->session->set_flashdata('sms', $this->get_alert('danger', $errors ?: 'Veuillez remplir tous les champs correctement.'));
            redirect(base_url('Admin'));
            return;
        }

        // Nettoyage des entrées
        $username = $this->security->xss_clean($this->input->post('email', TRUE));
        $password = $this->input->post('password'); // Ne pas nettoyer le mot de passe

        // Journaliser la tentative
        $this->log_attempt($username);

        // Vérifier si l'email existe
        if (!$this->Model->email_exists($username)) {
            $this->handle_failed_login($username, 'Email inexistant');
            return;
        }

        // Tentative de connexion
        $user = $this->Model->login($username, $password);

        if (!$user) {
            $this->handle_failed_login($username, 'Mot de passe incorrect');
            return;
        }

        // Vérifier si le compte est actif
        if (!isset($user['is_active']) || $user['is_active'] != 1) {
            $this->handle_failed_login($username, 'Compte désactivé');
            return;
        }

        // Vérifier si l'email est vérifié (si requis)
        if ($this->config->item('email_verification_required') && empty($user['email_verified_at'])) {
            $this->session->set_flashdata('sms', $this->get_alert('warning', 
                'Veuillez vérifier votre adresse email avant de vous connecter.'
            ));
            redirect(base_url('Admin'));
            return;
        }

        // Vérifier la double authentification (si activée)
        if ($this->check_two_factor_required($user)) {
            $this->handle_two_factor_auth($user);
            return;
        }

        // Connexion réussie
        $this->handle_successful_login($user);
    }

    /**
     * Gère une connexion réussie
     * 
     * @param array $user Données utilisateur
     */
    private function handle_successful_login($user) {
        // Récupérer le rôle de l'utilisateur
        $role = $this->Model->read_one('roles', ['id' => $user['role_id']]);
        $role_name = $role['nom'] ?? 'Utilisateur';
        $role_slug = $role['slug'] ?? 'utilisateur';

        // Créer la session utilisateur
        $session_data = [
            'idUser' => (int)$user['id'],
            'uuid' => $user['uuid'] ?? null,
            'email' => $user['email'],
            'nom' => $user['nom'] ?? '',
            'prenom' => $user['prenom'] ?? '',
            'username' => trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')),
            'photo' => $user['photo'] ?? 'default-avatar.png',
            'role_id' => (int)$user['role_id'],
            'role' => $role_name,
            'role_slug' => $role_slug,
            'logged_in' => TRUE,
            'login_time' => time(),
            'last_activity' => time(),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ];

        $this->session->set_userdata($session_data);

        // Créer la session dans la base de données
        $this->Model->create_user_session($user['id'], session_id());
        
        // Journaliser la connexion réussie
        $this->Model->log_login_attempt($user['email'], true, $user['id']);

        // Réinitialiser les tentatives échouées
        $this->reset_attempts($user['email']);

        // Message de succès
        $this->session->set_flashdata('success', 
            '<div class="alert alert-success text-center">
                <i class="bx bx-check-circle"></i> Connexion réussie ! Bienvenue ' . htmlspecialchars($session_data['username']) . '.
            </div>'
        );

        // Redirection selon le rôle
        redirect($this->get_redirect_url($role_slug));
    }

    /**
     * Gère une tentative de connexion échouée
     * 
     * @param string $username Email utilisé
     * @param string $reason Raison de l'échec
     */
    private function handle_failed_login($username, $reason = '') {
        // Incrémenter le compteur de tentatives
        $this->increment_attempts($username);
        
        // Journaliser l'échec
        $this->Model->log_login_attempt($username, false);

        // Message d'erreur
        $error_msg = $reason ? "Échec de connexion : $reason" : 'Email ou mot de passe incorrect.';
        
        $this->session->set_flashdata('sms', $this->get_alert('danger', $error_msg));
        
        redirect(base_url('Admin'));
    }

    /**
     * Vérifie le rate limiting
     * 
     * @return bool
     */
    private function check_rate_limit() {
        $ip = $this->input->ip_address();
        $cache_key = 'login_attempts_' . $ip;
        
        $attempts = $this->cache->get($cache_key);
        
        if ($attempts && $attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $lockout_key = 'login_lockout_' . $ip;
            $lockout = $this->cache->get($lockout_key);
            
            if ($lockout) {
                return false;
            }
            
            // Débloquer après le temps imparti
            $this->cache->save($lockout_key, true, self::LOCKOUT_TIME * 60);
        }
        
        return true;
    }

    /**
     * Incrémente le compteur de tentatives
     * 
     * @param string $username
     */
    private function increment_attempts($username) {
        $ip = $this->input->ip_address();
        $cache_key = 'login_attempts_' . $ip;
        
        $attempts = $this->cache->get($cache_key);
        $this->cache->save($cache_key, ($attempts ?: 0) + 1, self::LOCKOUT_TIME * 60);
    }

    /**
     * Réinitialise les tentatives après succès
     * 
     * @param string $username
     */
    private function reset_attempts($username) {
        $ip = $this->input->ip_address();
        $this->cache->delete('login_attempts_' . $ip);
        $this->cache->delete('login_lockout_' . $ip);
    }

    /**
     * Journalise une tentative
     * 
     * @param string $username
     */
    private function log_attempt($username) {
        // Vous pouvez logger dans un fichier si nécessaire
        log_message('info', "Login attempt for user: $username from IP: " . $this->input->ip_address());
    }

    /**
     * Vérifie la validité du token CSRF
     * 
     * @return bool
     */
    private function check_csrf() {
        $csrf_token = $this->input->post($this->security->get_csrf_token_name());
        return $csrf_token && $csrf_token === $this->security->get_csrf_hash();
    }

    /**
     * Vérifie si la 2FA est requise
     * 
     * @param array $user
     * @return bool
     */
    private function check_two_factor_required($user) {
        return !empty($user['two_factor_enabled']) && $user['two_factor_enabled'] == 1;
    }

    /**
     * Gère la double authentification
     * 
     * @param array $user
     */
    private function handle_two_factor_auth($user) {
        // Stocker l'ID utilisateur temporairement
        $this->session->set_userdata('two_factor_user_id', $user['id']);
        
        // Rediriger vers la page de vérification 2FA
        redirect(base_url('Admin/two_factor'));
    }

    /**
     * Page de vérification 2FA
     */
    public function two_factor() {
        if (!$this->session->userdata('two_factor_user_id')) {
            redirect(base_url('Admin'));
        }
        
        $data = [
            'csrf_token' => $this->security->get_csrf_hash(),
            'csrf_name' => $this->security->get_csrf_token_name(),
            'site_name' => $this->Model->get_setting('site_name', 'AGF')
        ];
        
        $this->load->view('TwoFactor_View', $data);
    }

    /**
     * Vérification du code 2FA
     */
    public function verify_two_factor() {
        // Validation et vérification du code 2FA
        // À implémenter selon votre logique 2FA
    }

    /**
     * Retourne l'URL de redirection selon le rôle
     * 
     * @param string $role_slug
     * @return string
     */
    private function get_redirect_url($role_slug) {
        $redirect_map = [
            'admin' => 'Dashboard',
            'medecin' => 'Dashboard',
            'investisseur' => 'Dashboard/investisseur',
            'client' => 'Dashboard/client',
            'utilisateur' => 'Dashboard/utilisateur'
        ];
        
        return base_url($redirect_map[$role_slug] ?? 'Dashboard');
    }

    /**
     * Génère un message d'alerte HTML
     * 
     * @param string $type (success, danger, warning, info)
     * @param string $message
     * @return string
     */
    private function get_alert($type, $message) {
        $icons = [
            'success' => 'bx-check-circle',
            'danger' => 'bx-error',
            'warning' => 'bx-error',
            'info' => 'bx-info-circle'
        ];
        
        $icon = $icons[$type] ?? 'bx-info-circle';
        
        return '<div class="alert alert-' . $type . ' alert-dismissible fade show mt-1 message" role="alert">
                    <i class="bx ' . $icon . ' me-2"></i>
                    <strong>' . htmlspecialchars($message) . '</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }

    /**
     * Déconnexion
     */
    public function logout() {
        // Récupérer l'ID utilisateur avant de détruire la session
        $user_id = $this->session->userdata('idUser');
        $session_id = session_id();

        // Terminer la session en base de données
        if ($user_id) {
            $this->Model->end_user_session($session_id, 'manual');
        }

        // Détruire toutes les données de session
        $session_data = [
            'idUser', 'uuid', 'email', 'nom', 'prenom', 'username',
            'photo', 'role_id', 'role', 'role_slug', 'logged_in',
            'login_time', 'last_activity', 'two_factor_user_id'
        ];
        
        $this->session->unset_userdata($session_data);
        $this->session->sess_destroy();
        
        // Supprimer le cookie de session
        delete_cookie('ci_session');

        // Journaliser la déconnexion
        log_message('info', "User logout - ID: $user_id, Session: $session_id");

        // Message de succès
        $this->session->set_flashdata('success', 
            '<div class="alert alert-success text-center">
                <i class="bx bx-log-out-circle"></i> Déconnexion réussie ! À bientôt.
            </div>'
        );

        redirect(base_url('Admin'));
    }

    /**
     * Vérification de session (appel AJAX)
     */
    public function check_session() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $logged_in = $this->session->userdata('logged_in') ? true : false;
        $last_activity = $this->session->userdata('last_activity');
        
        // Vérifier si la session a expiré (30 minutes d'inactivité)
        $session_expired = $last_activity && (time() - $last_activity > 1800);
        
        echo json_encode([
            'logged_in' => $logged_in,
            'session_expired' => $session_expired,
            'last_activity' => $last_activity
        ]);
    }

    /**
     * Refresh de session (keep-alive)
     */
    public function refresh_session() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        if ($this->session->userdata('logged_in')) {
            $this->session->set_userdata('last_activity', time());
            $this->Model->update_session_activity();
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
        }
    }

    /**
     * Page d'inscription
     */
    public function register() {
        // Si déjà connecté, rediriger
        if ($this->session->userdata('logged_in')) {
            redirect(base_url('Dashboard'));
        }
        
        $data = [
            'csrf_token' => $this->security->get_csrf_hash(),
            'csrf_name' => $this->security->get_csrf_token_name(),
            'site_name' => $this->Model->get_setting('site_name', 'AGF'),
            'site_logo' => $this->Model->get_setting('site_logo', 'logo.png'),
            'pays' => $this->Model->read('pays', ['est_actif' => 1], 'nom_fr', 'ASC')
        ];
        
        $this->load->view('Register_View', $data);
    }

    /**
     * Traitement de l'inscription
     */
    public function do_register() {
        // Vérification CSRF
        if ($this->config->item('csrf_protection') && !$this->check_csrf()) {
            $this->session->set_flashdata('sms', $this->get_alert('danger', 'Erreur de sécurité.'));
            redirect(base_url('Admin/register'));
            return;
        }

        // Règles de validation
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]|trim|max_length[100]');
        $this->form_validation->set_rules('password', 'Mot de passe', 'required|min_length[8]|trim');
        $this->form_validation->set_rules('confirm_password', 'Confirmation', 'required|matches[password]');
        $this->form_validation->set_rules('nom', 'Nom', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('prenom', 'Prénom', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'required|trim|max_length[20]');
        
        if ($this->form_validation->run() == FALSE) {
            $errors = validation_errors();
            $this->session->set_flashdata('sms', $this->get_alert('danger', $errors ?: 'Erreur de validation.'));
            redirect(base_url('Admin/register'));
            return;
        }

        // Nettoyage des données
        $data = [
            'uuid' => uuid_v4(),
            'email' => $this->security->xss_clean($this->input->post('email', TRUE)),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'nom' => $this->security->xss_clean($this->input->post('nom', TRUE)),
            'prenom' => $this->security->xss_clean($this->input->post('prenom', TRUE)),
            'telephone' => $this->security->xss_clean($this->input->post('telephone', TRUE)),
            'role_id' => 8, // Rôle par défaut: Utilisateur
            'is_active' => 1,
            'email_verified_at' => null
        ];

        // Générer token de vérification email
        $verification_token = bin2hex(random_bytes(32));
        $data['email_verification_token'] = $verification_token;

        // Démarrer transaction
        $this->Model->begin_transaction();

        try {
            // Créer l'utilisateur
            $user_id = $this->Model->create_last_id('users', $data);

            if (!$user_id) {
                throw new Exception("Échec de la création du compte");
            }

            // Envoyer email de vérification (à implémenter)
            // $this->send_verification_email($data['email'], $verification_token);

            // Valider transaction
            $this->Model->commit_transaction();

            // Message de succès
            $this->session->set_flashdata('success', 
                '<div class="alert alert-success text-center">
                    <i class="bx bx-check-circle"></i> Inscription réussie ! Veuillez vérifier votre email.
                </div>'
            );

            redirect(base_url('Admin'));

        } catch (Exception $e) {
            $this->Model->rollback_transaction();
            log_message('error', "Registration failed: " . $e->getMessage());
            
            $this->session->set_flashdata('sms', $this->get_alert('danger', 
                'Erreur lors de l\'inscription. Veuillez réessayer.'
            ));
            
            redirect(base_url('Admin/register'));
        }
    }

    /**
     * Vérification d'email
     * 
     * @param string $token
     */
    public function verify_email($token) {
        $user = $this->Model->verify_email_token($token);
        
        if ($user) {
            $this->session->set_flashdata('success', 
                '<div class="alert alert-success text-center">
                    <i class="bx bx-check-circle"></i> Email vérifié avec succès ! Vous pouvez maintenant vous connecter.
                </div>'
            );
        } else {
            $this->session->set_flashdata('sms', $this->get_alert('danger', 
                'Token invalide ou expiré.'
            ));
        }
        
        redirect(base_url('Admin'));
    }
}

/**
 * Génère un UUID v4
 * 
 * @return string
 */
if (!function_exists('uuid_v4')) {
    function uuid_v4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}