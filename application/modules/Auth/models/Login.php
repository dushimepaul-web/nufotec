<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('security');
    }

    // ==================== AUTHENTIFICATION ====================
    
    /**
     * Vérifier les identifiants de connexion
     */
    public function verify_login($login, $password) {
        // Chercher par email ou téléphone
        $this->db->where('(email = '.$this->db->escape($login).' OR telephone = '.$this->db->escape($login).')');
        $user = $this->db->get('users')->row_array();

        if (!$user) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        // Vérifier mot de passe
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        // Vérifier si compte actif
        if ($user['is_active'] != 1) {
            return ['success' => false, 'message' => 'Votre compte est désactivé'];
        }

        // Mettre à jour dernière connexion
        $this->db->where('id', $user['id']);
        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->input->ip_address()
        ]);

        return ['success' => true, 'user' => $this->get_user_data($user['id'])];
    }

    /**
     * Créer un nouvel utilisateur (inscription)
     */
    public function create_user($data) {
        // Vérifier email unique
        if ($this->email_exists($data['email'])) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }

        // Vérifier téléphone unique
        if (!empty($data['telephone']) && $this->phone_exists($data['telephone'])) {
            return ['success' => false, 'message' => 'Ce numéro de téléphone est déjà utilisé'];
        }

        // Préparer les données pour la table users
        $user_data = [
            'uuid' => $this->generate_uuid(),
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'] ?? null,
            'role_id' => 8, // Rôle patient par défaut
            'type_utilisateur' => 'patient',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Insérer
        $this->db->insert('users', $user_data);
        $user_id = $this->db->insert_id();

        if (!$user_id) {
            return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
        }

        return ['success' => true, 'user_id' => $user_id, 'user' => $this->get_user_data($user_id)];
    }

    /**
     * Récupérer les données utilisateur
     */
    public function get_user_data($user_id) {
        $this->db->select('id, uuid, email, nom, prenom, telephone, photo, type_utilisateur, is_active, created_at');
        $this->db->where('id', $user_id);
        $user = $this->db->get('users')->row_array();
        
        if ($user) {
            $user['fullname'] = trim($user['prenom'] . ' ' . $user['nom']);
            $user['photo'] = $user['photo'] ?? 'default-avatar.png';
        }
        
        return $user;
    }

    /**
     * Générer un UUID
     */
    private function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Vérifier si email existe
     */
    public function email_exists($email) {
        $this->db->where('email', $email);
        return $this->db->get('users')->num_rows() > 0;
    }

    /**
     * Vérifier si téléphone existe
     */
    public function phone_exists($phone) {
        $this->db->where('telephone', $phone);
        return $this->db->get('users')->num_rows() > 0;
    }

    /**
     * Récupérer utilisateur par email
     */
    public function get_by_email($email) {
        $this->db->where('email', $email);
        return $this->db->get('users')->row_array();
    }

   public function save_remember_token($user_id, $token) {
    $data = [
        'remember_token' => $token,
        'token_expires' => date('Y-m-d H:i:s', time() + 86400 * 30) // 30 jours
    ];
    $this->db->where('id', $user_id)->update('users', $data);
    return $this->db->affected_rows() > 0;
}
}

