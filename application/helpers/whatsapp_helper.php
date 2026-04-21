<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vérifie si un message contient des liens
 */
function has_link($text) {
    $patterns = [
        '/https?:\/\//i',
        '/www\.[a-z0-9\.\-]+/i',
        '/\.(com|org|net|fr|cm|info|biz)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    return false;
}

/**
 * Nettoie un message
 */
function sanitize_message($message) {
    // Supprimer les caractères de contrôle
    $message = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $message);
    // Limiter la longueur (4000 caractères max pour WhatsApp)
    $message = substr($message, 0, 4000);
    return trim($message);
}

/**
 * Formate un numéro de téléphone
 */
function format_phone_number($number) {
    $number = preg_replace('/[^0-9+]/', '', $number);
    if (substr($number, 0, 2) === '00') {
        $number = '+' . substr($number, 2);
    }
    if (substr($number, 0, 1) !== '+') {
        $number = '+237' . ltrim($number, '0');
    }
    return $number;
}

/**
 * Vérifie si un type de média est autorisé pour les membres
 */
function is_media_allowed_for_member($media_type, $allowed_types) {
    return in_array($media_type, $allowed_types);
}
