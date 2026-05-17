<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'NUFOTEC' ?> - Media</title>
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Translate -->
    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'fr',
            includedLanguages: 'fr,en,rn,sw,ar,de,es,pt,it,zh-CN,ru,nl,pl,tr,ja,ko,hi,vi,th,el,he,sv,da,no,fi,cs,hu,ro,uk',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }
    </script>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    
    <style>
        /* YouTube Dark Mode Colors */
        :root {
            --yt-black: #0f0f0f;
            --yt-white: #ffffff;
            --yt-gray-100: #f1f1f1;
            --yt-gray-200: #e5e5e5;
            --yt-gray-300: #d4d4d4;
            --yt-gray-400: #aaaaaa;
            --yt-gray-500: #717171;
            --yt-gray-600: #606060;
            --yt-gray-700: #3d3d3d;
            --yt-gray-800: #272727;
            --yt-gray-900: #212121;
            --yt-red: #ff0000;
            --yt-blue: #3ea6ff;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: var(--yt-black); 
            color: var(--yt-gray-100);
            overflow-x: hidden;
            top: 0 !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Cacher l'interface Google Translate */
        .goog-te-banner-frame.skiptranslate,
        .goog-te-banner-frame,
        .goog-te-banner,
        .skiptranslate {
            display: none !important;
            height: 0 !important;
            visibility: hidden !important;
            opacity: 0 !important;
            position: absolute !important;
            top: -9999px !important;
        }

        /* ============================================ */
        /* NAVBAR YOUTUBE STYLE */
        /* ============================================ */
        .navbar {
            background: var(--yt-black);
            border-bottom: 1px solid var(--yt-gray-800);
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 56px;
        }
        
        .navbar .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            gap: 1rem;
        }
        
        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        
        .menu-icon {
            background: transparent;
            border: none;
            color: var(--yt-gray-100);
            font-size: 1.5rem;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        
        .menu-icon:hover {
            background: var(--yt-gray-800);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            flex-shrink: 0;
        }
        
        .brand-name {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--yt-gray-100);
            letter-spacing: -0.5px;
            white-space: nowrap;
        }
        
        .brand-subname {
            font-size: 0.7rem;
            color: var(--yt-gray-500);
            margin-left: 0.25rem;
        }

        /* Search Bar */
        .search-container {
            flex: 1;
            max-width: 640px;
            margin: 0 2rem;
            display: flex;
            justify-content: center;
        }
        
        .search-form {
            display: flex;
            width: 100%;
            max-width: 640px;
        }
        
        .search-input {
            background: var(--yt-gray-900);
            border: 1px solid var(--yt-gray-700);
            border-radius: 40px 0 0 40px;
            color: var(--yt-gray-100);
            padding: 0.5rem 1rem;
            width: 100%;
            font-size: 0.9rem;
            outline: none;
            height: 40px;
        }
        
        .search-input:focus {
            border-color: var(--yt-blue);
            background: var(--yt-black);
        }
        
        .search-btn {
            background: var(--yt-gray-800);
            border: 1px solid var(--yt-gray-700);
            border-left: none;
            border-radius: 0 40px 40px 0;
            color: var(--yt-gray-100);
            padding: 0 1.25rem;
            cursor: pointer;
            transition: background 0.2s;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-btn:hover {
            background: var(--yt-gray-700);
        }

        /* Mobile Search */
        .search-toggle {
            display: none;
            background: transparent;
            border: none;
            color: var(--yt-gray-100);
            font-size: 1.2rem;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
        }
        
        .search-toggle:hover {
            background: var(--yt-gray-800);
        }

        .mobile-search-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--yt-black);
            padding: 0.5rem 1rem;
            z-index: 1002;
            align-items: center;
            gap: 0.5rem;
            height: 56px;
        }
        
        .mobile-search-overlay.active {
            display: flex;
        }
        
        .mobile-search-overlay .search-form {
            flex: 1;
        }
        
        .back-btn {
            background: transparent;
            border: none;
            color: var(--yt-gray-100);
            font-size: 1.5rem;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Language Selector Desktop */
        .lang-selector-custom {
            position: relative;
            margin-left: 8px;
        }
        
        .custom-language-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--yt-gray-800);
            border: 1px solid var(--yt-gray-700);
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            font-weight: 500;
            color: var(--yt-gray-100);
            transition: all 0.2s ease;
        }
        
        .custom-language-btn:hover {
            border-color: var(--yt-blue);
            background: var(--yt-gray-700);
            transform: translateY(-1px);
        }
        
        .custom-language-btn img {
            width: 20px;
            height: 15px;
            border-radius: 2px;
            object-fit: cover;
        }
        
        .custom-language-btn i {
            font-size: 10px;
            transition: transform 0.2s ease;
        }
        
        .custom-language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: var(--yt-gray-900);
            border-radius: 16px;
            box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.3);
            padding: 8px;
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 1000;
            border: 1px solid var(--yt-gray-700);
            max-height: 400px;
            overflow-y: auto;
        }
        
        .custom-language-dropdown.active {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }
        
        .lang-option {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 10px 14px !important;
            border-radius: 10px !important;
            width: 100% !important;
            border: none !important;
            background: transparent !important;
            text-align: left !important;
            cursor: pointer !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: var(--yt-gray-100) !important;
            transition: all 0.2s ease !important;
        }
        
        .lang-option:hover {
            background: var(--yt-gray-800) !important;
            color: var(--yt-blue) !important;
            transform: translateX(4px) !important;
        }
        
        .lang-option img {
            width: 22px !important;
            height: 16px !important;
            border-radius: 3px !important;
            object-fit: cover !important;
        }

        /* ============================================ */
        /* SELECTEUR DE LANGUE MOBILE (DANS SIDEBAR) */
        /* ============================================ */
        .mobile-lang-selector {
            padding: 0 1rem;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }

        .current-mobile-lang {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--yt-gray-800);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid var(--yt-gray-700);
        }

        .current-mobile-lang:hover {
            background: var(--yt-gray-700);
            border-color: var(--yt-blue);
        }

        .current-mobile-lang img {
            width: 24px;
            height: 16px;
            border-radius: 3px;
        }

        .current-mobile-lang span {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        }

        .current-mobile-lang i {
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .current-mobile-lang.active i {
            transform: rotate(180deg);
        }

        .mobile-lang-dropdown {
            margin-top: 8px;
            background: var(--yt-gray-800);
            border-radius: 12px;
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-lang-dropdown.active {
            max-height: 300px;
            opacity: 1;
            visibility: visible;
            overflow-y: auto;
        }

        .mobile-lang-option {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 12px 16px !important;
            width: 100% !important;
            background: transparent !important;
            border: none !important;
            color: var(--yt-gray-100) !important;
            font-size: 14px !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            text-align: left !important;
        }

        .mobile-lang-option:hover {
            background: var(--yt-gray-700) !important;
            padding-left: 24px !important;
        }

        .mobile-lang-option img {
            width: 24px;
            height: 16px;
            border-radius: 3px;
        }

        /* Nav Icons */
        .nav-icons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        
        /* Sidebar */
        .sidebar { 
            position: fixed; 
            left: 0; 
            top: 56px; 
            width: 240px; 
            height: calc(100vh - 56px); 
            background: var(--yt-black); 
            overflow-y: auto; 
            padding: 0.75rem 0; 
            z-index: 999; 
            transition: transform 0.3s ease;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--yt-gray-700);
            border-radius: 4px;
        }
        
        .sidebar-item { 
            display: flex; 
            align-items: center; 
            gap: 1.5rem; 
            padding: 0.5rem 1.5rem; 
            color: var(--yt-gray-100); 
            text-decoration: none; 
            cursor: pointer; 
            transition: background 0.2s;
            font-size: 0.875rem;
        }
        
        .sidebar-item:hover, .sidebar-item.active { 
            background: var(--yt-gray-800); 
        }
        
        .sidebar-item i { 
            font-size: 1.25rem; 
            width: 24px; 
        }
        
        .sidebar-section { 
            padding: 0.5rem 0; 
            border-bottom: 1px solid var(--yt-gray-800); 
        }
        
        .sidebar-title { 
            padding: 0.5rem 1.5rem; 
            font-size: 0.75rem; 
            font-weight: 600; 
            color: var(--yt-gray-500); 
            text-transform: uppercase; 
        }
        
        /* Main Content */
        .main-content { 
            margin-left: 240px; 
            padding: 1.5rem; 
            min-height: calc(100vh - 56px); 
            transition: margin-left 0.3s ease;
        }
        
        /* Media Grid */
        .media-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 1rem; 
        }
        
        .media-card { 
            cursor: pointer; 
            transition: transform 0.2s; 
        }
        
        .media-card:hover {
            transform: translateY(-2px);
        }
        
        .thumbnail-container { 
            position: relative; 
            border-radius: 12px;
            overflow: hidden;
            background: var(--yt-gray-900);
            aspect-ratio: 16 / 9;
        }
        
        .thumbnail-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        
        .duration-badge { 
            position: absolute; 
            bottom: 8px; 
            right: 8px; 
            background: rgba(0,0,0,0.85); 
            color: var(--yt-gray-100); 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-size: 0.7rem; 
            font-weight: 500; 
            z-index: 10; 
        }
        
        .card-info { 
            padding: 0.75rem 0.25rem; 
            display: flex;
            gap: 0.75rem;
        }
        
        .channel-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--yt-gray-700);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-details {
            flex: 1;
            min-width: 0;
        }
        
        .card-title { 
            font-size: 0.9rem; 
            font-weight: 500; 
            margin-bottom: 0.25rem; 
            line-height: 1.3; 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden;
            color: var(--yt-gray-100);
        }
        
        .card-meta { 
            color: var(--yt-gray-500); 
            font-size: 0.75rem; 
        }

        /* Audio Player */
        .audio-player-bar { 
            position: fixed; 
            bottom: 0; 
            left: 240px; 
            right: 0; 
            background: var(--yt-gray-900); 
            border-top: 1px solid var(--yt-gray-800); 
            padding: 0.5rem 1rem; 
            display: none; 
            align-items: center; 
            gap: 1rem; 
            z-index: 1001; 
            height: 64px; 
        }
        
        .audio-player-bar.active { 
            display: flex; 
        }
        
        /* Responsive */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .audio-player-bar {
                left: 0;
            }
            .media-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 767px) {
            .search-container {
                display: none;
            }
            .search-toggle {
                display: flex;
            }
            .media-grid {
                grid-template-columns: 1fr;
            }
            .brand-subname {
                display: none;
            }
            /* Cacher le sélecteur desktop sur mobile */
            .lang-selector-custom {
                display: none !important;
            }
        }
        
        @media (min-width: 768px) {
            /* Afficher le sélecteur desktop sur desktop */
            .lang-selector-custom {
                display: block;
            }
            /* Cacher le sélecteur mobile sur desktop */
            .mobile-lang-selector {
                display: none;
            }
        }
        
        @media (max-width: 479px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                padding: 0.75rem;
            }
            .brand-name {
                font-size: 1rem;
            }
        }

        .empty-state { 
            text-align: center; 
            padding: 4rem 2rem; 
            color: var(--yt-gray-500); 
        }
        
        .empty-state i { 
            font-size: 4rem; 
            margin-bottom: 1rem; 
        }
        
        .search-header { 
            margin-bottom: 1.5rem; 
        }
        
        .toast-container { 
            position: fixed; 
            bottom: 80px; 
            right: 20px; 
            z-index: 9999; 
        }
    </style>
