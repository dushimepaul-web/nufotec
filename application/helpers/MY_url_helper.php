<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('base_url')) {
    function base_url($uri = '')
    {
        $CI =& get_instance();
        
        // Enlever le préfixe langue (fr, en, sw) du lien
        $uri = preg_replace('#^(fr|en|sw)/#', '', $uri);
        $uri = preg_replace('#^(fr|en|sw)$#', '', $uri);
        
        return $CI->config->slash_item('base_url') . $uri;
    }
}