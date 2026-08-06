<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Instance CodeIgniter
 */
if (!function_exists('ci')) {
    function ci()
    {
        return get_instance();
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        $CI = ci();
        return ($CI->session->userdata('logged_in') === true);
    }
}

/**
 * Vérifie si l'utilisateur est admin, sinon redirige
 * @return bool
 */
if (!function_exists('is_admin')) {
    function is_admin()
    {
        $CI = ci();

        // 1. Vérifier connexion (redirige si non connecté)
        if (!is_logged_in()) {
            redirect(base_url('Admin'));
            exit;
        }

        $user_id = $CI->session->userdata('user_id');
        $role_slug = $CI->session->userdata('role_slug');

        // 2. Vérification rapide par session (performance)
        if ($role_slug === 'admin') {
            return true;
        }

        // 3. Double vérification en base (sécurité si session compromise)
        $user = $CI->db
            ->where('id', $user_id)
            ->get('users')
            ->row_array();

        // Si utilisateur existe ET est admin en DB → OK
        if ($user && $user['type_utilisateur'] === 'admin') {
            return true;
        }

        // 4. Sinon → redirection (pas les droits)
        redirect(base_url('Admin'));
        exit;
    }
}



/**
 * Vérifie si l'utilisateur est admin (pour les vues uniquement)
 * Ne redirige pas, ne déconnecte pas, retourne juste true/false
 * @return bool
 */
if (!function_exists('admin_view')) {
    function admin_view()
    {
        $CI = ci();

        // 1. Vérifier si connecté
        if (!is_logged_in()) {
            return false;  // ← Pas de redirect, juste false
        }

        $user_id = $CI->session->userdata('user_id');
        $role_slug = $CI->session->userdata('role_slug');

        // 2. Vérification rapide par session
        if ($role_slug === 'admin') {
            return true;
        }

        // 3. Vérification en base (sécurité)
        $user = $CI->db
            ->where('id', $user_id)
            ->get('users')
            ->row_array();

        // Retourne true seulement si admin, sinon false
        return ($user && $user['type_utilisateur'] === 'admin');
    }
}



if (!function_exists('medecin_view')) {
    function medecin_view()
    {
        $CI = ci();

        // 1. Vérifier si connecté
        if (!is_logged_in()) {
            return false;  // ← Pas de redirect, juste false
        }

        $user_id = $CI->session->userdata('user_id');
        $role_slug = $CI->session->userdata('role_slug');

        // 2. Vérification rapide par session
        if ($role_slug === 'medecin') {
            return true;
        }

        // 3. Vérification en base (sécurité)
        $user = $CI->db
            ->where('id', $user_id)
            ->get('users')
            ->row_array();

        // Retourne true seulement si admin, sinon false
        return ($user && $user['type_utilisateur'] === 'medecin');
    }
}





if (!function_exists('patient_view')) {
    /**
     * Vérifie si l'utilisateur connecté a au moins une consultation.
     * Si ce n'est pas le cas, redirige vers la page d'accueil.
     *
     * @return void
     */
    function patient_view() {
        $CI =& get_instance();

        // 1. Vérifier si l'utilisateur est connecté
        if (!$CI->session->userdata('logged_in')) {
            redirect('/');
            return;
        }

        $user_id = $CI->session->userdata('user_id');

        // 2. Vérifier l'existence d'au moins une consultation pour cet utilisateur
        $consultation = $CI->db
            ->select('id')
            ->where('patient_id', $user_id)
            ->limit(1)
            ->get('consultations')
            ->row();

        if (empty($consultation)) {
            redirect('/');
        }
    }
}


if (!function_exists('is_medecin')) {
    function is_medecin()
    {
        $CI = ci();

        // 1. Vérifie connexion d'abord
        if (!is_logged_in()) {
            redirect(base_url('Admin'));
            exit;
        }

        // 2. Vérifie le rôle en session
        $role_slug = $CI->session->userdata('role_slug');
        if ($role_slug === 'medecin') {
            return true;
        }

        // 3. Double vérification en DB (sécurité supplémentaire)
        $user_id = $CI->session->userdata('user_id');
        $user = $CI->db
            ->where('id', $user_id)
            ->get('users')
            ->row_array();

        if ($user && $user['type_utilisateur'] === 'medecin') {
            return true;
        }

        // 4. Ni l'un ni l'autre → redirection
        redirect(base_url('Admin'));
        exit;
    }
}
/**
 * Vérifie si l'utilisateur est patient, sinon redirige
 */
if (!function_exists('is_patient')) {
    function is_patient()
    {
        $CI = ci();

        if (!is_logged_in()) {
            redirect(base_url('Admin'));
            exit;
        }

        $role_slug = $CI->session->userdata('role_slug');
        if ($role_slug === 'patient') {
            return true;
        }

        $user_id = $CI->session->userdata('user_id');
        $user = $CI->db
            ->where('id', $user_id)
            ->get('users')
            ->row_array();

        if ($user && $user['type_utilisateur'] === 'patient') {
            return true;
        }

        redirect(base_url('Admin'));
        exit;
    }
}/**
 * Vérifie si l'utilisateur a l'un des rôles donnés, sinon redirige
 * @param string|array $roles
 * @return bool
 */
