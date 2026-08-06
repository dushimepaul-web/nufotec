<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol'] = 'smtp';
$config['smtp_host'] = 'localhost';
$config['smtp_port'] = 465;
$config['smtp_user'] = getenv('SMTP_USER') ?: 'info@nufotec.com';
$config['smtp_pass'] = getenv('SMTP_PASS') ?: '';
$config['smtp_crypto'] = 'ssl';
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n";




