<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('is_json')) {
    /**
     * Vérifie si une chaîne est un JSON valide
     * 
     * @param string $string La chaîne à vérifier
     * @return bool
     */
    function is_json($string) {
        if (empty($string) || !is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}

if (!function_exists('json_encode_pretty')) {
    /**
     * Encode en JSON avec formatage lisible
     * 
     * @param mixed $data Données à encoder
     * @return string
     */
    function json_encode_pretty($data) {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('json_decode_array')) {
    /**
     * Décode un JSON en tableau, retourne tableau vide en cas d'erreur
     * 
     * @param string $json JSON à décoder
     * @return array
     */
    function json_decode_array($json) {
        if (empty($json)) {
            return [];
        }
        
        $decoded = json_decode($json, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
    }
}