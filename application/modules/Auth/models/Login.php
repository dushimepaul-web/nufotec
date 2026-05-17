<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('security');
        $this->load->helper('string');
    }

    // ==================== AUTHENTIFICATION ====================
    
    /**
     * Vérifier les identifiants de connexion
     * @param string $login Email ou téléphone
     * @param string $password Mot de passe
     * @return array
     */
    public function verify_login($login, $password) {
        // Chercher par email ou téléphone
        $this->db->where('(email = '.$this->db->escape($login).' OR telephone = '.$this->db->escape($login).')');
        $this->db->where('deleted_at IS NULL');
        $user = $this->db->get('users')->row_array();

        if (!$user) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        // Vérifier si compte actif
        if (isset($user['is_active']) && $user['is_active'] != 1) {
            return ['success' => false, 'message' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.'];
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
     * @param array $data Données utilisateur
     * @return array
     */
    public function create_user($data) {
        // Vérifier que les données nécessaires sont présentes
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Email et mot de passe requis'];
        }

        // Vérifier email unique
        if ($this->email_exists($data['email'])) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }

        // Vérifier téléphone unique
        if (!empty($data['telephone']) && $this->phone_exists($data['telephone'])) {
            return ['success' => false, 'message' => 'Ce numéro de téléphone est déjà utilisé'];
        }

        // Récupérer l'ID du rôle par défaut
        $default_role_id = $this->get_default_role_id();
        
        // Génération token de vérification email
        $email_verification_token = random_string('alnum', 64);
        
        // Préparer les données pour la table users
        $user_data = [
            'uuid' => $this->generate_uuid(),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'], // Déjà hashé dans le contrôleur
            'nom' => ucfirst(strtolower(trim($data['nom'] ?? ''))),
            'prenom' => ucfirst(strtolower(trim($data['prenom'] ?? ''))),
            'telephone' => $data['telephone'] ?? null,
            'date_naissance' => $data['date_naissance'] ?? null,
            'genre' => $data['genre'] ?? null,
            'photo' => 'default-avatar.png',
            'role_id' => $data['role_id'] ?? $default_role_id,
            'type_utilisateur' => $data['type_utilisateur'] ?? 'patient',
            'nom_entreprise' => $data['nom_entreprise'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'email_verification_token' => $email_verification_token,
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

        return [
            'success' => true, 
            'user_id' => $user_id, 
            'user' => $this->get_user_data($user_id),
            'verification_token' => $email_verification_token
        ];
    }

    /**
     * Récupérer l'ID du rôle par défaut
     * @return int|null
     */
    private function get_default_role_id() {
        // Vérifier si la table roles existe
        if ($this->db->table_exists('roles')) {
            // Chercher le rôle 'patient' ou 'user'
            $query = $this->db->get_where('roles', ['slug' => 'patient']);
            $role = $query->row_array();
            if ($role) {
                return $role['id'];
            }
            
            $query = $this->db->get_where('roles', ['slug' => 'user']);
            $role = $query->row_array();
            if ($role) {
                return $role['id'];
            }
            
            // Sinon, prendre le premier rôle disponible
            $query = $this->db->get('roles', 1);
            $role = $query->row_array();
            if ($role) {
                return $role['id'];
            }
        }
        
        // Rôle par défaut (patient) - à ajuster selon votre BDD
        return 8;
    }

    /**
     * Récupérer les données utilisateur
     * @param int $user_id
     * @return array|null
     */
    public function get_user_data($user_id) {
        $this->db->select('id, uuid, email, nom, prenom, telephone, date_naissance, genre, photo, role_id, type_utilisateur, nom_entreprise, is_active, email_verified_at, two_factor_enabled, last_login_at, last_login_ip, created_at, secteur_activite, est_verifie');
        $this->db->where('id', $user_id);
        $user = $this->db->get('users')->row_array();
        
        if ($user) {
            $user['fullname'] = trim($user['prenom'] . ' ' . $user['nom']);
            $user['photo_url'] = base_url('uploads/photos/' . ($user['photo'] ?? 'default-avatar.png'));
            $user['is_email_verified'] = !is_null($user['email_verified_at']);
        }
        
        return $user;
    }

    /**
     * Récupérer utilisateur par UUID
     * @param string $uuid
     * @return array|null
     */
    public function get_user_by_uuid($uuid) {
        $this->db->where('uuid', $uuid);
        $this->db->where('deleted_at IS NULL');
        $user = $this->db->get('users')->row_array();
        
        if ($user) {
            $user['fullname'] = trim($user['prenom'] . ' ' . $user['nom']);
        }
        
        return $user;
    }

    /**
     * Générer un UUID v4
     * @return string
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
     * Vérifier si téléphone existe
     * @param string $phone
     * @return bool
     */
    // Dans Login.php, modifiez la validation du téléphone
public function phone_exists($phone) {
    // Nettoyer le numéro (enlever + et espaces)
    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Chercher dans la base
    $this->db->where('deleted_at IS NULL');
    $this->db->group_start();
    $this->db->like('telephone', $clean_phone, 'both');
    $this->db->or_like('telephone', '+' . $clean_phone, 'both');
    $this->db->group_end();
    
    return $this->db->get('users')->num_rows() > 0;
}


    /**
     * Récupérer utilisateur par email
     * @param string $email
     * @return array|null
     */
    public function get_by_email($email) {
        $this->db->where('email', $email);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get('users')->row_array();
    }

    /**
     * Récupérer un utilisateur par son numéro de téléphone
     * @param string $phone
     * @return array|null
     */
    public function get_user_by_phone($phone) {
        $this->db->where('telephone', $phone);
        $this->db->where('deleted_at IS NULL');
        $query = $this->db->get('users');
        return $query->row_array();
    }

    /**
     * Mettre à jour le mot de passe
     * @param int $user_id
     * @param string $hashed_password
     * @return bool
     */
    public function update_password($user_id, $hashed_password) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'password' => $hashed_password,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mettre à jour la dernière connexion
     * @param int $user_id
     * @param string $ip_address
     * @return bool
     */
    public function update_last_login($user_id, $ip_address) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip_address
        ]);
    }

    /**
     * Sauvegarder le token de connexion (remember me)
     * @param int $user_id
     * @param string $token
     * @return bool
     */
    public function save_remember_token($user_id, $token) {
        // Ajouter une colonne remember_token si elle n'existe pas
        // Sinon on utilise la session standard
        return true;
    }

    /**
     * Vérifier et valider l'email
     * @param string $token
     * @return array
     */
    public function verify_email($token) {
        $this->db->where('email_verification_token', $token);
        $user = $this->db->get('users')->row_array();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Token invalide'];
        }
        
        if (!is_null($user['email_verified_at'])) {
            return ['success' => false, 'message' => 'Email déjà vérifié'];
        }
        
        $this->db->where('id', $user['id']);
        $this->db->update('users', [
            'email_verified_at' => date('Y-m-d H:i:s'),
            'email_verification_token' => null
        ]);
        
        return ['success' => true, 'message' => 'Email vérifié avec succès', 'user_id' => $user['id']];
    }

    /**
     * Générer un token de réinitialisation de mot de passe
     * @param string $email
     * @return array
     */
    public function generate_reset_token($email) {
        $user = $this->get_by_email($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Aucun compte trouvé avec cet email'];
        }
        
        $token = random_string('alnum', 64);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Table password_resets (à créer si nécessaire)
        // Pour l'instant on stocke en session
        $this->session->set_tempdata('reset_token', $token, 3600);
        $this->session->set_tempdata('reset_email', $email, 3600);
        
        return ['success' => true, 'token' => $token];
    }

    /**
     * Réinitialiser le mot de passe avec token
     * @param string $token
     * @param string $new_password
     * @return array
     */
    public function reset_password_with_token($token, $new_password) {
        $reset_email = $this->session->tempdata('reset_email');
        $reset_token = $this->session->tempdata('reset_token');
        
        if (!$reset_email || !$reset_token || $reset_token !== $token) {
            return ['success' => false, 'message' => 'Token invalide ou expiré'];
        }
        
        $user = $this->get_by_email($reset_email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur introuvable'];
        }
        
        $this->db->where('id', $user['id']);
        $updated = $this->db->update('users', [
            'password' => password_hash($new_password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($updated) {
            $this->session->unset_tempdata('reset_token');
            $this->session->unset_tempdata('reset_email');
            return ['success' => true, 'message' => 'Mot de passe réinitialisé avec succès'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la réinitialisation'];
    }

    /**
     * Activer/Désactiver un compte utilisateur
     * @param int $user_id
     * @param int $is_active (0 ou 1)
     * @return bool
     */
    public function set_user_active($user_id, $is_active) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'is_active' => $is_active,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mettre à jour le profil utilisateur
     * @param int $user_id
     * @param array $data
     * @return bool
     */
    public function update_profile($user_id, $data) {
        $allowed_fields = ['nom', 'prenom', 'telephone', 'date_naissance', 'genre', 'photo', 'nom_entreprise', 'secteur_activite'];
        $update_data = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $user_id);
        return $this->db->update('users', $update_data);
    }

    /**
     * Mettre à jour la photo de profil
     * @param int $user_id
     * @param string $filename
     * @return bool
     */
    public function update_photo($user_id, $filename) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'photo' => $filename,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Compter le nombre total d'utilisateurs
     * @param string $type_utilisateur (optionnel)
     * @return int
     */
    public function count_users($type_utilisateur = null) {
        $this->db->where('deleted_at IS NULL');
        if ($type_utilisateur) {
            $this->db->where('type_utilisateur', $type_utilisateur);
        }
        return $this->db->count_all_results('users');
    }

    /**
     * Récupérer tous les utilisateurs (avec pagination)
     * @param int $limit
     * @param int $offset
     * @param string $type_utilisateur
     * @return array
     */
    public function get_all_users($limit = null, $offset = 0, $type_utilisateur = null) {
        $this->db->select('id, uuid, email, nom, prenom, telephone, photo, type_utilisateur, nom_entreprise, is_active, email_verified_at, created_at, last_login_at');
        $this->db->where('deleted_at IS NULL');
        
        if ($type_utilisateur) {
            $this->db->where('type_utilisateur', $type_utilisateur);
        }
        
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        $users = $this->db->get('users')->result_array();
        
        foreach ($users as &$user) {
            $user['fullname'] = trim($user['prenom'] . ' ' . $user['nom']);
        }
        
        return $users;
    }

    /**
     * Supprimer un utilisateur (soft delete)
     * @param int $user_id
     * @return bool
     */
    public function delete_user($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'is_active' => 0
        ]);
    }

    /**
     * Restaurer un utilisateur supprimé
     * @param int $user_id
     * @return bool
     */
    public function restore_user($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'deleted_at' => null,
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Déterminer le type d'utilisateur en fonction de l'URL de provenance
     * @param string $referer L'URL de provenance
     * @return string
     */
    public function determine_user_type($referer) {
        $type = 'patient'; // Type par défaut
        
        if (empty($referer)) {
            return $type;
        }
        
        $referer = strtolower($referer);
        
        if (strpos($referer, 'medecin') !== false || strpos($referer, 'doctor') !== false) {
            $type = 'medecin';
        } elseif (strpos($referer, 'entreprise') !== false || strpos($referer, 'company') !== false) {
            $type = 'entreprise';
        } elseif (strpos($referer, 'investisseur') !== false || strpos($referer, 'investor') !== false) {
            $type = 'investisseur';
        } elseif (strpos($referer, 'partenaire') !== false || strpos($referer, 'partner') !== false) {
            $type = 'partenaire';
        } elseif (strpos($referer, 'broker') !== false) {
            $type = 'broker';
        } elseif (strpos($referer, 'admin') !== false || strpos($referer, 'backoffice') !== false) {
            $type = 'admin';
        }
        
        return $type;
    }




    // ==================== GESTION OTP (MOT DE PASSE OUBLIÉ) ====================
    
    /**
     * Générer un code OTP à 6 chiffres
     * @return string
     */
    public function generate_otp_code() {
        return sprintf("%06d", mt_rand(1, 999999));
    }
    
    /**
     * Sauvegarder un code OTP pour réinitialisation de mot de passe
     * @param int $user_id
     * @param string $code
     * @param string $email
     * @return bool
     */
    public function save_reset_otp($user_id, $code, $email) {
        // Supprimer les anciens codes non utilisés pour cet utilisateur
        $this->db->where('user_id', $user_id);
        $this->db->where('type_otp', 'reinitialisation_mdp');
        $this->db->where('utilise', 0);
        $this->db->delete('codes_otp');
        
        // Insérer le nouveau code
        $data = array(
            'user_id' => $user_id,
            'code' => $code,
            'type_otp' => 'reinitialisation_mdp',
            'email' => $email,
            'tentatives' => 0,
            'date_expiration' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
            'utilise' => 0,
            'created_at' => date('Y-m-d H:i:s')
        );
        
        return $this->db->insert('codes_otp', $data);
    }
    
    /**
     * Vérifier si un code OTP est valide
     * @param int $user_id
     * @param string $code
     * @return bool|object
     */
    public function verify_reset_otp($user_id, $code) {
        $otp = $this->db->where('user_id', $user_id)
                        ->where('code', $code)
                        ->where('type_otp', 'reinitialisation_mdp')
                        ->where('utilise', 0)
                        ->where('date_expiration >', date('Y-m-d H:i:s'))
                        ->get('codes_otp')
                        ->row();
        
        if ($otp) {
            return $otp;
        }
        
        // Incrémenter le compteur de tentatives
        $this->db->where('user_id', $user_id)
                 ->where('type_otp', 'reinitialisation_mdp')
                 ->where('utilise', 0)
                 ->set('tentatives', 'tentatives+1', FALSE)
                 ->update('codes_otp');
        
        return false;
    }
    
    /**
     * Marquer un code OTP comme utilisé
     * @param int $otp_id
     * @return bool
     */
    public function mark_otp_as_used($otp_id) {
        $this->db->where('id', $otp_id);
        return $this->db->update('codes_otp', ['utilise' => 1]);
    }
    
    /**
     * Supprimer tous les codes OTP d'un utilisateur
     * @param int $user_id
     * @return bool
     */
    public function delete_user_otps($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->where('type_otp', 'reinitialisation_mdp');
        return $this->db->delete('codes_otp');
    }
    
    /**
     * Vérifier le nombre de tentatives pour un utilisateur
     * @param int $user_id
     * @return int
     */
    public function get_otp_attempts($user_id) {
        $this->db->select('tentatives');
        $this->db->where('user_id', $user_id);
        $this->db->where('type_otp', 'reinitialisation_mdp');
        $this->db->where('utilise', 0);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $otp = $this->db->get('codes_otp')->row();
        
        if ($otp) {
            return (int)$otp->tentatives;
        }
        
        return 0;
    }

    /**
 * Vérifier si email existe
 * @param string $email
 * @return bool
 */
public function email_exists($email) {
    $this->db->where('email', $email);
    $this->db->where('deleted_at IS NULL');
    return $this->db->get('users')->num_rows() > 0;
}
}