<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon -->
    <link rel="icon" 
          href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>"
          type="image/png"
          sizes="16x16 32x32 64x64">
    
    <!-- Plugins -->
    <link href="<?= base_url() ?>assets/backend/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/plugins/simplebar/css/simplebar.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <!-- Loader -->
    <link href="<?= base_url() ?>assets/backend/css/pace.min.css" rel="stylesheet">    
    <script src="<?= base_url() ?>assets/backend/js/pace.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="<?= base_url() ?>assets/backend/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/css/bootstrap-extended.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= base_url() ?>assets/backend/sass/app.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/backend/css/icons.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    
 <link rel="stylesheet" href="<?= base_url()?>assets/summernote/summernote-bs4.min.css">
    
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/backend/sass/dark-theme.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/backend/sass/semi-dark.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/backend/sass/bordered-theme.css">

    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <title><?= $this->Model->get_setting('site_name', 'AGF Phytomed') ?></title>
</head>

<body>
    <!--wrapper-->
    <div class="wrapper">