</head>
<body>

<!-- Google Translate Container (caché) -->
<div id="google_translate_element" style="display: none;"></div>

<!-- Mobile Search Overlay -->
<div class="mobile-search-overlay" id="mobileSearchOverlay">
    <button class="back-btn" onclick="closeMobileSearch()">
        <i class="bi bi-arrow-left"></i>
    </button>
    <form action="<?= base_url('media/apiSearch') ?>" method="GET" class="search-form">
        <input type="text" name="q" class="search-input" placeholder="Rechercher" id="mobileSearchInput">
        <button class="search-btn" type="submit"><i class="bi bi-search"></i></button>
    </form>
</div>

<!-- Navbar -->
<nav class="navbar">
    <div class="container-fluid">
        <div class="logo-wrapper">
            <button class="menu-icon d-md-none" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="<?= base_url('media') ?>">
                <?php 
                $site_logo = $this->Model->get_setting('site_logo');
                if (!empty($site_logo)): 
                ?>
                    <img src="<?= base_url('attachments/Configurations/' . $site_logo) ?>" alt="Logo NUFOTEC" height="40">
                <?php endif; ?>
                <span class="brand-name"><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC')) ?></span>
                <span class="brand-subname">MEDIA</span>
            </a>
        </div>
        
        <!-- Desktop Search -->
        <div class="search-container">
            <form action="<?= base_url('media/apiSearch') ?>" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Rechercher" value="<?= isset($search_query) ? htmlspecialchars($search_query) : '' ?>">
                <button class="search-btn" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
        
        <div class="nav-icons">
            <button class="search-toggle" onclick="openMobileSearch()">
                <i class="bi bi-search"></i>
            </button>
            
            <!-- Language Selector DESKTOP (visible sur desktop seulement) -->
            <div class="lang-selector-custom">
                <button class="custom-language-btn" id="customLanguageBtn">
                    <img src="https://flagcdn.com/w20/fr.png" alt="Français" id="currentLangFlag">
                    <span id="currentLangLabel">Français</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="custom-language-dropdown" id="customLanguageDropdown">
                    <button class="lang-option" data-lang="fr" data-flag="fr" data-label="Français">
                        <img src="https://flagcdn.com/w20/fr.png" alt="Français"> Français
                    </button>
                    <button class="lang-option" data-lang="en" data-flag="us" data-label="English">
                        <img src="https://flagcdn.com/w20/us.png" alt="English"> English
                    </button>
                    <button class="lang-option" data-lang="rn" data-flag="bi" data-label="Kirundi">
                        <img src="https://flagcdn.com/w20/bi.png" alt="Kirundi"> Kirundi
                    </button>
                    <button class="lang-option" data-lang="sw" data-flag="tz" data-label="Kiswahili">
                        <img src="https://flagcdn.com/w20/tz.png" alt="Kiswahili"> Kiswahili
                    </button>
                    <button class="lang-option" data-lang="ar" data-flag="sa" data-label="العربية">
                        <img src="https://flagcdn.com/w20/sa.png" alt="العربية"> العربية
                    </button>
                    <button class="lang-option" data-lang="de" data-flag="de" data-label="Deutsch">
                        <img src="https://flagcdn.com/w20/de.png" alt="Deutsch"> Deutsch
                    </button>
                    <button class="lang-option" data-lang="es" data-flag="es" data-label="Español">
                        <img src="https://flagcdn.com/w20/es.png" alt="Español"> Español
                    </button>
                    <button class="lang-option" data-lang="pt" data-flag="pt" data-label="Português">
                        <img src="https://flagcdn.com/w20/pt.png" alt="Português"> Português
                    </button>
                    <button class="lang-option" data-lang="it" data-flag="it" data-label="Italiano">
                        <img src="https://flagcdn.com/w20/it.png" alt="Italiano"> Italiano
                    </button>
                    <button class="lang-option" data-lang="zh-CN" data-flag="cn" data-label="中文">
                        <img src="https://flagcdn.com/w20/cn.png" alt="中文"> 中文
                    </button>
                    <button class="lang-option" data-lang="ru" data-flag="ru" data-label="Русский">
                        <img src="https://flagcdn.com/w20/ru.png" alt="Русский"> Русский
                    </button>
                    <button class="lang-option" data-lang="nl" data-flag="nl" data-label="Nederlands">
                        <img src="https://flagcdn.com/w20/nl.png" alt="Nederlands"> Nederlands
                    </button>
                    <button class="lang-option" data-lang="pl" data-flag="pl" data-label="Polski">
                        <img src="https://flagcdn.com/w20/pl.png" alt="Polski"> Polski
                    </button>
                    <button class="lang-option" data-lang="tr" data-flag="tr" data-label="Türkçe">
                        <img src="https://flagcdn.com/w20/tr.png" alt="Türkçe"> Türkçe
                    </button>
                    <button class="lang-option" data-lang="ja" data-flag="jp" data-label="日本語">
                        <img src="https://flagcdn.com/w20/jp.png" alt="日本語"> 日本語
                    </button>
                    <button class="lang-option" data-lang="ko" data-flag="kr" data-label="한국어">
                        <img src="https://flagcdn.com/w20/kr.png" alt="한국어"> 한국어
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-section">
        <a href="<?= base_url('media') ?>" class="sidebar-item <?= empty($current_type) && empty($search_query) ? 'active' : '' ?>">
            <i class="bi bi-house-fill"></i><span>Accueil</span>
        </a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-title">Catégories</div>
        <?php
        $types = [
            'video'    => ['icon' => 'camera-video-fill',   'label' => 'Vidéos'],
            'audio'    => ['icon' => 'music-note-beamed',   'label' => 'Audio'],
            'image'    => ['icon' => 'image-fill',          'label' => 'Images'],
            'document' => ['icon' => 'file-earmark-text-fill', 'label' => 'Documents']
        ];
        ?>
        <?php foreach ($types as $type => $info): ?>
            <a href="javascript:void(0)" class="sidebar-item <?= (!empty($current_type) && $current_type === $type) ? 'active' : '' ?>" onclick="filterMedia('<?= $type ?>')">
                <i class="bi bi-<?= $info['icon'] ?>"></i><span><?= $info['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Language Selector MOBILE (visible dans le sidebar sur mobile) -->
    <div class="sidebar-section">
        <div class="sidebar-title">
            <i class="bi bi-translate"></i> Langue
        </div>
        <div class="mobile-lang-selector" id="mobileLangSelector">
            <div class="current-mobile-lang" id="currentMobileLang" onclick="toggleMobileLangDropdown()">
                <img src="https://flagcdn.com/w20/fr.png" alt="Français" id="mobileCurrentLangFlag">
                <span id="mobileCurrentLangLabel">Français</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="mobile-lang-dropdown" id="mobileLangDropdown">
                <button class="mobile-lang-option" data-lang="fr" data-flag="fr" data-label="Français">
                    <img src="https://flagcdn.com/w20/fr.png" alt="Français"> Français
                </button>
                <button class="mobile-lang-option" data-lang="en" data-flag="us" data-label="English">
                    <img src="https://flagcdn.com/w20/us.png" alt="English"> English
                </button>
                <button class="mobile-lang-option" data-lang="rn" data-flag="bi" data-label="Kirundi">
                    <img src="https://flagcdn.com/w20/bi.png" alt="Kirundi"> Kirundi
                </button>
                <button class="mobile-lang-option" data-lang="sw" data-flag="tz" data-label="Kiswahili">
                    <img src="https://flagcdn.com/w20/tz.png" alt="Kiswahili"> Kiswahili
                </button>
                <button class="mobile-lang-option" data-lang="ar" data-flag="sa" data-label="العربية">
                    <img src="https://flagcdn.com/w20/sa.png" alt="العربية"> العربية
                </button>
                <button class="mobile-lang-option" data-lang="de" data-flag="de" data-label="Deutsch">
                    <img src="https://flagcdn.com/w20/de.png" alt="Deutsch"> Deutsch
                </button>
                <button class="mobile-lang-option" data-lang="es" data-flag="es" data-label="Español">
                    <img src="https://flagcdn.com/w20/es.png" alt="Español"> Español
                </button>
                <button class="mobile-lang-option" data-lang="pt" data-flag="pt" data-label="Português">
                    <img src="https://flagcdn.com/w20/pt.png" alt="Português"> Português
                </button>
                <button class="mobile-lang-option" data-lang="it" data-flag="it" data-label="Italiano">
                    <img src="https://flagcdn.com/w20/it.png" alt="Italiano"> Italiano
                </button>
                <button class="mobile-lang-option" data-lang="zh-CN" data-flag="cn" data-label="中文">
                    <img src="https://flagcdn.com/w20/cn.png" alt="中文"> 中文
                </button>
                <button class="mobile-lang-option" data-lang="ru" data-flag="ru" data-label="Русский">
                    <img src="https://flagcdn.com/w20/ru.png" alt="Русский"> Русский
                </button>
                <button class="mobile-lang-option" data-lang="nl" data-flag="nl" data-label="Nederlands">
                    <img src="https://flagcdn.com/w20/nl.png" alt="Nederlands"> Nederlands
                </button>
                <button class="mobile-lang-option" data-lang="pl" data-flag="pl" data-label="Polski">
                    <img src="https://flagcdn.com/w20/pl.png" alt="Polski"> Polski
                </button>
                <button class="mobile-lang-option" data-lang="tr" data-flag="tr" data-label="Türkçe">
                    <img src="https://flagcdn.com/w20/tr.png" alt="Türkçe"> Türkçe
                </button>
                <button class="mobile-lang-option" data-lang="ja" data-flag="jp" data-label="日本語">
                    <img src="https://flagcdn.com/w20/jp.png" alt="日本語"> 日本語
                </button>
                <button class="mobile-lang-option" data-lang="ko" data-flag="kr" data-label="한국어">
                    <img src="https://flagcdn.com/w20/kr.png" alt="한국어"> 한국어
                </button>
            </div>
        </div>
    </div>

    <div class="sidebar-section">
        <a href="<?= base_url('') ?>" class="sidebar-item">
            <i class="bi bi-door-closed"></i><span>Quitter</span>
        </a>
    </div>
</aside>

<!-- Main Content -->
<main class="main-content">
    <?php if (!empty($search_query)): ?>
        <div class="search-header">
            <h5>Résultats pour "<?= htmlspecialchars($search_query) ?>"</h5>
            <small class="text-secondary"><?= (int)$results_count ?> résultat(s)</small>
        </div>
    <?php endif; ?>

    <div class="media-grid">
        <?php if (!empty($medias)): ?>
            <?php foreach ($medias as $media): ?>
                <?= createMediaCard($media, $lang ?? 'fr') ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="empty-state" style="display: <?= empty($medias) ? 'flex' : 'none' ?>; flex-direction: column; align-items: center;">
        <i class="bi bi-play-circle"></i>
        <h5>Aucun média disponible</h5>
        <small class="text-secondary">Revenez plus tard pour découvrir du nouveau contenu.</small>
    </div>
</main>

<!-- Audio Player -->
<div class="audio-player-bar" id="audioPlayerBar">
    <div class="player-info">
        <img src="" alt="" class="player-thumb" id="playerThumb">
        <div class="player-details">
            <h4 id="playerTitle">Titre</h4>
        </div>
    </div>
    <div class="player-controls">
        <div class="control-buttons">
            <button class="control-btn" onclick="previousTrack()"><i class="bi bi-skip-start-fill"></i></button>
            <button class="control-btn play" onclick="togglePlay()" id="playPauseBtn"><i class="bi bi-play-fill"></i></button>
            <button class="control-btn" onclick="nextTrack()"><i class="bi bi-skip-end-fill"></i></button>
        </div>
        <div class="progress-container">
            <span id="currentTime">0:00</span>
            <div class="progress-bar" onclick="seekAudio(event)"><div class="progress-fill" id="progressFill"></div></div>
            <span id="totalTime">0:00</span>
        </div>
    </div>
    <button class="control-btn" onclick="toggleMute()"><i class="bi bi-volume-up-fill" id="volumeIcon"></i></button>
    <audio id="audioElement" preload="metadata"></audio>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================
// AUDIO PLAYER FUNCTIONS
// ============================================
let audioElement = document.getElementById('audioElement');
let isPlaying = false;
let mediaQueue = [];
let currentQueueIndex = 0;

if (audioElement) {
    audioElement.addEventListener('timeupdate', updateProgress);
    audioElement.addEventListener('ended', nextTrack);
    audioElement.addEventListener('loadedmetadata', function() {
        document.getElementById('totalTime').textContent = formatTime(audioElement.duration);
    });
}

function openMedia(mediaSlug) {
    window.location.href = '<?= base_url('media/detail/') ?>' + mediaSlug;
}

function openMobileSearch() {
    document.getElementById('mobileSearchOverlay').classList.add('active');
    document.getElementById('mobileSearchInput').focus();
}

function closeMobileSearch() {
    document.getElementById('mobileSearchOverlay').classList.remove('active');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMobileSearch();
    }
});

