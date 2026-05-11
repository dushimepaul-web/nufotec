<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('base_url')) {
    function base_url($uri = '')
    {
        $CI =& get_instance();
        
        if (empty($uri)) {
            return $CI->config->slash_item('base_url');
        }
        
        // Enlever tous les préfixes langue (AVEC ou SANS majuscules)
        // fr, FR, Fr, fR, en, EN, sw, SW, etc.
        $uri = preg_replace('#^(fr|en|sw|FR|EN|SW|Fr|En|Sw)/#i', '', $uri);
        $uri = preg_replace('#^(fr|en|sw|FR|EN|SW|Fr|En|Sw)$#i', '', $uri);
        $uri = preg_replace('#/(fr|en|sw|FR|EN|SW|Fr|En|Sw)/#i', '/', $uri);
        $uri = preg_replace('#/(fr|en|sw|FR|EN|SW|Fr|En|Sw)$#i', '', $uri);
        
        // Nettoyer les doubles slashes
        $uri = preg_replace('#/+#', '/', $uri);
        $uri = trim($uri, '/');
        
        return $CI->config->slash_item('base_url') . $uri;
    }
}

if (!function_exists('t')) {
    function t($key, $params = []) {
        $CI =& get_instance();
        $text = $CI->lang->line($key);
        if (!empty($params) && is_array($params)) {
            foreach ($params as $k => $v) {
                $text = str_replace('{' . $k . '}', $v, $text);
            }
        }
        return !empty($text) ? $text : $key;
    }
}