if (!function_exists('require_role')) {
    function require_role($roles)
    {
        $CI = ci();

        if (!is_logged_in()) {
            redirect(base_url('Admin'));
            exit;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $role_slug = $CI->session->userdata('role_slug');
        // Vérification par le slug en session (le plus rapide)
        if (in_array($role_slug, $roles)) {
            return true;
        }

        // Sinon, vérifier dans la DB
        $user_id = $CI->session->userdata('user_id');
        $user = $CI->db
            ->where('id', $user_id)
            ->get('users')
            ->row_array();

        if (!$user) {
            redirect(base_url('Admin'));
            exit;
        }

        if (in_array($user['type_utilisateur'], $roles)) {
            return true;
        }

        redirect(base_url('Admin'));
        exit;
    }


    /**
 * Vérifie si l'utilisateur peut voir cette consultation
 * @param array $consultation Ligne de consultation
 * @return bool
 */
if (!function_exists('can_view_consultation')) {
    function can_view_consultation($consultation) {
        $CI = ci();
        
        $current_role = $CI->session->userdata('role_slug');
        $current_user_id = $CI->session->userdata('user_id');
        
        // Admin voit tout
        if ($current_role === 'admin') {
            return true;
        }
        
        // Médecin : vérifier medecin_id
        if ($current_role === 'medecin') {
            $medecin = $CI->db->where('user_id', $current_user_id)->get('medecins')->row();
            if (!$medecin) return false;
            
            return ($consultation['medecin_id'] == $medecin->id);
        }
        
        // Patient : vérifier patient_id
        if ($current_role === 'patient') {
            return ($consultation['patient_id'] == $current_user_id);
        }
        
        return false;
    }
}

/**
 * View-only : l'utilisateur connecté est un utilisateur "standard"
 * (patient par défaut, ou broker/investisseur enregistré via la table users)
 * Ne redirige pas — retourne juste true/false.
 */
if (!function_exists('user_dashboard_view')) {
    function user_dashboard_view()
    {
        $CI = ci();
        if (!is_logged_in()) return false;

        $user_id = $CI->session->userdata('user_id');
        if (!$user_id) return false;

        $role_slug = $CI->session->userdata('role_slug');
        if (in_array($role_slug, ['admin', 'medecin'])) return false;

        $type = $CI->session->userdata('type_utilisateur');
        if (in_array($type, ['admin', 'medecin'])) return false;

        $user = $CI->db->select('type_utilisateur')->where('id', $user_id)->get('users')->row_array();
        if ($user && in_array($user['type_utilisateur'], ['admin', 'medecin'])) return false;

        return true;
    }
}

/**
 * View-only : l'utilisateur connecté est aussi dans la table "investors"
 * (par son email ou son nom complet). Ne redirige pas.
 */
if (!function_exists('investor_view')) {
    function investor_view()
    {
        $CI = ci();
        if (!is_logged_in()) return false;

        static $cache = null;
        if ($cache !== null) return $cache;

        $email = $CI->session->userdata('email');
        $fullname = trim(($CI->session->userdata('prenom') ?? '') . ' ' . ($CI->session->userdata('nom') ?? ''));

        if (!$email && !$fullname) return $cache = false;

        $CI->db->group_start();
        if ($email)      $CI->db->where('email', $email);
        if ($email && $fullname) $CI->db->or_where('full_name', $fullname);
        elseif ($fullname) $CI->db->where('full_name', $fullname);
        $CI->db->group_end();

        $found = $CI->db->get('investors', 1)->row();
        return $cache = (bool)$found;
    }
}

/**
 * View-only : l'utilisateur connecté est aussi dans la table "brokers"
 * (par son email ou son nom complet). Ne redirige pas.
 */
if (!function_exists('broker_view')) {
    function broker_view()
    {
        $CI = ci();
        if (!is_logged_in()) return false;

        static $cache = null;
        if ($cache !== null) return $cache;

        $email = $CI->session->userdata('email');
        $fullname = trim(($CI->session->userdata('prenom') ?? '') . ' ' . ($CI->session->userdata('nom') ?? ''));

        if (!$email && !$fullname) return $cache = false;

        $CI->db->group_start();
        if ($email)      $CI->db->where('email', $email);
        if ($email && $fullname) $CI->db->or_where('full_name', $fullname);
        elseif ($fullname) $CI->db->where('full_name', $fullname);
        $CI->db->group_end();

        $found = $CI->db->get('brokers', 1)->row();
        return $cache = (bool)$found;
    }
}

}