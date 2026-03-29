<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['whapi'] = array(
    'api_key' => 'j8D4W16avimu9kWT741NO8rdjA3yfpWK',  // Votre clé API complète
    'base_url' => 'https://gate.whapi.cloud',     // ← SUPPRESSION de l'espace à la fin
    'timeout' => 60,
    'upload_timeout' => 120,
    'debug' => true,
    'max_file_size' => 16 * 1024 * 1024, // 16MB
    'allowed_extensions' => array(
        'image'   => array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'),
        'video'   => array('mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp'),
        'audio'   => array('mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma'),
        'document'=> array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar')
    ),
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
    'retry_attempts' => 3,
    'retry_delay' => 2000,
    'rate_limit_delay' => 1000
);