function togglePlay() {
    if (!audioElement) return;
    if (isPlaying) audioElement.pause(); else audioElement.play();
    isPlaying = !isPlaying;
    updatePlayButton();
}

function updatePlayButton() {
    const btn = document.getElementById('playPauseBtn');
    if (btn) btn.innerHTML = isPlaying ? '<i class="bi bi-pause-fill"></i>' : '<i class="bi bi-play-fill"></i>';
}

function updateProgress() {
    if (!audioElement) return;
    const percent = (audioElement.currentTime / audioElement.duration) * 100;
    const fill = document.getElementById('progressFill');
    if (fill) fill.style.width = percent + '%';
    document.getElementById('currentTime').textContent = formatTime(audioElement.currentTime);
}

function seekAudio(e) {
    if (!audioElement) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const x = e.clientX - rect.left;
    audioElement.currentTime = (x / rect.width) * audioElement.duration;
}

function previousTrack() { if (currentQueueIndex > 0) currentQueueIndex--; }
function nextTrack() { if (currentQueueIndex < mediaQueue.length - 1) currentQueueIndex++; }
function toggleMute() { if (!audioElement) return; audioElement.muted = !audioElement.muted; }

function formatTime(seconds) {
    if (isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function filterMedia(type) {
    window.location.href = type === 'all' ? '<?= base_url('media') ?>' : '<?= base_url('media/type/') ?>' + type;
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'primary'} border-0`;
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(toast);
    new bootstrap.Toast(toast).show();
    setTimeout(() => toast.remove(), 3000);
}

// ============================================
// LANGUAGE MANAGEMENT - DESKTOP
// ============================================
const langBtn = document.getElementById('customLanguageBtn');
const langDropdown = document.getElementById('customLanguageDropdown');
const currentLangFlag = document.getElementById('currentLangFlag');
const currentLangLabel = document.getElementById('currentLangLabel');

const savedLang = localStorage.getItem('preferred_language');
const savedFlag = localStorage.getItem('preferred_flag');
const savedLabel = localStorage.getItem('preferred_label');

if (savedLang && savedFlag && savedLabel && savedLang !== 'fr') {
    document.cookie = `googtrans=/fr/${savedLang}; path=/; max-age=31536000`;
    if (currentLangFlag && currentLangLabel) {
        currentLangFlag.src = `https://flagcdn.com/w20/${savedFlag}.png`;
        currentLangLabel.textContent = savedLabel;
    }
}

