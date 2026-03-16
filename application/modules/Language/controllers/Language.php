<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends MY_Controller {
    public function switch_lang($lang = 'french') {
        // Détermine le code Google Translate
        $code = 'fr'; // défaut français
        if ($lang == 'english') $code = 'en';
        if ($lang == 'swahili') $code = 'sw';
        
        // Définit le cookie googtrans (valable 1 an, sur tout le site)
        setcookie('googtrans', '/fr/' . $code, time() + 365*24*3600, '/');
        
        // Optionnel : enregistre aussi en session pour d'autres usages
        $this->session->set_userdata('site_lang', $lang);
        
        // Redirige vers la page précédente
        redirect($_SERVER['HTTP_REFERER']);
    }
}