<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Public_Controller extends MX_Controller
{
    public $data = [];

    public $current_lang = 'fr';
    public $available_langs = ['fr'];

    public $lang_names = [
        'fr' => ['name' => 'Français', 'flag' => 'fr', 'dir' => 'ltr']
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Model');

        $this->data['menu_items'] = $this->_get_menu();
        $this->data['lang'] = 'fr';
        $this->data['available_langs'] = ['fr'];
        $this->data['lang_names'] = $this->lang_names;

        $this->load->vars($this->data);
    }

    /**
     * LANGUAGE SYSTEM - SANS PREFIXE DANS L'URL
     * Version modifiée : pas de redirection, pas de /fr dans l'URL
     */
    private function _setup_language()
    {
        $session_lang = $this->session->userdata('lang');
        $uri_lang = $this->uri->segment(1);
        
        // Vérifier si l'URL contient déjà un préfixe langue (pour compatibilité)
        if (in_array($uri_lang, $this->available_langs)) {
            // Si oui, on garde cette langue mais on ne l'affiche pas dans l'URL
            $this->current_lang = $uri_lang;
            $this->session->set_userdata('lang', $uri_lang);
            return;
        }
        
        // Pas de préfixe dans l'URL : on utilise la session ou la détection auto
        if (in_array($session_lang, $this->available_langs)) {
            $this->current_lang = $session_lang;
        } else {
            // Détection auto depuis le navigateur
            $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fr', 0, 2);
            $this->current_lang = in_array($browser_lang, $this->available_langs) ? $browser_lang : 'fr';
            $this->session->set_userdata('lang', $this->current_lang);
        }
        
        // PAS DE REDIRECTION - l'URL reste sans préfixe
    }

    /**
     * MENU
     */
    private function _get_menu()
    {
        $pages = static_pages_where([
            'est_publiee' => 1,
            'deleted_at' => null
        ], 'menu_ordre', 'ASC');

        $menu = [];

        foreach ($pages as $page) {
            $page['titre_page'] = $page['titre_page'] ?? 'Page';
            $menu[] = $page;
        }

        return $menu;
    }

    /**
     * PAGE
     */
    public function get_page($slug)
    {
        $page = static_pages_one([
            'slug' => $slug,
            'est_publiee' => 1,
            'deleted_at' => null
        ]);

        if (!$page) return null;

        $page['titre'] = $page['titre_page'] ?? '';
        $page['contenu'] = $page['contenu_page'] ?? '';
        $page['meta_desc'] = $page['meta_description'] ?? '';

        return $page;
    }

    /**
     * SWITCH LANG - Change la langue sans perdre la page courante
     * Utilisable depuis n'importe quelle page (Home, Medicins, Boutique, etc.)
     */
    public function switch_lang($new_lang)
    {
        redirect(base_url());
    }

    public function t($key)
    {
        return $key;
    }
}

require_once(APPPATH.'core/Backend_Controller.php');
require_once(APPPATH.'core/Frontend_Controller.php');