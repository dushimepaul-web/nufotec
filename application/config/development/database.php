<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

// Détection automatique environnement
if ($_SERVER['SERVER_NAME'] == 'localhost') {

    // Localhost Windows
    $db['default'] = array(
        'dsn'       => '',
        'hostname'  => 'localhost',
        'username'  => 'root',
        'password'  => '',
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

    // VPS Docker
    $db['default'] = array(
        'dsn'       => '',
        'hostname'  => 'db',
        'username'  => 'root',
        'password'  => 'root123',
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

}