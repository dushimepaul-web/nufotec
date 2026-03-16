<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_session_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('user_agent');
    }

    // ------------------------------------------------------------------------
    // CRUD de base (optionnel, mais on utilise directement les méthodes CI)
    // ------------------------------------------------------------------------

    /**
     * Insère une session et retourne l'ID
     */
    private function _insert($data) {
        $this->db->insert('user_sessions', $data);
        return $this->db->insert_id();
    }

    /**
     * Met à jour une ou plusieurs sessions selon les conditions
     */
    private function _update($conditions, $data) {
        $this->db->where($conditions);
        return $this->db->update('user_sessions', $data);
    }

    /**
     * Récupère une seule session selon les conditions
     */
    private function _get_one($conditions) {
        return $this->db->where($conditions)->get('user_sessions')->row_array();
    }

    /**
     * Récupère plusieurs sessions selon les conditions
     */
    private function _get_many($conditions = [], $order_by = null, $direction = 'DESC', $limit = null, $offset = 0) {
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        if ($order_by) {
            $this->db->order_by($order_by, $direction);
        }
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get('user_sessions')->result_array();
    }

    // ------------------------------------------------------------------------
    // GESTION DES SESSIONS UTILISATEURS
    // ------------------------------------------------------------------------

    /**
     * Crée une nouvelle session utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @param string|null $session_id ID de session (si null, utilise session_id() courante)
     * @param int $expiry_hours Durée de validité en heures (défaut 24)
     * @return int|false ID de la session créée ou false en cas d'échec
     */
    public function create_user_session($user_id, $session_id = null, $expiry_hours = 24) {
        // Récupérer l'ID de session courant si non fourni
        if ($session_id === null) {
            $session_id = session_id();
        }

        // Détection du type d'appareil
        $device_type = 'unknown';
        if ($this->agent->is_mobile()) {
            $device_type = 'mobile';
        } elseif ($this->agent->is_robot()) {
            $device_type = 'bot';
        } elseif ($this->agent->is_browser()) {
            // On considère que si c'est un navigateur, c'est desktop (ou tablette si on veut affiner)
            // Pour une détection plus précise de tablette, il faudrait une bibliothèque plus complète.
            $device_type = 'desktop';
        }
        // Note : is_tablet() n'existe pas nativement dans CI, on peut utiliser une regex sur user_agent si nécessaire.

        $data = [
            'user_id'       => $user_id,
            'session_id'    => $session_id,
            'ip_address'    => $this->input->ip_address(),
            'user_agent'    => $this->input->user_agent(),
            'device_type'   => $device_type,
            'platform'      => $this->agent->platform(),
            'browser'       => $this->agent->browser(),
            'login_time'    => date('Y-m-d H:i:s'),
            'last_activity' => date('Y-m-d H:i:s'),
            'expiry_time'   => date('Y-m-d H:i:s', strtotime("+{$expiry_hours} hours")),
            'is_active'     => 1
        ];

        // Désactiver toutes les autres sessions actives de cet utilisateur
        // (on force la déconnexion des anciennes sessions)
        $this->db->where('user_id', $user_id)
                 ->where('is_active', 1)
                 ->update('user_sessions', [
                     'is_active'     => 0,
                     'logout_time'   => date('Y-m-d H:i:s'),
                     'logout_reason' => 'force'
                 ]);

        // Insérer la nouvelle session
        $inserted = $this->_insert($data);
        if ($inserted) {
            return $inserted;
        }
        return false;
    }

    

    /**
 * Met à jour l'activité d'une session et vérifie le timeout d'inactivité.
 * Si l'utilisateur est inactif depuis plus de $max_inactivity minutes, il est déconnecté.
 * 
 * @param string|null $session_id ID de session (null = session courante)
 * @param int $max_inactivity Durée maximale d'inactivité en minutes (défaut 15)
 * @return bool true si la session est encore active, false si elle a été terminée pour inactivité
 */
