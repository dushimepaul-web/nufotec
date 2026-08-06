<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * ============================================
 * Module: admin_galerie
 * Description: Unified admin media management (video, audio, images, documents, links)
 * Route alias: admin_media (routes map admin/media/* to admin_galerie/Media/*)
 * Version: 1.0.0
 * Author: AGF Phytomed
 * ============================================
 */

$config['module_name']        = 'admin_galerie';
$config['module_route_alias'] = 'admin_media';
$config['module_version']     = '1.0.0';
$config['module_description'] = 'Gestion unifiée des medias (video, audio, image, document, lien)';
$config['module_author']      = 'AGF Phytomed';
$config['module_controller']  = 'Media';
$config['module_database_table'] = 'galerie_medias';
