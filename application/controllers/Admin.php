<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author:    dushime paul
 * Email:     dushimeyesupaulin@gmail.com
 * Date :     Le 20/01/2026
 * https://github.com/Dushimepaul
 */

class Admin extends MY_Controller{

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        // Charger les modèles de sessions et d'activités
        $this->load->model('user_session_model');
        $this->load->model('user_activity_model');
        // Charger la bibliothèque user_agent (utilisée dans les modèles)
        $this->load->library('user_agent');
        // Charger la bibliothèque de validation
        $this->load->library('form_validation');
        // Charger le helper de sécurité
        $this->load->helper('security');
    }

    public function index() {
        // Si déjà connecté, rediriger vers le tableau de bord
        if ($this->session->userdata('logged_in')) {
            redirect(base_url('Dashboard'));
        }
        $this->load->view('Login_View');
    }

    public function Login() {
        redirect(base_url('Admin'));
    }

    /**
     * Traite la tentative de connexion avec sécurités renforcées
     */
    public function do_login() {
        // Validation des entrées
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Mot de passe', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('sms', '<div class="alert alert-danger mt-1 message">'.validation_errors().'</div>');
            redirect(base_url('Admin'));
        }

        $username = $this->input->post('email', TRUE); // XSS filter
        $password = $this->input->post('password', TRUE);

        // --- Sécurité : limitation des tentatives (brute force) ---
        $ip = $this->input->ip_address();
        $max_attempts = 5;
        $lockout_time = 15 * 60; // 15 minutes en secondes

        // Vérifier dans la session ou une table dédiée (simplifié ici avec session)
        $attempts = $this->session->userdata('login_attempts') ?: 0;
        $last_attempt = $this->session->userdata('last_attempt') ?: 0;

        if ($attempts >= $max_attempts && (time() - $last_attempt) < $lockout_time) {
            $this->session->set_flashdata('sms', '<div class="alert alert-danger mt-1 message"><strong>Trop de tentatives.</strong> Veuillez réessayer dans 15 minutes.</div>');
            redirect(base_url('Admin'));
        }

        $checkUsername = $this->Model->check_email($username);

        if ($checkUsername == TRUE) {
            $login = $this->Model->login($username, $password);

            if (!empty($login) && isset($login['is_active']) && $login['is_active'] != 0) {
                // Réinitialiser les tentatives en cas de succès
                $this->session->unset_userdata(['login_attempts', 'last_attempt']);

                $result = $this->Model->readOne('users', ['id' => $login['id']]);

                if (!empty($result)) {
                    $role = $this->Model->readOne('roles', ['id' => $result['role_id']]);

                    // Préparer les données de session
                    $user_name = trim(($result['prenom'] ?? '') . ' ' . ($result['nom'] ?? ''));
                    $session_data = [
                        'user_id'          => $result['id'],
                        'uuid'             => $result['uuid'] ?? null,
                        'email'            => $result['email'],
                        'nom'              => $result['nom'] ?? '',
                        'prenom'           => $result['prenom'] ?? '',
                        'username'         => $user_name,
                        'photo'            => $result['photo'],
                        'type_utilisateur' => $result['type_utilisateur'],
                        'role'             => $role['nom'],
                        'role_id'          => (int)$result['role_id'],
                        'role_slug'        => $role['slug'],
                        'logged_in'        => TRUE,
                        'last_regenerate'  => time() // pour régénération périodique
                    ];

                    $this->session->set_userdata($session_data);

                    // Régénérer l'ID de session pour éviter la fixation
                    $this->session->sess_regenerate(TRUE);

                    // --- Enregistrement de la session dans user_sessions ---
                    $this->user_session_model->create_user_session($result['id'], session_id());

                    // --- Journalisation de la connexion ---
                    $this->user_activity_model->log_action([
                        'user_id'    => $result['id'],
                        'action'     => 'login',
                        'module'     => 'auth',
                        'item_id'    => $result['id'],
                        'item_name'  => $user_name,
                        'description' => 'Connexion réussie',
                        'ip_address' => $ip,
                        'user_agent' => $this->input->user_agent()
                    ]);

                    $this->session->set_flashdata('success', 'Connexion réussie ! Bienvenue ' . $user_name);
                    redirect(base_url('Dashboard'));
                } else {
                    $this->_log_failed_attempt($username, $ip, 'Utilisateur trouvé mais impossible de récupérer les détails');
                    $this->session->set_flashdata('sms', '<div class="alert alert-danger mt-1 message">Erreur interne. Veuillez réessayer.</div>');
                    redirect(base_url('Admin'));
                }
            } else {
                // Échec de connexion (mot de passe ou compte inactif)
                $this->_log_failed_attempt($username, $ip, 'Mot de passe incorrect ou compte inactif');
                $this->_increment_attempts();
                $this->session->set_flashdata('sms', '<div class="alert alert-danger mt-1 message"><strong>Oups!</strong> Mot de passe incorrect ou compte non activé.</div>');
                redirect(base_url('Admin'));
            }
        } else {
            // Email inexistant
            $this->_log_failed_attempt($username, $ip, 'Email incorrect');
            $this->_increment_attempts();
            $this->session->set_flashdata('sms', '<div class="alert alert-danger mt-1 message"><strong>Oups!</strong> Email incorrect ou compte désactivé.</div>');
            redirect(base_url('Admin'));
        }
    }

    /**
     * Incrémente le compteur de tentatives échouées
     */
    private function _increment_attempts() {
        $attempts = $this->session->userdata('login_attempts') ?: 0;
        $this->session->set_userdata('login_attempts', $attempts + 1);
        $this->session->set_userdata('last_attempt', time());
    }

    /**
     * Journalise les tentatives échouées
     */
    private function _log_failed_attempt($username, $ip, $reason) {
        // Optionnel : enregistrer dans une table dédiée ou simplement dans les logs
        log_message('info', "Échec de connexion pour $username depuis $ip : $reason");
        // On pourrait aussi utiliser le modèle d'activité avec un user_id null
        $this->user_activity_model->log_action([
            'user_id'    => null,
            'action'     => 'login_failed',
            'module'     => 'auth',
            'item_name'  => $username,
            'description' => "Tentative échouée : $reason",
            'ip_address' => $ip,
            'user_agent' => $this->input->user_agent()
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout() {
        $user_id = $this->session->userdata('user_id');
        $username = $this->session->userdata('username');

        if ($user_id) {
            // Terminer la session en cours dans user_sessions
            $this->user_session_model->end_user_session(session_id(), 'manual');

            // Journaliser la déconnexion
            $this->user_activity_model->log_action([
                'user_id'    => $user_id,
                'action'     => 'logout',
                'module'     => 'Admin',
                'item_id'    => $user_id,
                'item_name'  => $username,
                'description' => 'Déconnexion manuelle',
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent()
            ]);
        }

        // Détruire la session PHP
        $this->session->sess_destroy();

        $this->session->set_flashdata('success', 'Déconnexion réussie');
        redirect(base_url('Admin'));
    }
}