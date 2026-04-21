<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ============================================
// CONFIGURATION WHAPI.CLOUD
// ============================================

$config['whapi'] = array(
    // Votre clé API Whapi.cloud
    'api_key' => 'j8D4W16avimu9kWT741NO8rdjA3yfpWK',
    
    // URL de base de l'API
    'base_url' => 'https://gate.whapi.cloud',
    
    // Timeouts
    'timeout' => 60,
    'upload_timeout' => 120,
    
    // Debug
    'debug' => true,
    
    // Limites des fichiers
    'max_file_size' => 16 * 1024 * 1024, // 16MB
    
    // Extensions autorisées par type
    'allowed_extensions' => array(
        'image'   => array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'),
        'video'   => array('mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp'),
        'audio'   => array('mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma'),
        'document'=> array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar')
    ),
    
    // Types MIME
    'mime_types' => array(
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed'
    ),
    
    // Retry automatique
    'retry_attempts' => 3,
    'retry_delay' => 2000, // millisecondes
    'rate_limit_delay' => 1000
);

// ============================================
// NUMÉROS ADMIN
// ============================================
$config['admin_numbers'] = [
    '2376XXXXXXXXX',  // REMPLACEZ PAR VOTRE NUMÉRO
];

// ============================================
// RÈGLES DE FILTRAGE POUR LES MEMBRES
// ============================================
$config['allowed_for_members'] = ['text'];  // Seul le texte est autorisé

$config['blocked_patterns'] = [
    '/https?:\/\//i',           // URLs
    '/www\.[a-z0-9\.\-]+/i',    // www.
    '/\.(com|org|net|fr|cm|info|biz)/i', // Domaines
    '/bit\.ly|tinyurl|goo\.gl/i' // Raccourcisseurs
];

// ============================================
// CONFIGURATION ANTI-BAN
// ============================================
$config['antiban'] = [
    'min_delay_micro' => 500000,   // 0.5 sec
    'max_delay_micro' => 1500000,  // 1.5 sec
    'min_delay_seconds' => 2,      // 2 sec
    'max_delay_seconds' => 4,      // 4 sec
    'long_pause_probability' => 20, // 20% de chance
    'long_pause_min' => 5,         // 5 sec min
    'long_pause_max' => 10,        // 10 sec max
    'batch_size' => 5,             // 5 messages par batch
    'batch_interval' => 60,        // 60 sec entre batchs
    'max_messages_per_hour' => 60  // 60 messages/heure max
];

// ============================================
// STOCKAGE DES MÉDIAS
// ============================================
$config['media_storage_path'] = FCPATH . 'uploads/whatsapp_media/';
$config['tmp_path'] = FCPATH . 'tmp/';
