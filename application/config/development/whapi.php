<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['whapi'] = array(
    'api_key' => 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw',
    'base_url' => 'https://gate.whapi.cloud/',
    'timeout' => 60,
    'upload_timeout' => 120,
    'debug' => true,
    'max_file_size' => 16 * 1024 * 1024,
    'retry_attempts' => 3,
    'retry_delay' => 2000,
    'rate_limit_delay' => 1000,
    'webhook_secret' => 'nufotecburundi2026',
    'media_storage_path' => FCPATH . 'uploads/whatsapp_media/',
    'tmp_path' => FCPATH . 'tmp/'
);

// NUMÉROS ADMIN (format international complet)
$config['admin_numbers'] = ['25779666439'];

// Types autorisés pour les MEMBRES (SEULEMENT texte)
$config['allowed_for_members'] = ['text'];

// Patterns de liens bloqués
$config['blocked_patterns'] = [
    '/https?:\/\//i',
    '/www\.[a-z0-9\.\-]+/i',
    '/\.(com|org|net|fr|cm|info|biz|io|app|dev)/i',
    '/t\.me\//i',
    '/wa\.me\//i',
    '/bit\.ly\//i',
    '/tinyurl\./i'
];

// Paramètres anti-ban
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