function toggleDropdown() {
    if (langDropdown) {
        langDropdown.classList.toggle('active');
    }
}

document.addEventListener('click', function(event) {
    if (langBtn && langDropdown && !langBtn.contains(event.target) && !langDropdown.contains(event.target)) {
        langDropdown.classList.remove('active');
    }
});

if (langBtn) {
    langBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        toggleDropdown();
    });
}

// Fonction améliorée avec nettoyage complet
function changeLanguage(langCode, flagCode, label) {
    // Mise à jour UI
    if (currentLangFlag && currentLangLabel) {
        currentLangFlag.src = `https://flagcdn.com/w20/${flagCode}.png`;
        currentLangLabel.textContent = label;
    }
    
    // Sauvegarde
    localStorage.setItem('preferred_language', langCode);
    localStorage.setItem('preferred_flag', flagCode);
    localStorage.setItem('preferred_label', label);
    
    // Nettoyage complet des cookies googtrans
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        if (cookie.trim().startsWith('googtrans=')) {
            const cookieName = cookie.trim().split('=')[0];
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=${window.location.hostname};`;
        }
    }
    
    // Réinitialiser l'élément Google Translate
    const googleTranslateElement = document.getElementById('google_translate_element');
    if (googleTranslateElement) {
        googleTranslateElement.innerHTML = '';
    }
    
    // Recharger Google Translate API
    if (typeof google !== 'undefined' && google.translate) {
        // Attendre un peu pour la réinitialisation
        setTimeout(() => {
            window.location.reload();
        }, 100);
    } else {
        window.location.reload();
    }
}

document.querySelectorAll('.lang-option').forEach(option => {
    option.addEventListener('click', function(event) {
        event.stopPropagation();
        const langCode = this.getAttribute('data-lang');
        const flagCode = this.getAttribute('data-flag');
        const label = this.getAttribute('data-label');
        changeLanguage(langCode, flagCode, label);
    });
});

// ============================================
// LANGUAGE MANAGEMENT - MOBILE (SIDEBAR)
// ============================================
function toggleMobileLangDropdown() {
    const currentMobileLang = document.getElementById('currentMobileLang');
    const mobileLangDropdown = document.getElementById('mobileLangDropdown');
    
    if (currentMobileLang && mobileLangDropdown) {
        currentMobileLang.classList.toggle('active');
        mobileLangDropdown.classList.toggle('active');
    }
}

function changeLanguageMobile(langCode, flagCode, label) {
    // Mettre à jour l'affichage dans la sidebar
    const mobileCurrentLangFlag = document.getElementById('mobileCurrentLangFlag');
    const mobileCurrentLangLabel = document.getElementById('mobileCurrentLangLabel');
    
    if (mobileCurrentLangFlag && mobileCurrentLangLabel) {
        mobileCurrentLangFlag.src = `https://flagcdn.com/w20/${flagCode}.png`;
        mobileCurrentLangLabel.textContent = label;
    }
    
    // Mettre à jour l'affichage desktop aussi
    if (currentLangFlag && currentLangLabel) {
        currentLangFlag.src = `https://flagcdn.com/w20/${flagCode}.png`;
        currentLangLabel.textContent = label;
    }
    
    // Sauvegarder dans localStorage
    localStorage.setItem('preferred_language', langCode);
    localStorage.setItem('preferred_flag', flagCode);
    localStorage.setItem('preferred_label', label);
    
    // Définir le cookie Google Translate
    document.cookie = `googtrans=/fr/${langCode}; path=/; max-age=31536000`;
    
    // Recharger la page
    window.location.reload();
}

