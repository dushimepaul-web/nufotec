<?php
// application/config/whatsapp.php

defined('BASEPATH') OR exit('No direct script access allowed');

// API WhatsApp Configuration
$config['whatsapp_api_url'] = 'https://graph.facebook.com/v18.0/YOUR_PHONE_NUMBER_ID/messages';
$config['whatsapp_access_token'] = 'YOUR_ACCESS_TOKEN';
$config['whatsapp_webhook_verify_token'] = 'YOUR_VERIFY_TOKEN';

// Numéros admin (ceux qui peuvent envoyer tous types de médias)
$config['admin_numbers'] = [
    '2376XXXXXXXXX',  // Remplacez par votre numéro
];

// Règles de filtrage pour les membres
$config['allowed_for_members'] = ['text'];  // Seul le texte est autorisé

$config['blocked_patterns'] = [
    '/https?:\/\//i',           // URLs
    '/www\.[a-z0-9\.\-]+/i',    // www.
    '/\.(com|org|net|fr|cm|info|biz)/i', // Domaines
    '/bit\.ly|tinyurl|goo\.gl/i' // Shorteners
];

// Configuration anti-ban
$config['antiban'] = [
    'min_delay_micro' => 500000,
    'max_delay_micro' => 1500000,
    'min_delay_seconds' => 2,
    'max_delay_seconds' => 4,
    'long_pause_probability' => 20,
    'long_pause_min' => 5,
    'long_pause_max' => 10,
    'batch_size' => 5,
    'batch_interval' => 60,
    'max_messages_per_hour' => 60
];

// Configuration du stockage des médias
$config['media_storage_path'] = FCPATH . 'uploads/whatsapp_media/';
$config['media_url_base'] = base_url('uploads/whatsapp_media/');
