<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Public_Controller extends MX_Controller
{
    public $data = [];

    public $current_lang;
    public $available_langs = ['fr', 'en', 'sw'];

    public $lang_names = [
        'fr' => ['name' => 'Français', 'flag' => 'fr', 'dir' => 'ltr'],
        'en' => ['name' => 'English', 'flag' => 'us', 'dir' => 'ltr'],
        'sw' => ['name' => 'Kiswahili', 'flag' => 'tz', 'dir' => 'ltr']
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Model');

        $this->_setup_language();

        $this->lang->load('site', $this->current_lang);

        $this->data['menu_items'] = $this->_get_menu();
        $this->data['lang'] = $this->current_lang;
        $this->data['available_langs'] = $this->available_langs;
        $this->data['lang_names'] = $this->lang_names;

        $this->load->vars($this->data);
    }

    /**
     * LANGUAGE SYSTEM STABLE (NO LOOP / NO 404)
     */
    private function _setup_language()
    {
        $uri_lang = $this->uri->segment(1);
        $session_lang = $this->session->userdata('lang');

        // 1. Si aucune langue dans l'URL
        if (empty($uri_lang)) {

            $lang = in_array($session_lang, $this->available_langs)
                ? $session_lang
                : 'fr';

            redirect($lang);
        }

        // 2. Si langue valide dans URL
        if (in_array($uri_lang, $this->available_langs)) {

            $this->current_lang = $uri_lang;
            $this->session->set_userdata('lang', $uri_lang);

            return;
        }

        // 3. Langue invalide → fallback
        $lang = in_array($session_lang, $this->available_langs)
            ? $session_lang
            : 'fr';

        redirect($lang);
    }

    /**
     * MENU
     */
    private function _get_menu()
    {
        $lang = $this->current_lang;

        $pages = $this->Model->read('pages', [
            'est_publiee' => 1,
            'deleted_at' => null
        ], 'menu_ordre', 'ASC');

        $menu = [];

        foreach ($pages as $page) {
            $titre_field = "titre_page_{$lang}";
            $page['titre_page'] = $page[$titre_field]
                ?? $page['titre_page_fr']
                ?? $page['titre_page'];

            $menu[] = $page;
        }

        return $menu;
    }

    /**
     * PAGE
     */
    public function get_page($slug)
    {
        $lang = $this->current_lang;

        $page = $this->Model->read_one('pages', [
            'slug' => $slug,
            'est_publiee' => 1,
            'deleted_at' => null
        ]);

        if (!$page) return null;

        $page['titre'] = $page["titre_page_{$lang}"] ?? $page['titre_page_fr'];
        $page['contenu'] = $page["contenu_page_{$lang}"] ?? '';
        $page['meta_desc'] = $page["meta_description_{$lang}"] ?? '';

        return $page;
    }

    /**
     * SWITCH LANG
     */
    public function switch_lang($new_lang)
    {
        if (!in_array($new_lang, $this->available_langs)) {
            $new_lang = 'fr';
        }

        $this->session->set_userdata('lang', $new_lang);

        $current_uri = $this->uri->uri_string();
        $segments = explode('/', $current_uri);

        if (in_array($segments[0], $this->available_langs)) {
            array_shift($segments);
        }

        $new_uri = implode('/', array_filter($segments));

        redirect(empty($new_uri) ? $new_lang : $new_lang . '/' . $new_uri);
    }

    /**
     * TRANSLATION HELPER
     */
    public function t($key)
    {
        $text = $this->lang->line($key);
        return $text ?: $key;
    }
}

require_once(APPPATH.'core/Backend_Controller.php');
require_once(APPPATH.'core/Frontend_Controller.php');