// Ajouter les événements pour les options de langue mobile
document.querySelectorAll('.mobile-lang-option').forEach(option => {
    option.addEventListener('click', function(event) {
        event.stopPropagation();
        const langCode = this.getAttribute('data-lang');
        const flagCode = this.getAttribute('data-flag');
        const label = this.getAttribute('data-label');
        changeLanguageMobile(langCode, flagCode, label);
    });
});

// Fermer le dropdown mobile si on clique ailleurs
document.addEventListener('click', function(event) {
    const mobileLangSelector = document.getElementById('mobileLangSelector');
    const currentMobileLang = document.getElementById('currentMobileLang');
    const mobileLangDropdown = document.getElementById('mobileLangDropdown');
    
    if (mobileLangSelector && currentMobileLang && mobileLangDropdown) {
        if (!mobileLangSelector.contains(event.target)) {
            currentMobileLang.classList.remove('active');
            mobileLangDropdown.classList.remove('active');
        }
    }
});

// Mettre à jour l'affichage mobile avec la langue sauvegardée
if (savedLang && savedFlag && savedLabel && savedLang !== 'fr') {
    const mobileCurrentLangFlag = document.getElementById('mobileCurrentLangFlag');
    const mobileCurrentLangLabel = document.getElementById('mobileCurrentLangLabel');
    
    if (mobileCurrentLangFlag && mobileCurrentLangLabel) {
        mobileCurrentLangFlag.src = `https://flagcdn.com/w20/${savedFlag}.png`;
        mobileCurrentLangLabel.textContent = savedLabel;
    }
}

