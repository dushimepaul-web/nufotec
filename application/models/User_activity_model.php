<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modèle pour gérer les activités utilisateur (table `user_activities`)
 */
class User_activity_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Enregistre une action utilisateur
     *
     * @param array $data Données de l'action (doit contenir au moins 'user_id' et 'action')
     * @return int|false ID de l'insertion ou false
     */
    public function log_action($data) {
        // Ajouter automatiquement l'IP et le user_agent s'ils ne sont pas fournis
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = $this->input->ip_address();
        }
        if (!isset($data['user_agent'])) {
            $data['user_agent'] = $this->input->user_agent();
        }
        // Ajouter la date de création
        $data['created_at'] = date('Y-m-d H:i:s');

        // Insérer dans la table
        if ($this->db->insert('user_activities', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Récupère les activités d'un utilisateur spécifique
     *
     * @param int $user_id ID de l'utilisateur
     * @param int $limit  Nombre maximum d'enregistrements
     * @param int $offset Décalage
     * @return array
     */
    public function get_user_activities($user_id, $limit = 50, $offset = 0) {
        return $this->db
            ->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get('user_activities')
            ->result_array();
    }

    /**
     * Récupère les activités d'un module spécifique
     *
     * @param string $module Nom du module
     * @param int $limit Nombre maximum d'enregistrements
     * @return array
     */
    public function get_module_activities($module, $limit = 100) {
        return $this->db
            ->where('module', $module)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('user_activities')
            ->result_array();
    }

    /**
     * Récupère les activités récentes (tous utilisateurs)
     *
     * @param int $limit Nombre maximum d'enregistrements
     * @return array
     */
    public function get_recent_activities($limit = 50) {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('user_activities')
            ->result_array();
    }

    /**
     * Récupère les activités avec jointure sur users pour obtenir le nom
     *
     * @param int $limit
     * @return array
     */
    public function get_activities_with_user($limit = 50) {
        $this->db->select('user_activities.*, users.nom, users.prenom, users.email');
        $this->db->from('user_activities');
        $this->db->join('users', 'users.id = user_activities.user_id', 'left');
        $this->db->order_by('user_activities.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    /**
     * Supprime les activités plus anciennes qu'une date donnée
     *
     * @param string $date Date limite (format Y-m-d H:i:s)
     * @return int Nombre de lignes supprimées
     */
    public function delete_older_than($date) {
        $this->db->where('created_at <', $date);
        $this->db->delete('user_activities');
        return $this->db->affected_rows();
    }

    /**
     * Compte le nombre total d'activités
     *
     * @return int
     */
    public function count_all() {
        return $this->db->count_all('user_activities');
    }

    /**
     * Récupère une activité par son ID
     *
     * @param int $id
     * @return array|null
     */
    public function get_activity($id) {
        return $this->db->where('id', $id)->get('user_activities')->row_array();
    }

    /**
     * Supprime une activité spécifique
     *
     * @param int $id
     * @return bool
     */
    public function delete_activity($id) {
        return $this->db->delete('user_activities', ['id' => $id]);
    }
}