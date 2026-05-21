<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['whatsappdash'] = [
    'title' => 'WhatsApp Bot Dashboard',
    'items_per_page' => 50,
    'refresh_interval' => 30000, // millisecondes
    'enable_realtime' => true,
    'socket_port' => 3000
];