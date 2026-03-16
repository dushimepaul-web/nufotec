<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['maintenance_mode'] = true;
//$config['maintenance_allowed_ips'] = array(
//    '127.0.0.1',
 //   '::1'
//);
$config['maintenance_allowed_users'] = array('admin');

// 🔑 URLs toujours accessibles (login, assets, etc.)
$config['maintenance_allowed_routes'] = array(
    'Admin',           // Page login admin
    'assets',                // CSS/JS/Images
    'css', 'js', 'images', 'uploads'
);