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
        // Chercher par email ou téléphone - UTILISEZ LES BONS NOMS DE COLONNES
        $this->db->where('(email = '.$this->db->escape($login).' OR telephone = '.$this->db->escape($login).')');
        $user = $this->db->get('users')->row_array();

        if (!$user) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        // Vérifier si compte actif
        if (isset($user['is_active']) && $user['is_active'] != 1) {
            return ['success' => false, 'message' => 'Votre compte est désactivé'];
        }

        // Vérifier mot de passe
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
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
    // Vérifier que les données nécessaires sont présentes
    if (empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'message' => 'Données manquantes'];
    }

    // Vérifier email unique
    if ($this->email_exists($data['email'])) {
        return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
    }

    // Vérifier téléphone unique
    if (!empty($data['telephone']) && $this->phone_exists($data['telephone'])) {
        return ['success' => false, 'message' => 'Ce numéro de téléphone est déjà utilisé'];
    }

    // Récupérer l'ID du rôle par défaut (par exemple 'patient' ou 'user')
    $default_role_id = $this->get_default_role_id();
    
    // Préparer les données pour la table users
    $user_data = [
        'uuid' => $this->generate_uuid(),
        'email' => $data['email'],
        'password' => $data['password'],
        'nom' => $data['nom'] ?? '',
        'prenom' => $data['prenom'] ?? '',
        'telephone' => $data['telephone'] ?? null,
        'role_id' => $default_role_id, // AJOUTER role_id
        'type_utilisateur' => $data['type_utilisateur'],
        'is_active' => $data['is_active'] ?? 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Insérer
    $inserted = $this->db->insert('users', $user_data);
    
    if (!$inserted) {
        $error = $this->db->error();
        log_message('error', 'Erreur insertion user: ' . $error['message']);
        return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
    }
    
    $user_id = $this->db->insert_id();

    return ['success' => true, 'user_id' => $user_id, 'user' => $this->get_user_data($user_id)];
}

/**
 * Récupérer l'ID du rôle par défaut
 */
private function get_default_role_id() {
    // Vérifier si la table roles existe
    if ($this->db->table_exists('roles')) {
        $query = $this->db->get_where('roles', ['slug' => 'user']);
        $role = $query->row_array();
        if ($role) {
            return $role['id'];
        }
        
        // Sinon, prendre le premier rôle disponible
        $query = $this->db->get('roles', 8);
        $role = $query->row_array();
        if ($role) {
            return $role['id'];
        }
    }
    
    // Si aucun rôle n'existe, retourner NULL ou 0
    return null;
}

    /**
     * Récupérer les données utilisateur
     */
    public function get_user_data($user_id) {
        $this->db->select('id, uuid, email, nom, prenom, telephone, photo, type_utilisateur, is_active, created_at, last_login_at');
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

    /**
     * Récupérer un utilisateur par son numéro de téléphone
     */
    public function get_user_by_phone($phone) {
        $this->db->where('telephone', $phone);
        $query = $this->db->get('users');
        return $query->row_array();
    }

    /**
     * Sauvegarder le token de connexion
     */
    public function save_remember_token($user_id, $token) {
        $data = [
            'remember_token' => $token,
            'token_expires' => date('Y-m-d H:i:s', time() + 86400 * 30) // 30 jours
        ];
        $this->db->where('id', $user_id)->update('users', $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Détermine le type d'utilisateur en fonction de l'URL de provenance
     * @param string $referer L'URL de provenance
     * @return string Le type d'utilisateur
     */
    public function determine_user_type($referer) {
        // Type par défaut
        $type = 'visiteur';
        
        if (empty($referer)) {
            return $type;
        }
        
        $referer = strtolower($referer);
        
        // Déterminer le type selon l'URL
        if (strpos($referer, 'medecins') !== false || strpos($referer, 'doctor') !== false) {
            $type = 'patient';
        } elseif (strpos($referer, 'panier') !== false || strpos($referer, 'client') !== false) {
            $type = 'client';
        } elseif (strpos($referer, 'vendeur') !== false || strpos($referer, 'seller') !== false) {
            $type = 'vendeur';
        } elseif (strpos($referer, 'admin') !== false || strpos($referer, 'backoffice') !== false) {
            $type = 'admin';
        } elseif (strpos($referer, 'partenaire') !== false || strpos($referer, 'partner') !== false) {
            $type = 'partenaire';
        }
        
        return $type;
    }
}