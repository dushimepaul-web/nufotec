<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CodeIgniter-HMVC
 *
 * @package    CodeIgniter-HMVC
 * @author     N3Cr0N (N3Cr0N@list.ru)
 * @copyright  2019 N3Cr0N
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @link       <URI> (description)
 * @version    GIT: $Id$
 * @since      Version 0.0.1
 * @filesource
 *
 */

class Public_Controller extends MX_Controller
{
    public $CI;

    /**
     * An array of variables to be passed through to the
     * view, layout,....
     */
    public $data = array(); // CHANGÉ : protected → public

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

        // 1. Charger votre modèle générique
        $this->load->model('Model');
        
        // 2. Gestion de la langue
        $this->_setup_language();
        
        // 3. Charger les traductions statiques
        $this->lang->load('site', $this->current_lang);
        
        // 4. Récupérer le menu depuis la table pages (traduit)
        $this->data['menu_items'] = $this->_get_menu();
        
        // 5. Variables globales pour TOUTES les vues
        $this->data['lang'] = $this->current_lang;
        $this->data['available_langs'] = $this->available_langs;
        $this->data['lang_names'] = $this->lang_names;
        
        // 6. Rendre disponible PARTOUT
        $this->load->vars($this->data);
    }
    
    /**
     * Gestion automatique de la langue
     */
    private function _setup_language()
    {
        // Récupérer langue depuis l'URL
        $uri_lang = $this->uri->segment(1);
        
        // Nettoyer la session si elle contient une langue non supportée
        $session_lang = $this->session->userdata('lang');
        if (!in_array($session_lang, $this->available_langs)) {
            $this->session->unset_userdata('lang');
            $session_lang = null;
        }
        
        // Cas 1: Pas de langue dans l'URL ou langue invalide
        if (!in_array($uri_lang, $this->available_langs)) {
            $this->current_lang = (!empty($session_lang) && in_array($session_lang, $this->available_langs)) 
                                ? $session_lang 
                                : 'fr';
            
            // Rediriger vers URL avec langue
            $current_uri = $this->uri->uri_string();
            if (empty($current_uri)) {
                redirect($this->current_lang);
            } else {
                redirect($this->current_lang . '/' . $current_uri);
            }
        } 
        // Cas 2: Langue valide dans l'URL
        else {
            $this->current_lang = $uri_lang;
            $this->session->set_userdata('lang', $this->current_lang);
        }
        
        // Configurer CodeIgniter
        $this->config->set_item('language', $this->current_lang);
    }
    
    /**
     * Récupérer le menu avec les traductions
     * Utilise VOTRE modèle Model
     */
    private function _get_menu()
    {
        $lang = $this->current_lang;
        
        // Utiliser votre modèle générique
        $pages = $this->Model->read('pages', [
            'est_publiee' => 1,
            'deleted_at' => null
        ], 'menu_ordre', 'ASC');
        
        // Transformer pour avoir le titre traduit
        $menu = [];
        foreach ($pages as $page) {
            $titre_field = "titre_page_{$lang}";
            $page['titre_page'] = $page[$titre_field] ?? $page['titre_page_fr'] ?? $page['titre_page'];
            $menu[] = $page;
        }
        
        return $menu;
    }
    
    /**
     * Récupérer une page avec sa traduction
     * Utilise VOTRE modèle Model
     */
    public function get_page($slug)
    {
        $lang = $this->current_lang;
        
        // Récupérer la page
        $page = $this->Model->read_one('pages', [
            'slug' => $slug,
            'est_publiee' => 1,
            'deleted_at' => null
        ]);
        
        if (!$page) {
            return null;
        }
        
        // Ajouter les champs traduits
        $page['titre'] = $page["titre_page_{$lang}"] ?? $page['titre_page_fr'] ?? $page['titre_page'];
        $page['contenu'] = $page["contenu_page_{$lang}"] ?? $page['contenu_page_fr'] ?? '';
        $page['meta_desc'] = $page["meta_description_{$lang}"] ?? $page['meta_description_fr'] ?? '';
        
        return $page;
    }
    
    /**
     * Changer de langue (accessible via URL)
     */
    public function switch_lang($new_lang)
    {
        if (!in_array($new_lang, $this->available_langs)) {
            $new_lang = 'fr';
        }
        
        $this->session->set_userdata('lang', $new_lang);
        
        // Reconstruire l'URL
        $current_uri = $this->uri->uri_string();
        $segments = explode('/', $current_uri);
        
        if (in_array($segments[0], $this->available_langs)) {
            array_shift($segments);
        }
        
        $segments = array_filter($segments);
        $new_uri = implode('/', $segments);
        
        redirect(empty($new_uri) ? $new_lang : $new_lang . '/' . $new_uri);
    }
    
    /**
     * Helper pour traduire les textes statiques
     */
    public function t($key)
    {
        $text = $this->lang->line($key);
        return !empty($text) ? $text : $key;
    }
}

require_once(APPPATH.'core/Backend_Controller.php');
require_once(APPPATH.'core/Frontend_Controller.php');