public function update_session_activity($session_id = null, $max_inactivity = 30) {
    if ($session_id === null) {
        $session_id = session_id();
    }

    // Récupérer la session active
    $session = $this->_get_one([
        'session_id' => $session_id,
        'is_active'  => 1
    ]);

    if (!$session) {
        return false; // session déjà inactive ou inexistante
    }

    // Vérifier le temps écoulé depuis la dernière activité
    $last_activity = strtotime($session['last_activity']);
    $now = time();
    $inactive_seconds = $now - $last_activity;

    if ($inactive_seconds > ($max_inactivity * 60)) {
        // Dépassement du temps d'inactivité : on termine la session
        $this->_update(
            ['id' => $session['id']],
            [
                'is_active'     => 0,
                'logout_time'   => date('Y-m-d H:i:s'),
                'logout_reason' => 'timeout'
            ]
        );
        return false; // session terminée
    }

    // Sinon, on met à jour la dernière activité
    $this->_update(
        ['id' => $session['id']],
        ['last_activity' => date('Y-m-d H:i:s')]
    );
    return true;
}



    /**
     * Termine une session utilisateur (déconnexion volontaire ou forcée)
     * 
     * @param string|null $session_id ID de session (null = session courante)
     * @param string $reason Raison de la déconnexion (manual, timeout, expired, force)
     * @return bool
     */
    public function end_user_session($session_id = null, $reason = 'manual') {
        if ($session_id === null) {
            $session_id = session_id();
        }

        return $this->_update(
            ['session_id' => $session_id, 'is_active' => 1],
            [
                'is_active'     => 0,
                'logout_time'   => date('Y-m-d H:i:s'),
                'logout_reason' => $reason
            ]
        );
    }

    /**
     * Termine toutes les sessions actives d'un utilisateur (ex: admin force)
     * 
     * @param int $user_id ID de l'utilisateur
     * @param string $reason Raison de la déconnexion
     * @return bool
     */
    public function end_all_user_sessions($user_id, $reason = 'force') {
        return $this->_update(
            ['user_id' => $user_id, 'is_active' => 1],
            [
                'is_active'     => 0,
                'logout_time'   => date('Y-m-d H:i:s'),
                'logout_reason' => $reason
            ]
        );
    }

    /**
     * Vérifie si une session est valide (active, non expirée, IP optionnelle)
     * 
     * @param string|null $session_id ID de session (null = session courante)
     * @param bool $check_ip Vérifier également l'IP
     * @return array|false Les données de session ou false si invalide
     */
    public function validate_session($session_id = null, $check_ip = true) {
        if ($session_id === null) {
            $session_id = session_id();
        }

        $session = $this->_get_one([
            'session_id' => $session_id,
            'is_active'  => 1
        ]);

        if (!$session) {
            return false;
        }

        // Vérifier l'expiration
        if (strtotime($session['expiry_time']) < time()) {
            $this->end_user_session($session_id, 'expired');
            return false;
        }

        // Vérifier l'IP si demandé
        if ($check_ip && $session['ip_address'] != $this->input->ip_address()) {
            $this->end_user_session($session_id, 'force');
            return false;
        }

        return $session;
    }

    /**
     * Récupère les sessions actives d'un utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @return array
     */
    public function get_user_active_sessions($user_id) {
        return $this->_get_many(
            ['user_id' => $user_id, 'is_active' => 1],
            'last_activity',
            'DESC'
        );
    }

    /**
     * Récupère une session par son ID (quel que soit son état)
     * 
     * @param int $id ID de la session dans la table
     * @return array|null
     */
    public function get_session_by_id($id) {
        return $this->_get_one(['id' => $id]);
    }

    /**
     * Récupère une session par son session_id (quel que soit son état)
     * 
     * @param string $session_id
     * @return array|null
     */
    public function get_session_by_session_id($session_id) {
        return $this->_get_one(['session_id' => $session_id]);
    }

    /**
     * Récupère l'historique des connexions d'un utilisateur
     * 
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function get_user_session_history($user_id, $limit = 20) {
        return $this->_get_many(
            ['user_id' => $user_id],
            'login_time',
            'DESC',
            $limit
        );
    }

    /**
     * Compte le nombre de sessions actives d'un utilisateur
     * 
     * @param int $user_id
     * @return int
     */
    public function count_active_sessions($user_id) {
        return $this->db
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->count_all_results('user_sessions');
    }

    /**
     * Nettoie les sessions expirées (les marque comme inactives)
     * Peut être appelé périodiquement (cron)
     * 
     * @return int Nombre de sessions affectées
     */
    public function clean_expired_sessions() {
        $this->db->where('expiry_time <', date('Y-m-d H:i:s'))
                 ->where('is_active', 1)
                 ->update('user_sessions', [
                     'is_active'     => 0,
                     'logout_time'   => date('Y-m-d H:i:s'),
                     'logout_reason' => 'expired'
                 ]);
        return $this->db->affected_rows();
    }

    /**
     * Supprime physiquement les sessions inactives plus anciennes qu'une date
     * (pour nettoyage profond)
     * 
     * @param string $date Date limite (format Y-m-d H:i:s)
     * @return int Nombre de suppressions
     */
    public function delete_old_sessions($date) {
        $this->db->where('is_active', 0)
                 ->where('logout_time <', $date)
                 ->delete('user_sessions');
        return $this->db->affected_rows();
    }
}