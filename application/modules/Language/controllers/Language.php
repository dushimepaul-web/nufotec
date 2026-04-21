<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Changer de langue
     * @param string $lang - Code langue: fr, en, ar, sw
     */
    public function switch_lang($lang = 'fr') {
        // Langues disponibles
        $available_langs = ['fr', 'en', 'ar', 'sw'];
        
        // Vérifier si la langue est valide
        if (!in_array($lang, $available_langs)) {
            $lang = 'fr';
        }
        
        // Sauvegarder en session
        $this->session->set_userdata('lang', $lang);
        
        // Optionnel: Sauvegarder en cookie pour Google Translate si nécessaire
        $google_codes = [
            'fr' => 'fr',
            'en' => 'en', 
            'ar' => 'ar',
            'sw' => 'sw'
        ];
        if (isset($google_codes[$lang])) {
            setcookie('googtrans', '/fr/' . $google_codes[$lang], time() + 365*24*3600, '/');
        }
        
        // Récupérer l'URL de redirection
        $redirect_url = $this->input->get('redirect');
        
        if (!empty($redirect_url)) {
            // Rediriger vers l'URL spécifique avec la nouvelle langue
            redirect($lang . '/' . ltrim($redirect_url, '/'));
        } elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            // Remplacer l'ancienne langue par la nouvelle dans l'URL
            $referer = $_SERVER['HTTP_REFERER'];
            foreach ($available_langs as $old_lang) {
                if (strpos($referer, '/' . $old_lang . '/') !== false) {
                    $referer = str_replace('/' . $old_lang . '/', '/' . $lang . '/', $referer);
                    break;
                }
            }
            redirect($referer);
        } else {
            // Redirection par défaut
            redirect($lang);
        }
    }
    
    /**
     * Obtenir la langue actuelle (API)
     */
    public function get_current_lang() {
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode([
                         'lang' => $this->current_lang,
                         'available' => $this->available_langs
                     ]));
    }
}