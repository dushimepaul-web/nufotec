<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function load_user_language() {
    // Récupérer l'instance de CodeIgniter
    $CI =& get_instance();

    // Déterminer la langue depuis la session (par défaut 'fr')
    $raw = $CI->session->userdata('site_lang');
    $map = array(
        'french' => 'fr', 'francais' => 'fr', 'fr' => 'fr',
        'english' => 'en', 'anglais' => 'en', 'en' => 'en',
        'kiswahili' => 'sw', 'swahili' => 'sw', 'sw' => 'sw'
    );
    $idiom = isset($map[$raw]) ? $map[$raw] : 'fr';

    // Appliquer l'idiome de langue et charger le fichier de langue
    $CI->config->set_item('language', $idiom);
    $CI->lang->load('site_lang', $idiom);
}