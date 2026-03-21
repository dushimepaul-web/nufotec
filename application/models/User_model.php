<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    protected $table = 'users';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Récupérer un utilisateur par son ID
     */
    public function get_user_by_id($id) {
        return $this->db->where('id', $id)
                       ->where('deleted_at IS NULL', NULL, FALSE)
                       ->get($this->table)
                       ->row_array();
    }

    /**
     * Récupérer un utilisateur par son UUID
     */
    public function get_user_by_uuid($uuid) {
        return $this->db->where('uuid', $uuid)
                       ->where('deleted_at IS NULL', NULL, FALSE)
                       ->get($this->table)
                       ->row_array();
    }

    /**
     * Récupérer un utilisateur par email
     */
    public function get_user_by_email($email) {
        return $this->db->where('email', $email)
                       ->where('deleted_at IS NULL', NULL, FALSE)
                       ->get($this->table)
                       ->row_array();
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update_user($id, $data) {
        // Ajouter la date de mise à jour manuellement si nécessaire
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Récupérer les informations d'un rôle
     */
    public function get_role_by_id($role_id) {
        return $this->db->where('id', $role_id)
                       ->get('roles')
                       ->row_array();
    }

    /**
     * Vérifier si un email existe déjà (excluant l'utilisateur actuel)
     */
    public function email_exists_except($email, $exclude_id) {
        return $this->db->where('email', $email)
                       ->where('id !=', $exclude_id)
                       ->where('deleted_at IS NULL', NULL, FALSE)
                       ->count_all_results($this->table) > 0;
    }

    /**
     * Mettre à jour la date de dernière connexion
     */
    public function update_last_login($id, $ip_address) {
        return $this->db->where('id', $id)
                       ->update($this->table, [
                           'last_login_at' => date('Y-m-d H:i:s'),
                           'last_login_ip' => $ip_address
                       ]);
    }
}