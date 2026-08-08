<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modèle Générique - African Green Farmers
 * 
 * Ce modèle fournit des méthodes CRUD génériques et des utilitaires
 * pour toutes les tables de la base de données.
 * 
 * @package     AfricanGreenFarmers
 * @subpackage  Models
 * @category    Core
 * @author      AGF Development Team
 * @version     2.0.0
 */
class Model extends CI_Model {

    /**
     * Configuration par défaut
     */
    protected $default_table = '';
    protected $primary_key = 'id';
    protected $soft_delete_key = 'deleted_at';
    protected $created_at_key = 'created_at';
    protected $updated_at_key = 'updated_at';
    
    /**
     * Constructeur
     */
    public function __construct() {
    parent::__construct();
    $this->load->library('user_agent');
    $this->load->helper('url');
    $this->load->driver('cache', array('adapter' => 'file')); // AJOUTER CETTE LIGNE
    
    // Définir le fuseau horaire
    date_default_timezone_set('Africa/Bujumbura');
}

    // ------------------------------------------------------------------------
    // OPÉRATIONS CRUD GÉNÉRIQUES
    // ------------------------------------------------------------------------

    /**
     * Crée un nouvel enregistrement
     * 
     * @param string $table Nom de la table
     * @param array $data Données à insérer
     * @param bool $return_id Retourner l'ID créé
     * @return mixed bool|int
     */
    public function create($table, $data, $return_id = false) {
        // Ajout automatique de la date de création si la colonne existe
        if ($this->db->field_exists($this->created_at_key, $table) && !isset($data[$this->created_at_key])) {
            $data[$this->created_at_key] = date('Y-m-d H:i:s');
        }
        
        $query = $this->db->insert($table, $data);
        
        if ($query) {
            return $return_id ? $this->db->insert_id() : true;
        }
        
        log_message('error', "Create failed on table {$table}: " . $this->db->error()['message']);
        return false;
    }


     public function get_user_by_id($id) {
        return $this->db->where('id', $id)->get('users')->row_array();
    }

