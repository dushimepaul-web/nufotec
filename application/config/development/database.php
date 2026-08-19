<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

// Détection automatique de l'environnement
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // Configuration pour le serveur local
    $db['default'] = array(
        'dsn'       => '',
        'hostname'  => 'localhost',
        'username'  => 'root',
        'password'  => '',          // mot de passe local
        'database'  => 'nufotec_db',
        'dbdriver'  => 'mysqli',
        'dbprefix'  => '',
        'pconnect'  => FALSE,
        'db_debug'  => (ENVIRONMENT !== 'production'),
        'cache_on'  => FALSE,
        'cachedir'  => '',
        'char_set'  => 'utf8',
        'dbcollat'  => 'utf8_general_ci',
        'swap_pre'  => '',
        'encrypt'   => FALSE,
        'compress'  => FALSE,
        'stricton'  => FALSE,
        'failover'  => array(),
        'save_queries' => TRUE
    );
} else {
    // Configuration pour le serveur distant (identifiants via variables d'environnement)
    $db['default'] = array(
        'dsn'       => '',
'hostname'  => 'localhost',
'username'  => getenv('DB_USERNAME') ?: 'nufotec_nufotec',
'password'  => getenv('DB_PASSWORD') ?: 'Nufotec#2026Admin',
'database'  => getenv('DB_DATABASE') ?: 'nufotec_db',
'dbdriver'  => 'mysqli',
'dbprefix'  => '',
'pconnect'  => FALSE,
'db_debug'  => (ENVIRONMENT !== 'production'),
'cache_on'  => FALSE,
'cachedir'  => '',
'char_set'  => 'utf8mb4',
'dbcollat'  => 'utf8mb4_general_ci',
'swap_pre'  => '',
'encrypt'   => FALSE,
'compress'  => FALSE,
'stricton'  => FALSE,
'failover'  => array(),
'save_queries' => TRUE,
    );
}