/*<?php
// ==================== MODEL: Login.php ====================
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('security');
    }

    // ==================== AUTHENTIFICATION ====================
    
    /**
     * Vérifier les identifiants de connexion
 
    public function verify_login($login, $password) {
        // Chercher par email ou téléphone
        $this->db->where('(email = '.$this->db->escape($login).' OR phone = '.$this->db->escape($login).')');
        $this->db->where('status', 'active');
        $user = $this->db->get('users')->row_array();

        if (!$user) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        // Vérifier mot de passe
        if (!password_verify($password, $user['password'])) {
            // Incrémenter tentatives échouées
            $this->increment_login_attempts($user['id']);
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        // Vérifier si compte bloqué
        if ($user['login_attempts'] >= 5 && strtotime($user['last_attempt']) > strtotime('-30 minutes')) {
            return ['success' => false, 'message' => 'Compte temporairement bloqué. Réessayez dans 30 minutes.'];
        }

        // Réinitialiser tentatives
        $this->reset_login_attempts($user['id']);

        // Mettre à jour dernière connexion
        $this->db->where('id', $user['id']);
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address()
        ]);

        return ['success' => true, 'user' => $this->get_user_data($user['id'])];
    }

    /**
     * Créer un nouvel utilisateur (inscription)

    public function create_user($data) {
        // Vérifier email unique
        if ($this->email_exists($data['email'])) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }

        // Vérifier téléphone unique
        if ($this->phone_exists($data['phone'])) {
            return ['success' => false, 'message' => 'Ce numéro de téléphone est déjà utilisé'];
        }

        // Hasher le mot de passe
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Données par défaut
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['login_attempts'] = 0;

        // Insérer
        $this->db->insert('users', $data);
        $user_id = $this->db->insert_id();

        if (!$user_id) {
            return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
        }

        return ['success' => true, 'user_id' => $user_id, 'user' => $this->get_user_data($user_id)];
    }

    /**
     * Récupérer les données utilisateur (sans mot de passe)
   
    public function get_user_data($user_id) {
        $this->db->select('id, fullname, email, phone, profile_complete, status, created_at, last_login');
        $this->db->where('id', $user_id);
        return $this->db->get('users')->row_array();
    }

    /**
     * Vérifier si email existe

    public function email_exists($email) {
        $this->db->where('email', $email);
        return $this->db->get('users')->num_rows() > 0;
    }

    /**
     * Vérifier si téléphone existe
    
    public function phone_exists($phone) {
        $this->db->where('phone', $phone);
        return $this->db->get('users')->num_rows() > 0;
    }

    /**
     * Incrémenter tentatives de connexion

    private function increment_login_attempts($user_id) {
        $this->db->where('id', $user_id);
        $this->db->set('login_attempts', 'login_attempts + 1', FALSE);
        $this->db->set('last_attempt', date('Y-m-d H:i:s'));
        $this->db->update('users');
    }

    /**
     * Réinitialiser tentatives

    private function reset_login_attempts($user_id) {
        $this->db->where('id', $user_id);
        $this->db->update('users', [
            'login_attempts' => 0,
            'last_attempt' => null
        ]);
    }

    // ==================== RÉINITIALISATION MOT DE PASSE ====================

    public function get_by_email($email) {
        $this->db->where('email', $email);
        $this->db->where('status', 'active');
        return $this->db->get('users')->row_array();
    }

    public function save_reset_token($user_id, $token, $expires) {
        // Supprimer anciens tokens
        $this->db->where('user_id', $user_id);
        $this->db->delete('password_resets');
        
        $data = [
            'user_id' => $user_id,
            'token' => hash('sha256', $token), // Stocker hash du token
            'expires' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('password_resets', $data);
    }

    public function verify_reset_token($token) {
        $token_hash = hash('sha256', $token);
        
        $this->db->select('pr.*, u.email, u.id as user_id, u.fullname');
        $this->db->from('password_resets pr');
        $this->db->join('users u', 'u.id = pr.user_id');
        $this->db->where('pr.token', $token_hash);
        $this->db->where('pr.expires >', date('Y-m-d H:i:s'));
        $this->db->where('pr.used', 0);
        
        return $this->db->get()->row_array();
    }

    public function update_password($user_id, $hashed_password) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'password' => $hashed_password,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function delete_reset_token($token) {
        $token_hash = hash('sha256', $token);
        $this->db->where('token', $token_hash);
        return $this->db->delete('password_resets');
    }

    public function mark_token_used($token) {
        $token_hash = hash('sha256', $token);
        $this->db->where('token', $token_hash);
        return $this->db->update('password_resets', ['used' => 1]);
    }

    // ==================== PROFIL UTILISATEUR ====================

    /**
     * Compléter le profil patient après inscription
 
    public function complete_profile($user_id, $data) {
        $data['profile_complete'] = 1;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $user_id);
        $updated = $this->db->update('users', $data);

        if ($updated) {
            return ['success' => true, 'user' => $this->get_user_data($user_id)];
        }
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour du profil'];
    }

    /**
     * Vérifier si profil est complet
     
    public function is_profile_complete($user_id) {
        $this->db->select('profile_complete');
        $this->db->where('id', $user_id);
        $result = $this->db->get('users')->row();
        return $result && $result->profile_complete == 1;
    }
}*/