    /**
     * Crée un enregistrement et retourne l'ID
     * 
     * @param string $table Nom de la table
     * @param array $data Données à insérer
     * @return int|false ID créé ou false
     */
    public function create_last_id($table, $data) {
        return $this->create($table, $data, true);
}



public function getConsultationById($id) {
    $this->db->select('c.*, u.nom as patient_nom, u.prenom as patient_prenom, u.email as patient_email');
    $this->db->from('consultations c');
    $this->db->join('users u', 'u.id = c.patient_id', 'left');
    $this->db->where('c.id', $id);
    $query = $this->db->get();
    return $query->row_array();
}

/**
 * Récupérer une consultation par son numéro de consultation
 * @param string $numero Numéro de consultation
 * @return array|false
 */
/**
 * Récupérer une consultation par son numéro avec toutes les informations
 * @param string $numero Numéro de consultation
 * @return array|false
 */
/**
 * Récupérer une consultation par son numéro avec toutes les informations
 * @param string $numero Numéro de consultation
 * @return array|false
 */
public function getConsultationByNumber($numero) {
    $this->db->select('c.*, 
                       u.nom as patient_nom, 
                       u.prenom as patient_prenom, 
                       u.email as patient_email,
                       u.telephone as patient_telephone,
                       p.pays as pays_nom,
                       p.id_country as pays_code');
    $this->db->from('consultations c');
    $this->db->join('users u', 'u.id = c.patient_id', 'left');
    $this->db->join('pays p', 'p.id = c.country_id', 'left');  // Utilisez country_id
    $this->db->where('c.numero_consultation', $numero);
    $query = $this->db->get();
    return $query->row_array();
}

/**
 * Récupérer un pays par son nom
 * @param string $nom Nom du pays
 * @return array|false
 */
public function getPaysByName($nom) {
    $this->db->select('id, id_country, pays');
    $this->db->where('pays', $nom);
    $query = $this->db->get('pays');
    return $query->row_array();
}

/**
 * Vérifier s'il y a une consultation en attente de paiement pour un patient
 * @param int $patient_id ID du patient
 * @return array|false
 */
public function getPendingConsultationByPatient($patient_id) {
    $this->db->select('id, numero_consultation, prix_ht, devise, paiement_statut, created_at');
    $this->db->from('consultations');
    $this->db->where('patient_id', $patient_id);
    $this->db->where('paiement_statut', 'en_attente');
    $this->db->order_by('created_at', 'DESC');
    $this->db->limit(1);
    $query = $this->db->get();
    return $query->row_array();
}

/**
 * Récupérer un médecin par son ID avec toutes les informations
 * @param int $id ID du médecin
 * @return array|false
 */
public function getDoctorById($id) {
    $this->db->select('m.*, u.nom, u.prenom, u.email, u.telephone, u.photo');
    $this->db->from('medecins m');
    $this->db->join('users u', 'u.id = m.user_id', 'left');
    $this->db->where('m.id', $id);
    $query = $this->db->get();
    return $query->row_array();
}


/**
 * Récupérer un utilisateur par son ID
 */
public function getUserById($id) {
    $this->db->select('id, nom, prenom, email, telephone');
    $this->db->where('id', $id);
    $query = $this->db->get('users');
    return $query->row_array();
}

/**
 * Récupérer les modes de paiement actifs
 */
public function getActivePaymentMethods() {
    $this->db->where('est_actif', 1);
    $this->db->order_by('id_mode_payement', 'ASC');
    $query = $this->db->get('mode_payement');
    return $query->result_array();
}
    /**
     * Insertion multiple (batch)
     * 
     * @param string $table Nom de la table
     * @param array $data Tableau de données à insérer
     * @return bool
     */
    public function create_batch($table, $data) {
        if (empty($data)) {
            return false;
        }
        
        // Ajout automatique de la date de création pour tous les éléments
        if ($this->db->field_exists($this->created_at_key, $table)) {
            foreach ($data as &$row) {
                if (!isset($row[$this->created_at_key])) {
                    $row[$this->created_at_key] = date('Y-m-d H:i:s');
                }
            }
        }
        
        $query = $this->db->insert_batch($table, $data);
        
        if (!$query) {
            log_message('error', "Batch insert failed on table {$table}: " . $this->db->error()['message']);
        }
        
        return (bool) $query;
    }

    // ------------------------------------------------------------------------






public function get_products_translated($lang, $limit = null, $offset = 0)
{
    $this->db->select("id, main_image, title, description, slug, price, created_at");
    $this->db->where('is_active', 1);
    $this->db->where('deleted_at IS NULL');
    $this->db->order_by('id', 'DESC');
    if ($limit) $this->db->limit($limit, $offset);
    return $this->db->get('advertise_product')->result_array();
}







    function readOne($table, $criteres) {
        $this->db->where($criteres);
        $query = $this->db->get($table);
        return $query->row_array();
    }
    /**
     * Lit des enregistrements avec filtres optionnels
     * 
     * @param string $table Nom de la table
     * @param array $where Conditions WHERE
     * @param string $order_by Colonne de tri
     * @param string $order Sens du tri (ASC|DESC)
     * @param int $limit Limite
     * @param int $offset Offset
     * @param bool $include_deleted Inclure les soft-deleted
     * @return array
     */
   public function read($table, $where = [], $order_by = null, $order = 'DESC', $limit = null, $offset = 0, $include_deleted = false) {
    // Gestion du soft delete
    if (!$include_deleted && $this->db->field_exists($this->soft_delete_key, $table)) {
        $this->db->where($this->soft_delete_key, null);
    }

    // Conditions WHERE : gestion des clauses IN si la valeur est un tableau
    if (!empty($where)) {
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $this->db->where_in($key, $value); // Pour les clauses IN
                } else {
                    $this->db->where($key, $value);   // Pour les égalités simples
                }
            }
        } else {
            $this->db->where($where); // Si c'est une chaîne SQL brute
        }
    }

    // Tri
    if ($order_by !== null) {
        $this->db->order_by($order_by, $order);
    }

    // Limite
    if ($limit !== null) {
        $this->db->limit($limit, $offset);
    }

    $query = $this->db->get($table);
    return $query->result_array();
}

    /**
     * Lit un seul enregistrement
     * 
     * @param string $table Nom de la table
     * @param mixed $where Conditions WHERE ou valeur de la clé primaire
     * @param bool $include_deleted Inclure les soft-deleted
     * @return array|null
     */
    public function read_one($table, $where, $include_deleted = false) {
        // Si $where est un scalaire, on suppose que c'est la valeur de la clé primaire
        if (!is_array($where)) {
            $where = [$this->primary_key => $where];
        }
        
        // Gestion du soft delete
        if (!$include_deleted && $this->db->field_exists($this->soft_delete_key, $table)) {
            $this->db->where($this->soft_delete_key, null);
        }
        
        $query = $this->db->get_where($table, $where);
        return $query->row_array();
    }

    /**
     * Lit des enregistrements avec une liste d'IDs
     * 
     * @param string $table Nom de la table
     * @param array $ids Liste des IDs
     * @param string $id_field Nom du champ ID
     * @return array
     */
    public function read_where_in($table, $ids = [], $id_field = 'id') {
        if (empty($ids)) {
            return [];
        }
        
        // Gestion du soft delete
        if ($this->db->field_exists($this->soft_delete_key, $table)) {
            $this->db->where($this->soft_delete_key, null);
        }
        
        $this->db->where_in($id_field, $ids);
        $query = $this->db->get($table);
        return $query->result_array();
    }

    /**
     * Lit des enregistrements avec une limite
     * 
     * @param string $table Nom de la table
     * @param int $limit Limite
     * @param string $order_by Colonne de tri
     * @param string $order Sens du tri
     * @return array
     */
    public function read_limit($table, $limit, $order_by = null, $order = 'DESC') {
        return $this->read($table, [], $order_by, $order, $limit);
    }

    // ------------------------------------------------------------------------

    /**
     * Met à jour des enregistrements
     * 
     * @param string $table Nom de la table
     * @param mixed $where Conditions WHERE
     * @param array $data Données à mettre à jour
     * @param bool $return_affected Retourner le nombre de lignes affectées
     * @return mixed bool|int
     */
    public function update($table, $where, $data, $return_affected = false) {
        // Ajout automatique de la date de mise à jour si la colonne existe
        if ($this->db->field_exists($this->updated_at_key, $table) && !isset($data[$this->updated_at_key])) {
            $data[$this->updated_at_key] = date('Y-m-d H:i:s');
        }
        
        $this->db->where($where);
        $query = $this->db->update($table, $data);
        
        if ($query) {
            return $return_affected ? $this->db->affected_rows() : true;
        }
        
        log_message('error', "Update failed on table {$table}: " . $this->db->error()['message']);
        return false;
    }