// Supprimer la barre Google Translate
setInterval(function() {
    var banner = document.querySelector('.goog-te-banner-frame');
    if (banner) {
        banner.style.display = 'none';
        banner.style.visibility = 'hidden';
        banner.style.height = '0';
    }
    document.body.style.marginTop = '0';
    document.body.style.top = '0';
}, 100);
</script>
</body>
</html>

<?php
function createMediaCard($media, $lang) {
    $type = $media['type'];
    $identifier = !empty($media['slug']) ? $media['slug'] : $media['id_media'];
    
    // Extraction YouTube ID
    $youtubeId = $media['youtube_id'] ?? '';
    if (empty($youtubeId) && !empty($media['lien'])) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $media['lien'], $matches);
        $youtubeId = $matches[1] ?? '';
    }
    
    // Miniature logic
    $thumbUrl = '';
    if (!empty($youtubeId)) {
        $thumbUrl = "https://img.youtube.com/vi/{$youtubeId}/mqdefault.jpg";
    } elseif (!empty($media['miniature']) && file_exists(FCPATH . $media['miniature'])) {
        $thumbUrl = base_url($media['miniature']);
    } elseif ($type === 'video' && !empty($media['fichier'])) {
        $thumb_path = FCPATH . 'attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbUrl = file_exists($thumb_path) 
            ? base_url('attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg')
            : base_url('assets/images/video-default.jpg');
    } elseif ($type === 'audio' && !empty($media['fichier'])) {
        $cover_path = FCPATH . 'attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg';
        $thumbUrl = file_exists($cover_path)
            ? base_url('attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg')
            : base_url('assets/images/audio-default.png');
    } elseif ($type === 'image' && !empty($media['fichier_url'])) {
        $thumbUrl = $media['fichier_url'];
    } else {
        $defaults = [
            'video' => base_url('assets/images/video-default.jpg'),
            'audio' => base_url('assets/images/audio-default.png'),
            'image' => base_url('assets/images/image-default.jpg'),
            'document' => base_url('assets/images/document-default.jpg')
        ];
        $thumbUrl = $defaults[$type] ?? base_url('assets/images/default-thumbnail.jpg');
    }
    
    $duration = $media['duration_formatted'] ?? '0:00';
    $title = htmlspecialchars($media['titre'] ?? 'Sans titre');
    $channel = htmlspecialchars($media['credits'] ?? $media['categorie'] ?? 'Chaîne inconnue');
    $views = number_format($media['views_count'] ?? 0);
    
    return '
    <div class="media-card" onclick="openMedia(\'' . addslashes($identifier) . '\')">
        <div class="thumbnail-container">
            <img src="' . $thumbUrl . '" class="thumbnail-img" loading="lazy" alt="' . $title . '">
            <span class="duration-badge">' . $duration . '</span>
        </div>
        <div class="card-info">
            <div class="channel-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="card-details">
                <div class="card-title">' . $title . '</div>
                <div class="card-meta">' . $channel . ' • ' . $views . ' vues</div>
            </div>
        </div>
    </div>';
}
?>