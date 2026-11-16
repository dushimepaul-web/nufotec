<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon -->
    <?php $favicon = $this->Model->get_setting('favicon_ico', ''); ?>
    <?php if (!empty($favicon) && strpos($favicon, 'assets/') !== 0 && strpos($favicon, 'http') !== 0): ?>
        <?php $favicon = 'attachments/Configurations/' . $favicon; ?>
    <?php endif; ?>
    <link rel="icon"
          href="<?= base_url($favicon ?: 'assets/backend/images/defaut-logo.jpeg') ?>"
          type="image/png"
          sizes="16x16 32x32 64x64">
    
    <!-- Loader -->
    <link href="<?= base_url() ?>assets/backend/css/pace.min.css" rel="stylesheet">
    <script src="<?= base_url() ?>assets/backend/js/pace.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="<?= base_url() ?>assets/backend/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE 4 CSS -->
    <link href="<?= base_url() ?>assets/backend/adminlte/css/adminlte.min.css" rel="stylesheet">

    <!-- Icones -->
    <link href="<?= base_url() ?>assets/backend/css/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/plugins/boxicons/css/boxicons.min.css" rel="stylesheet">

    <!-- Plugins CSS requis par les vues -->
    <link href="<?= base_url() ?>assets/backend/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Typos -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

    <title><?= $this->Model->get_setting('site_name', 'AGF Phytomed') ?></title>
    <!-- jQuery (chargé dans le Head pour éviter les erreurs d'initialisation dans les vues) -->
    <script src="<?= base_url() ?>assets/backend/js/jquery.min.js"></script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">