function readQuery($query,$bindings = null){
      if (!is_null($bindings) && !empty($bindings)) {
            $query=$this->db->query($query, $bindings);
        } else {
            $query=$this->db->query($query);
        }

      if ($query) {
         return $query->result_array();
      }
    }

    
    /**
     * Met à jour et retourne l'enregistrement modifié
     * 
     * @param string $table Nom de la table
     * @param mixed $where Conditions WHERE
     * @param array $data Données à mettre à jour
     * @return array|null
     */
    public function update_return_affected($table, $where, $data) {
        if ($this->update($table, $where, $data)) {
            return $this->read_one($table, $where);
        }
        return null;
    }

    /**
     * Met à jour plusieurs enregistrements par liste d'IDs
     * 
     * @param string $table Nom de la table
     * @param array $ids Liste des IDs
     * @param array $data Données à mettre à jour
     * @param string $id_field Nom du champ ID
     * @return bool
     */
    public function update_where_in($table, $ids = [], $data = [], $id_field = 'id') {
        if (empty($ids) || empty($data)) {
            return false;
        }
        
        // Ajout automatique de la date de mise à jour
        if ($this->db->field_exists($this->updated_at_key, $table) && !isset($data[$this->updated_at_key])) {
            $data[$this->updated_at_key] = date('Y-m-d H:i:s');
        }
        
        $this->db->where_in($id_field, $ids);
        return $this->db->update($table, $data);
    }

    /**
     * Met à jour en batch
     * 
     * @param string $table Nom de la table
     * @param array $data Données à mettre à jour
     * @param string $key Clé d'indexation
     * @return bool
     */
    public function update_batch($table, $data, $key = 'id') {
        if (empty($data)) {
            return false;
        }
        
        $query = $this->db->update_batch($table, $data, $key);
        
        if (!$query) {
            log_message('error', "Batch update failed on table {$table}: " . $this->db->error()['message']);
        }
        
        return (bool) $query;
    }

    // ------------------------------------------------------------------------

    /**
     * Supprime un ou des enregistrements
     * 
     * @param string $table Nom de la table
     * @param mixed $where Conditions WHERE
     * @param bool $soft Utiliser le soft delete si disponible
     * @return bool
     */
    public function delete($table, $where, $soft = true) {
        // Soft delete si demandé et si la colonne existe
        if ($soft && $this->db->field_exists($this->soft_delete_key, $table)) {
            return $this->update($table, $where, [
                $this->soft_delete_key => date('Y-m-d H:i:s')
            ]);
        }
        
        // Suppression physique
        $this->db->where($where);
        $query = $this->db->delete($table);
        
        if (!$query) {
            log_message('error', "Delete failed on table {$table}: " . $this->db->error()['message']);
        }
        
        return (bool) $query;
    }

    /**
     * Restaure un enregistrement soft-deleted
     * 
     * @param string $table Nom de la table
     * @param mixed $where Conditions WHERE
     * @return bool
     */
    public function restore($table, $where) {
        if ($this->db->field_exists($this->soft_delete_key, $table)) {
            return $this->update($table, $where, [
                $this->soft_delete_key => null
            ]);
        }
        return false;
    }

    // ------------------------------------------------------------------------
    // REQUÊTES PERSONNALISÉES
    // ------------------------------------------------------------------------

    /**
     * Exécute une requête SQL personnalisée (résultats multiples)
     * 
     * @param string $sql Requête SQL
     * @param array $bindings Paramètres à binder
     * @return array
     */
    public function query($sql, $bindings = null) {
        if (!is_null($bindings) && !empty($bindings)) {
            $query = $this->db->query($sql, $bindings);
        } else {
            $query = $this->db->query($sql);
        }

        if ($query && $query->num_rows() > 0) {
            return $query->result_array();
        }
        
        return [];
    }

    /**
     * Exécute une requête SQL (résultat unique)
     * 
     * @param string $sql Requête SQL
     * @param array $bindings Paramètres à binder
     * @return array|null
     */
    public function query_one($sql, $bindings = null) {
        if (!is_null($bindings) && !empty($bindings)) {
            $query = $this->db->query($sql, $bindings);
        } else {
            $query = $this->db->query($sql);
        }
        
        if ($query && $query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Exécute n'importe quelle requête SQL
     * 
     * @param string $sql Requête SQL
     * @return mixed
     */
    public function execute_query($sql) {
        $query = $this->db->query($sql);
        
        if ($query && $query->num_rows() > 0) {
            return $query->result();
        }
        
        return [];
    }

    // ------------------------------------------------------------------------
    // FONCTIONS UTILITAIRES
    // ------------------------------------------------------------------------

    /**
     * Compte le nombre d'enregistrements
     * 
     * @param string $table Nom de la table
     * @param array $where Conditions WHERE
     * @param bool $include_deleted Inclure les soft-deleted
     * @return int
     */
    public function count($table, $where = [], $include_deleted = false) {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        if (!$include_deleted && $this->db->field_exists($this->soft_delete_key, $table)) {
            $this->db->where($this->soft_delete_key, null);
        }
        
        return $this->db->count_all_results($table);
    }

    /**
     * Vérifie si une valeur existe
     * 
     * @param string $table Nom de la table
     * @param array $where Conditions WHERE
     * @return bool
     */
    public function exists($table, $where) {
        return $this->count($table, $where) > 0;
    }

    /**
     * Vérifie si un email existe (méthode spécifique courante)
     * 
     * @param string $email Email à vérifier
     * @param string $table Table des utilisateurs
     * @return bool
     */
    public function email_exists($email, $table = 'users') {
        return $this->exists($table, ['email' => $email]);
    }

    // ------------------------------------------------------------------------
    // GESTION DES PARAMÈTRES (SETTINGS)
    // ------------------------------------------------------------------------

    /**
     * Récupère la valeur d'un paramètre
     * 
     * @param string $key Clé du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    /**
 * Récupère une configuration par sa clé (avec cache)
 * @param string $key La clé de configuration
 * @param mixed $default Valeur par défaut si non trouvée
 * @return string|null La valeur de configuration
 */
public function get_setting($key, $default = null) {
    // Cache statique pour éviter les requêtes répétées
    static $cache = [];
    
    // Retourner la valeur du cache si elle existe
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    
    $this->db->select('valeur');
    $this->db->from('configurations');
    $this->db->where('cle', $key);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        $value = $query->row()->valeur;
        $result = $value !== null ? $value : $default;
        
        // Mettre en cache
        $cache[$key] = $result;
        
        return $result;
    }

    // Mettre la valeur par défaut en cache
    $cache[$key] = $default;
    return $default;
}

    /**
     * Définit la valeur d'un paramètre
     * 
     * @param string $key Clé du paramètre
     * @param mixed $value Valeur
     * @return bool
     */
    public function set_setting($key, $value) {
        $exists = $this->exists('configurations', ['cle' => $key]);
        
        if ($exists) {
            return $this->update('configurations', ['cle' => $key], ['valeur' => $value]);
        } else {
            return $this->create('configurations', [
                'cle' => $key,
                'valeur' => $value,
                'type' => 'texte',
                'categorie' => 'general'
            ]);
        }
    }

    /**
     * Récupère tous les paramètres sous forme de tableau
     * 
     * @return array
     */
    public function get_all_settings() {
        $settings = $this->read('configurations');
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['cle']] = $setting['valeur'];
        }
        
        return $result;
    }

    // ------------------------------------------------------------------------
    // HISTORIQUE ET LOGS
    // ------------------------------------------------------------------------

    /**
     * Enregistre une action dans l'historique
     * 
     * @param int $user_id ID de l'utilisateur
     * @param string $action Action effectuée
     * @param string $description Description détaillée
     * @return bool
     */
    public function log_history($user_id, $action, $description = '') {
        return $this->create('logs', [
            'user_id' => $user_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'url' => current_url(),
            'method' => $this->input->method(),
            'niveau' => 'info',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // ------------------------------------------------------------------------
    // TRACKING DES VISITEURS
    // ------------------------------------------------------------------------

    /**
     * Enregistre une visite sur le site
     * 
     * @return bool
     */
    public function log_visit() {
        $ip = $this->input->ip_address();
        
        // IP de test pour environnement local
        if (in_array($ip, ['::1', '127.0.0.1'])) {
            $ip = '197.255.128.0'; // IP de test (Afrique)
        }

        $device='Desktop';
        if ($this->agent->is_mobile()) {
            $device='Mobile';
        }
         
        $today = date('Y-m-d');
        $current_page = current_url();

        // Vérifier si la visite existe déjà aujourd'hui
        $existing = $this->read_one('visitors_logs', [
            'ip_address' => $ip,
            'visit_date' => $today,
            'page' => $current_page
        ]);

        if ($existing) {
            return true; // Déjà enregistré aujourd'hui
        }

        // Récupération des données géolocalisées (en cache si possible)
        $geo = $this->get_geolocation($ip);

        return $this->create('visitors_logs', [
            'page' => $current_page,
            'ip_address' => $ip,
            'user_agent' => $this->input->user_agent(),
            'referer' => $this->agent->referrer(),
            'device'=> $device,
            'visit_date' => $today,
            'visit_time' => date('H:i:s')
        ]);
    }










    /**
     * Récupère les données de géolocalisation d'une IP
     * 
     * @param string $ip Adresse IP
     * @return array
     */
    private function get_geolocation($ip) {
        // Vérifier le cache
        $cache_key = 'geo_' . str_replace('.', '_', $ip);
        $cached = $this->cache->get($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }

        // Appel à l'API externe
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "http://ip-api.com/json/{$ip}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $geo = [];

        if ($http_code == 200 && $response) {
            $data = json_decode($response, true);
            $geo = [
                'country' => $data['country'] ?? 'Unknown',
                'city' => $data['city'] ?? 'Unknown',
                'lat' => $data['lat'] ?? null,
                'lon' => $data['lon'] ?? null
            ];
            
            // Mettre en cache pour 24h
            $this->cache->save($cache_key, $geo, 86400);
        }

        return $geo;
    }

    // ------------------------------------------------------------------------
    // UTILITAIRES DIVERS
    // ------------------------------------------------------------------------

    /**
     * Extrait l'ID d'une vidéo YouTube
     * 
     * @param string $url URL de la vidéo
     * @return string|null
     */
    public function get_youtube_id($url) {
        preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
            $url,
            $match
        );
        
        return $match[1] ?? null;
    }

    /**
     * Récupère le nom de la table courante
     * 
     * @return string
     */
    public function get_table() {
        return $this->default_table;
    }

    /**
     * Définit la table par défaut
     * 
     * @param string $table Nom de la table
     * @return self
     */
    public function set_table($table) {
        $this->default_table = $table;
        return $this;
    }

    // ------------------------------------------------------------------------
    // MÉTHODES SPÉCIFIQUES À CONSERVER (adaptées)
    // ------------------------------------------------------------------------

    /**
     * Authentification utilisateur (à garder pour compatibilité)
     * 
     * @deprecated Utiliser un modèle Auth_model dédié
     * @param string $username Nom d'utilisateur/email
     * @param string $password Mot de passe
     * @return array|false
     */
    public function login($username, $password) {
        $user = $this->read_one('users', ['email' => $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Ne pas retourner le hash
            return $user;
        }
        
        return false;
    }


    public function check_email($email){ // it checks if a specific username exist 
      if($email) {
        $sql = 'SELECT * FROM users WHERE email = ?';
        $query = $this->db->query($sql, array($email));
        $result = $query->num_rows();
        return ($result == 1) ? true : false;
      }

      return false;
    }

    /**
     * Récupère les groupes d'un utilisateur (compatibilité)
     * 
     * @deprecated Utiliser un modèle dédié
     * @param int $user_id ID utilisateur
     * @return array|null
     */
    public function get_user_group($user_id) {
        $sql = "SELECT r.* FROM users u 
                INNER JOIN roles r ON r.id = u.role_id 
                WHERE u.id = ?";
        
        return $this->query_one($sql, [$user_id]);
    }

    // ------------------------------------------------------------------------
    // TRANSACTIONS
    // ------------------------------------------------------------------------

    /**
     * Démarre une transaction
     */
    public function begin_transaction() {
        $this->db->trans_begin();
    }

    /**
     * Valide une transaction
     * 
     * @return bool
     */
    public function commit_transaction() {
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        
        $this->db->trans_commit();
        return true;
    }

    /**
     * Annule une transaction
     */
    public function rollback_transaction() {
        $this->db->trans_rollback();
    }

    /**
     * Exécute une fonction dans une transaction
     * 
     * @param callable $callback Fonction à exécuter
     * @return mixed
     * @throws Exception
     */
    public function transaction(callable $callback) {
        $this->begin_transaction();
        
        try {
            $result = $callback($this);
            
            if ($this->commit_transaction()) {
                return $result;
            }
            
            throw new Exception('Transaction failed');
        } catch (Exception $e) {
            $this->rollback_transaction();
            log_message('error', 'Transaction error: ' . $e->getMessage());
            throw $e;
        }
    }











public function create_user_session($user_id, $session_id = null) {
    $this->load->library('user_agent');
    
    if ($session_id === null) {
        $session_id = session_id();
    }

    // Détection améliorée
    if ($this->agent->is_mobile()) {
        $device_type = 'mobile';
    } elseif ($this->agent->is_robot()) {
        $device_type = 'bot';
    } elseif ($this->agent->is_tablet()) { // nécessite une librairie
        $device_type = 'tablet';
    } else {
        $device_type = 'desktop';
    }

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
        'expiry_time'   => date('Y-m-d H:i:s', strtotime('+24 hours')),
        'is_active'     => 1
    ];

    // Désactiver les anciennes sessions actives
    $this->db->where('user_id', $user_id)
             ->where('is_active', 1)
             ->update('user_sessions', [
                 'is_active'     => 0,
                 'logout_time'   => date('Y-m-d H:i:s'),
                 'logout_reason' => 'force'
             ]);

    $this->db->insert('user_sessions', $data);
    return $this->db->insert_id();
}









    // ------------------------------------------------------------------------
    // GESTION DE LA DOUBLE AUTHENTIFICATION (2FA)
    // ------------------------------------------------------------------------

    /**
     * Active ou désactive la 2FA pour un utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @param bool $enable Activer ou désactiver
     * @param string $secret Secret 2FA (optionnel)
     * @return bool
     */
    public function toggle_two_factor($user_id, $enable = true, $secret = null) {
        $data = [
            'two_factor_enabled' => $enable ? 1 : 0
        ];
        
        if ($enable && $secret) {
            $data['two_factor_secret'] = $secret;
        } elseif (!$enable) {
            $data['two_factor_secret'] = null;
        }
        
        return $this->update('users', ['id' => $user_id], $data);
    }

    /**
     * Vérifie si un utilisateur a la 2FA active
     * 
     * @param int $user_id ID de l'utilisateur
     * @return bool
     */
    public function has_two_factor_enabled($user_id) {
        $user = $this->read_one('users', ['id' => $user_id]);
        return $user && $user['two_factor_enabled'] == 1;
    }

    /**
     * Récupère le secret 2FA d'un utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @return string|null
     */
    public function get_two_factor_secret($user_id) {
        $user = $this->read_one('users', ['id' => $user_id]);
        return $user ? $user['two_factor_secret'] : null;
    }

    // ------------------------------------------------------------------------
    // SUIVI DES CONNEXIONS (LOGIN HISTORY)
    // ------------------------------------------------------------------------

    /**
     * Enregistre une tentative de connexion
     * 
     * @param string $email Email utilisé
     * @param bool $success Succès ou échec
     * @param int|null $user_id ID utilisateur si succès
     * @return bool
     */
    public function log_login_attempt($email, $success = false, $user_id = null) {
        // Mettre à jour last_login_at et last_login_ip si succès
        if ($success && $user_id) {
            $this->update('users', ['id' => $user_id], [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => $this->input->ip_address()
            ]);
        }
        
        // Journaliser la tentative
        return $this->create('logs', [
            'user_id' => $user_id,
            'action' => $success ? 'login_success' : 'login_failed',
            'description' => "Tentative de connexion avec l'email: {$email}",
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'url' => current_url(),
            'method' => $this->input->method(),
            'niveau' => $success ? 'info' : 'warning'
        ]);
    }

    /**
     * Récupère l'historique des connexions d'un utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @param int $limit Limite de résultats
     * @return array
     */
    public function get_user_login_history($user_id, $limit = 10) {
        return $this->read('user_sessions', 
            ['user_id' => $user_id],
            'login_time', 'DESC',
            $limit
        );
    }

    // ------------------------------------------------------------------------
    // GESTION DES EMAILS DE VÉRIFICATION
    // ------------------------------------------------------------------------

    /**
     * Génère un token de vérification d'email
     * 
     * @param int $user_id ID de l'utilisateur
     * @return string|false Token généré ou false
     */
    public function generate_email_verification_token($user_id) {
        $token = bin2hex(random_bytes(32));
        
        $updated = $this->update('users', ['id' => $user_id], [
            'email_verification_token' => $token,
            'email_verified_at' => null
        ]);
        
        return $updated ? $token : false;
    }

    /**
     * Vérifie un token de vérification d'email
     * 
     * @param string $token Token à vérifier
     * @return array|false Utilisateur concerné ou false
     */
    public function verify_email_token($token) {
        $user = $this->read_one('users', ['email_verification_token' => $token]);
        
        if ($user) {
            $this->update('users', ['id' => $user['id']], [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'email_verification_token' => null
            ]);
            return $user;
        }
        
        return false;
    }

    /**
     * Vérifie si l'email d'un utilisateur est vérifié
     * 
     * @param int $user_id ID de l'utilisateur
     * @return bool
     */
    public function is_email_verified($user_id) {
        $user = $this->read_one('users', ['id' => $user_id]);
        return $user && $user['email_verified_at'] !== null;
    }

    // ------------------------------------------------------------------------
    // GESTION DU STATUT UTILISATEUR
    // ------------------------------------------------------------------------

    /**
     * Active ou désactive un compte utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @param bool $activate Activer ou désactiver
     * @return bool
     */
    public function toggle_user_status($user_id, $activate = true) {
        return $this->update('users', ['id' => $user_id], [
            'is_active' => $activate ? 1 : 0
        ]);
    }

    /**
     * Vérifie si un compte utilisateur est actif
     * 
     * @param int $user_id ID de l'utilisateur
     * @return bool
     */
    public function is_user_active($user_id) {
        $user = $this->read_one('users', ['id' => $user_id, 'is_active' => 1]);
        return !empty($user);
    }

    /**
     * Soft delete d'un utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @return bool
     */
    public function soft_delete_user($user_id) {
        // Terminer toutes les sessions actives
        $this->end_all_user_sessions($user_id, 'force');
        
        return $this->update('users', ['id' => $user_id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'is_active' => 0
        ]);
    }

    /**
     * Restaure un utilisateur soft-deleted
     * 
     * @param int $user_id ID de l'utilisateur
     * @return bool
     */
    public function restore_user($user_id) {
        return $this->update('users', ['id' => $user_id], [
            'deleted_at' => null,
            'is_active' => 1
        ]);
    }

    // ------------------------------------------------------------------------
    // STATISTIQUES DE SESSIONS
    // ------------------------------------------------------------------------

    /**
     * Récupère les statistiques des sessions actives
     * 
     * @return array
     */
    public function get_session_stats() {
        $stats = [];
        
        // Nombre total de sessions actives
        $stats['total_active'] = $this->db->where('is_active', 1)
                                          ->count_all_results('user_sessions');
        
        // Sessions par appareil
        $devices = $this->db->select('device_type, COUNT(*) as count')
                            ->where('is_active', 1)
                            ->group_by('device_type')
                            ->get('user_sessions')
                            ->result_array();
        
        $stats['by_device'] = [];
        foreach ($devices as $device) {
            $stats['by_device'][$device['device_type']] = $device['count'];
        }
        
        // Sessions par navigateur
        $browsers = $this->db->select('browser, COUNT(*) as count')
                             ->where('is_active', 1)
                             ->where('browser IS NOT NULL')
                             ->group_by('browser')
                             ->get('user_sessions')
                             ->result_array();
        
        $stats['by_browser'] = [];
        foreach ($browsers as $browser) {
            $stats['by_browser'][$browser['browser']] = $browser['count'];
        }
        
        return $stats;
    }

    /**
     * Récupère les utilisateurs actuellement en ligne
     * 
     * @param int $minutes Délai d'inactivité considéré comme "en ligne"
     * @return array
     */
  public function get_online_users($minutes = 15) {
    $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
    
    $this->db->select('user_id')
             ->distinct()
             ->from('user_sessions')
             ->where('is_active', 1)
             ->where('last_activity >=', $since);
    
    return $this->db->get()->result_array();
}






    /**
     * Ajouter un produit au panier ligne
     */







































    /*<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categorie_model extends CI_Model {*   utulisateur*/

    public function get_categories_actives() {
        return $this->db
            ->where('est_actif', 1)
            ->order_by('ordre', 'ASC')
            ->get('categories')
            ->result();
    }

    public function get_categorie_by_slug($slug) {
        return $this->db->where('slug', $slug)->get('categories')->row();
    }



    public function get_all_configs() {
        $query = $this->db->get('configurations');
        $configs = [];
        foreach($query->result() as $row) {
            $configs[$row->cle] = $row->valeur;
        }
        return $configs;
    }


 



 

    /**
     * Récupère les médecins disponibles
     */
    public function get_medecins_disponibles($limit = 4) {
        $this->db->select('medecins.*, users.nom, users.prenom, users.photo');
        $this->db->from('medecins');
        $this->db->join('users', 'users.id = medecins.user_id');
        $this->db->where('medecins.est_disponible', 1);
        $this->db->order_by('medecins.note_moyenne', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        
        return ($query->num_rows() > 0) ? $query->result() : [];
    }


    public function getDoctorByUUID($uuid) {
    $this->db->select('
        medecins.*, 
        users.nom, 
        users.prenom, 
        users.email, 
        users.telephone, 
        users.photo, 
        users.is_active, 
        users.est_verifie
    ');
    $this->db->from('medecins');
    $this->db->join('users', 'users.id = medecins.user_id'); // jointure sur table users
    $this->db->where('medecins.uuid', $uuid); // filtrer par UUID
    $this->db->where('users.is_active', 1);   // facultatif : uniquement utilisateurs actifs
    $this->db->limit(1);

    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row_array(); // retourne un tableau associatif avec toutes les infos
    }

    return null; // aucun médecin trouvé
}

}
