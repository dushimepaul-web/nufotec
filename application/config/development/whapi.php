<?php
$config['whapi'] = array(
    'api_key' => 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw',
    'base_url' => 'https://gate.whapi.cloud/',
    'timeout' => 60,
    'upload_timeout' => 120,
    'debug' => true,
    'max_file_size' => 16 * 1024 * 1024,
    'retry_attempts' => 3,
    'retry_delay' => 2000,        // ms de base
    'rate_limit_delay' => 1000,
    // NOUVEAU : token secret pour webhook
    'webhook_secret' => 'nufotecburundi2026',
    // Chemin de stockage local des médias
    'media_storage_path' => FCPATH . 'uploads/whatsapp_media/',
    'tmp_path' => FCPATH . 'tmp/'
);

$config['admin_numbers'] = ['25779666439'];

$config['allowed_for_members'] = ['text'];
$config['blocked_patterns'] = [
    '/https?:\/\//i',
    '/www\.[a-z0-9\.\-]+/i',
    '/\.(com|org|net|fr|cm|info|biz)/i'
];

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