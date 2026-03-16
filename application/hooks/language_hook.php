<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function load_user_language() {
    // Récupérer l'instance de CodeIgniter
    $CI =& get_instance();
    
    // Déterminer la langue à partir de la session (par défaut 'french')
    $lang = $CI->session->userdata('site_lang') ?: 'french';
    
    // Charger le fichier de langue (par exemple 'common_lang.php')
    $CI->lang->load('common', $lang);
}