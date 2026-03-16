<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MaintenanceHook {
    
    private $CI;
    private $allowed_routes = array();
    
    public function __construct() {
        $this->CI =& get_instance();
    }
    
    public function check_maintenance() {
        // Charger le modèle pour accéder aux settings
        $this->CI->load->model('Model');
        
        // Récupérer le statut maintenance depuis la BDD
        $is_maintenance = $this->CI->Model->get_setting('site_maintenance', 'false');
        
        // Convertir en booléen
        $maintenance_mode = ($is_maintenance === 'true' || $is_maintenance === '1' || $is_maintenance === true);
        
        // Si maintenance désactivée, ne rien faire
        if (!$maintenance_mode) {
            return;
        }
        
        // Charger les routes autorisées avec valeur par défaut
        $this->allowed_routes = array('Admin', 'assets', 'css', 'js', 'images', 'uploads');
        
        $current_uri = $this->CI->uri->uri_string();
        $current_url = current_url();
        
        // Vérifier si la route actuelle est autorisée
        if ($this->is_route_allowed($current_uri, $current_url)) {
            return;
        }
        
        // Vérifier utilisateur connecté autorisé
        $this->CI->load->library('session');
        $username = $this->CI->session->userdata('username');
        $role = $this->CI->session->userdata('role');
        
        $allowed_users = array('admin');
        
        if ($username && in_array($username, $allowed_users)) {
            return;
        }
        
        if ($role && in_array($role, $allowed_users)) {
            return;
        }
        
        // Bloquer et afficher maintenance
        $this->show_maintenance_page();
    }
    
    /**
     * Vérifie si l'URI actuel est dans les routes autorisées
     */
    private function is_route_allowed($uri, $full_url) {
        $uri = trim($uri, '/');
        
        foreach ($this->allowed_routes as $route) {
            $route = trim($route, '/');
            
            // Correspondance exacte (insensible à la casse)
            if (strtolower($uri) === strtolower($route)) {
                return TRUE;
            }
            
            // Correspondance partielle (pour les dossiers comme assets/)
            if (stripos($uri, $route . '/') === 0 || $uri === $route) {
                return TRUE;
            }
        }
        
        // Vérifier les requêtes AJAX
        if ($this->CI->input->is_ajax_request()) {
            $referer = $this->CI->input->server('HTTP_REFERER');
            if ($referer && $this->is_url_allowed($referer)) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Vérifie si une URL est autorisée
     */
    private function is_url_allowed($url) {
        foreach ($this->allowed_routes as $route) {
            if (stripos($url, $route) !== FALSE) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Affiche la page de maintenance
     */
    private function show_maintenance_page() {
        $this->CI->output->set_status_header(503);
        
        // Récupération du logo
        $logo_path = base_url('attachments/Configurations/' . $this->CI->Model->get_setting('favicon_ico', 'assets/fro.png'));
        $login_url = site_url('Admin');
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUFOTEC - Maintenance Mode</title>
    <link rel="icon" href="' . $logo_path . '">
    <link rel="apple-touch-icon" href="' . $logo_path . '">
    <style>
        :root {
            --primary: #0f4c3a;
            --primary-dark: #0a3326;
            --primary-light: #1a5f4a;
            --primary-lighter: #e8f5f0;
            --accent: #d4af37;
            --accent-hover: #b8941f;
            --accent-light: #faf6e9;
            --light: #f8faf9;
            --dark: #1a1a1a;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            background-color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            line-height: 1.6;
        }

        .maintenance-card {
            max-width: 600px;
            margin: 2rem;
            padding: 3rem 2.5rem;
            background-color: var(--white);
            border-radius: 2rem;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid var(--gray-light);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            margin-bottom: 2rem;
        }

        .logo img {
            max-width: 120px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background-color: var(--primary-lighter);
            color: var(--primary-dark);
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            border: 1px solid var(--primary-light);
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .message {
            color: var(--gray);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .admin-section {
            margin-top: 2rem;
            padding: 2rem;
            border-top: 2px solid var(--gray-light);
        }

        .admin-label {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 2.5rem;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            box-shadow: 0 4px 12px rgba(15,76,58,0.2);
        }

        .btn-admin:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15,76,58,0.3);
        }

        .btn-admin:active {
            transform: translateY(0);
        }

        .footer {
            margin-top: 2.5rem;
            font-size: 0.85rem;
            color: var(--gray);
        }

        @media (max-width: 600px) {
            .maintenance-card {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .logo img {
                max-width: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="logo">
            <img src="' . $logo_path . '" alt="NUFOTEC Logo" onerror="this.src=\'' . base_url('assets/fro.png') . '\'">
        </div>

        <div class="badge">🔧 System Maintenance</div>

        <h1>We will be back soon</h1>

        <p class="message">
            Our website is currently undergoing scheduled maintenance<br>
            to improve your experience. Thank you for your patience.
        </p>

        <div class="footer">
            &copy; ' . date('Y') . ' NUFOTEC. All rights reserved.
        </div>
    </div>
</body>
</html>';
        
        echo $html;
        exit;
    }
}