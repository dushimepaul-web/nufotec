<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Titre de la page (affiché dans les résultats de recherche) -->
    <title><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?></title>

    <!-- Meta descriptions et mots-clés (pour le SEO) -->
    <meta name="description" content="<?= htmlspecialchars($this->Model->get_setting('site_description', $this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi'))) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($this->Model->get_setting('site_keywords', 'phytomédicaments, agro-industrie, Burundi, santé naturelle, nutrition, NUFOTEC')) ?>">
    <meta name="author" content="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="<?= htmlspecialchars($this->Model->get_setting('theme_color', '#2c7a4b')) ?>">

    <!-- URL canonique (évite le contenu dupliqué) -->
    <link rel="canonical" href="<?= base_url() ?>">

    <!-- Balises Open Graph (pour les réseaux sociaux et l’affichage enrichi) -->
    <meta property="og:title" content="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi')) ?>">
    <meta property="og:image" content="<?= base_url($this->Model->get_setting('og_image', 'assets/fro.png')) ?>">
    <meta property="og:url" content="<?= base_url() ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?>">

    <!-- Balises Twitter Card (améliore l’affichage sur Twitter) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi')) ?>">
    <meta name="twitter:image" content="<?= base_url($this->Model->get_setting('og_image', 'assets/fro.png')) ?>">
    <?php if ($twitter_site = $this->Model->get_setting('twitter_site')): ?>
    <meta name="twitter:site" content="<?= htmlspecialchars($twitter_site) ?>">
    <?php endif; ?>

    <!-- Favicône (affichée dans l’onglet du navigateur) -->
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>">

    <!-- Préchargement et chargement optimisé des polices Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    </noscript>

    <!-- Styles CSS (Bootstrap et Font Awesome) -->
    <link href="<?= base_url('assets/backend/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Données structurées JSON-LD (pour les rich snippets Google) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?>",
        "url": "<?= base_url() ?>",
        "logo": "<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', '')) ?>",
        "description": "<?= htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi')) ?>",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Bujumbura",
            "addressCountry": "BI"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "<?= htmlspecialchars($this->Model->get_setting('site_phone', '+257 79 666 439')) ?>",
            "contactType": "customer service",
            "email": "<?= htmlspecialchars($this->Model->get_setting('smtp_email', '')) ?>"
        },
        "sameAs": [
            "<?= htmlspecialchars($this->Model->get_setting('twitter_site', '#')) ?>"
        ]
    }
    </script>
</head>
<!-- ... suite du body ... -->
    <style>
        :root {
            --primary: #0f4c3a;
            --primary-dark: #0a3326;
            --primary-light: #1a5f4a;
            --primary-lighter: #e8f5f0;
            --accent: #d4af37;
            --accent-hover: #b8941f;
            --accent-light: #faf6e9;
            --light: #f8faf9;
            --dark: #1a1a1a;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            padding-top: 140px;
            line-height: 1.6;
        }

        /* ============================================
           TOP BAR - Responsive & Intelligent
           ============================================ */
        .top-bar {
            background: var(--primary-dark);
            color: rgba(255,255,255,0.9);
            font-size: 12px;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            transition: transform 0.3s ease;
        }

        .top-bar.hidden {
            transform: translateY(-100%);
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .top-bar-left, .top-bar-right {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .top-bar a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
            font-weight: 500;
            white-space: nowrap;
        }

        .top-bar a:hover { color: var(--accent); }
        .top-bar i { color: var(--accent); font-size: 13px; }
        .top-bar-divider {
            width: 1px;
            height: 16px;
            background: rgba(212, 175, 55, 0.3);
        }

        /* Responsive Top Bar */
        @media (max-width: 768px) {
            .top-bar { font-size: 11px; padding: 8px 0; }
            .top-bar-left, .top-bar-right { gap: 12px; }
            .top-bar-divider { display: none; }
            .top-bar-right { display: none; } /* Cache droite sur mobile */
        }

        @media (max-width: 480px) {
            .top-bar-left { width: 100%; justify-content: center; }
            .top-bar a span { max-width: 120px; overflow: hidden; text-overflow: ellipsis; }
        }

        /* ============================================
           MAIN HEADER - Intelligent & Adaptatif
           ============================================ */
        .main-header {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            position: fixed;
            top: 42px;
            left: 0;
            right: 0;
            z-index: 1020;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .main-header.scrolled {
            top: 0;
            box-shadow: var(--shadow-lg);
        }

        .main-header.scrolled .top-bar {
            transform: translateY(-100%);
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 82px;
            gap: 24px;
            position: relative;
        }

        /* Logo Premium */
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(15, 76, 58, 0.25);
            transition: var(--transition);
            overflow: hidden;
            flex-shrink: 0;
        }

        .brand:hover .brand-logo {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(15, 76, 58, 0.35);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            line-height: 1.2;
            white-space: nowrap;
            transition: var(--transition);
        }

        .brand-info span {
            font-size: 9px;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            display: block;
        }

        /* Search - Adaptive */
        .search-container {
            flex: 1;
            max-width: 450px;
            position: relative;
            transition: var(--transition);
        }

        .search-box { position: relative; width: 100%; }

        .search-input {
            width: 100%;
            height: 44px;
            padding: 0 45px 0 18px;
            border: 2px solid var(--gray-light);
            border-radius: 22px;
            font-size: 14px;
            background: var(--light);
            transition: var(--transition);
            color: var(--dark);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(15, 76, 58, 0.1);
        }

        .search-btna {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            background: var(--primary);
            border: none;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-btna:hover {
            background: var(--accent);
            transform: translateY(-50%) scale(1.05);
        }

        /* Header Actions - Intelligent */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .action-btna {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--dark);
            font-size: 13px;
            font-weight: 600;
            transition: var(--transition);
            background: transparent;
            border: 2px solid transparent;
            white-space: nowrap;
        }

        .action-btna:hover {
            background: var(--primary-lighter);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .action-btna i {
            font-size: 18px;
            color: var(--primary);
            transition: var(--transition);
        }

        .action-btna:hover i { color: var(--accent); }

        /* Language Selector Compact */
        .lang-selector { position: relative; }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 10px;
            border: 2px solid var(--gray-light);
            background: var(--white);
            cursor: pointer;
            transition: var(--transition);
            font-size: 13px;
            font-weight: 600;
        }

        .lang-btn:hover {
            border-color: var(--accent);
            background: var(--accent-light);
        }

        .lang-btn img {
            width: 20px;
            height: 15px;
            border-radius: 2px;
            object-fit: cover;
        }

        .lang-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            padding: 8px;
            min-width: 160px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 100;
            border: 1px solid var(--gray-light);
        }

        .lang-selector:hover .lang-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .lang-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .lang-option:hover {
            background: var(--primary-lighter);
            color: var(--primary);
        }

        .lang-option img {
            width: 20px;
            height: 15px;
            border-radius: 2px;
        }

        /* Mobile Toggle */
        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: none;
            background: var(--primary-lighter);
            color: var(--primary);
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .mobile-menu-btn:hover {
            background: var(--primary);
            color: var(--white);
        }

        /* ============================================
           NAVIGATION - SMART & ADAPTIVE
           ============================================ */
        .main-nav {
            background: var(--white);
            border-top: 1px solid var(--gray-light);
            position: fixed;
            top: 124px;
            left: 0;
            right: 0;
            z-index: 1010;
            transition: var(--transition);
        }

        .main-header.scrolled + .main-nav {
            top: 82px;
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            position: relative;
        }

        /* Navigation Menu - Smart Overflow */
        .nav-menu {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 4px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            flex: 1;
            max-width: calc(100% - 200px); /* Reserve space for CTA */
        }

        .nav-menu::-webkit-scrollbar { display: none; }

        .nav-item {
            position: relative;
            flex-shrink: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            color: var(--dark);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition);
            white-space: nowrap;
            background: transparent;
            border: 2px solid transparent;
        }

        .nav-link:hover {
            background: var(--primary-lighter);
            color: var(--primary);
            border-color: var(--accent);
        }

        .nav-link i { font-size: 12px; transition: var(--transition); }
        .nav-link:hover i { transform: rotate(180deg); color: var(--accent); }

        /* Dropdowns - Positioned Smart */
        .dropdown-menu-custom {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            padding: 12px;
            min-width: 260px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            z-index: 1000;
            max-height: 70vh;
            overflow-y: auto;
        }

        .nav-item:hover .dropdown-menu-custom {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item-custom {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            color: var(--gray);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .dropdown-item-custom:hover {
            background: var(--primary-lighter);
            color: var(--primary);
            transform: translateX(4px);
        }

        .dropdown-item-custom i {
            width: 20px;
            text-align: center;
            color: var(--primary);
        }

        /* Mega Menu - Smart Positioning */
        .mega-menu { position: static !important; }

        .mega-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(-10px);
            width: 90vw;
            max-width: 1000px;
            max-height: 70vh;
            overflow-y: auto;
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            padding: 30px;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            z-index: 1000;
        }

        .mega-menu:hover .mega-dropdown {
            opacity: 1;
            visibility: visible;
            /*transform: translateX(-50%) translateY(0);*/
        }

        .mega-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }

        .mega-column h3 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary);
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--accent);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mega-column h3 i { font-size: 16px; color: var(--accent); }

        .mega-list { list-style: none; padding: 0; }
        .mega-list li { margin-bottom: 4px; }

        .mega-list a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            color: var(--gray);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            transition: var(--transition);
        }

        .mega-list a:hover {
            background: var(--primary-lighter);
            color: var(--primary);
            padding-left: 16px;
        }

        .mega-list a i { font-size: 11px; color: var(--accent); }

        /* Badge Pro */
        .badge-pro {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: var(--primary-dark);
            font-size: 9px;
            padding: 3px 8px;
            border-radius: 12px;
            margin-left: auto;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* CTA Nav */
        .nav-cta {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
            margin-left: 16px;
        }

        .btn-nav-primary {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            border: none;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(15, 76, 58, 0.25);
        }

        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 76, 58, 0.35);
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }

        /* Mobile Overlay */
        .mobile-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 76, 58, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1005;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* ============================================
           RESPONSIVE - INTELLIGENT BREAKPOINTS
           ============================================ */
        
        /* XL Screens - Full Display */
        @media (min-width: 1400px) {
            .nav-link { padding: 10px 20px; font-size: 14px; }
            .mega-dropdown { max-width: 1200px; padding: 40px; }
        }

        /* Large Screens - Optimized */
        @media (max-width: 1200px) {
            .search-container { max-width: 350px; }
            .nav-link { padding: 10px 14px; font-size: 12px; }
            .brand-info h1 { font-size: 20px; }
        }

        /* Medium Screens - Compact Mode */
        @media (max-width: 992px) {
            body { padding-top: 120px; }
            
            .top-bar { transform: translateY(0); }
            .top-bar.hidden { transform: translateY(-100%); }
            
            .main-header { top: 0; }
            .main-header.scrolled { top: 0; }
            
            .header-container { height: 70px; gap: 16px; }
            
            /* Hide desktop elements */
            .search-container,
            .nav-cta,
            .action-btna span { display: none; }
            
            .action-btna { padding: 10px; }
            .action-btna i { font-size: 20px; }
            
            .mobile-menu-btn { display: flex; }
            
            /* Mobile Navigation - Full Screen */
            .main-nav {
                position: fixed;
                top: 70px !important;
                left: -100%;
                width: 85%;
                max-width: 380px;
                height: calc(100vh - 70px);
                background: var(--white);
                box-shadow: var(--shadow-xl);
                transition: left 0.3s ease;
                overflow-y: auto;
                border-top: none;
                border-radius: 0 20px 20px 0;
            }
            
            .main-nav.active { left: 0; }
            
            .nav-container {
                flex-direction: column;
                height: auto;
                padding: 20px;
                align-items: stretch;
            }
            
            .nav-menu {
                flex-direction: column;
                width: 100%;
                max-width: 100%;
                gap: 6px;
                overflow-x: visible;
            }
            
            .nav-item { width: 100%; }
            
            .nav-link {
                width: 100%;
                justify-content: space-between;
                padding: 16px 18px;
                font-size: 15px;
                background: var(--light);
                border: 1px solid var(--gray-light);
            }

            .nav-link:hover {
                background: var(--primary);
                color: var(--white);
                transform: translateX(4px);
            }

            .nav-link:hover i { color: var(--accent); transform: rotate(90deg); }
            
            /* Mobile Dropdowns - Accordion Style */
            .dropdown-menu-custom,
            .mega-dropdown {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                border: none;
                padding: 0 0 0 16px;
                margin-top: 8px;
                display: none;
                width: 100%;
                max-height: none;
                overflow-y: visible;
                background: transparent;
            }
            
            .nav-item.active .dropdown-menu-custom,
            .nav-item.active .mega-dropdown {
                display: block;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
                from { opacity: 0; max-height: 0; }
                to { opacity: 1; max-height: 1000px; }
            }
            
            .mega-grid { 
                grid-template-columns: 1fr; 
                gap: 20px; 
            }
            
            .mega-dropdown { 
                padding: 0; 
                width: 100%;
            }
            
            .mega-column h3 { 
                font-size: 11px;
                margin-bottom: 12px;
                padding-bottom: 8px;
            }

            .dropdown-item-custom,
            .mega-list a {
                background: rgba(255,255,255,0.8);
                margin-bottom: 6px;
                border: 1px solid var(--gray-light);
                padding: 12px 14px;
            }

            /* Mobile CTA */
            .mobile-cta {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-top: 24px;
                padding-top: 24px;
                border-top: 2px solid var(--accent);
            }
            
            .mobile-cta .btn-nav-primary,
            .mobile-cta .btn-nav-secondary {
                width: 100%;
                justify-content: center;
                padding: 14px;
                font-size: 14px;
            }

            .btn-nav-secondary {
                padding: 10px 20px;
                background: transparent;
                color: var(--primary);
                border-radius: 10px;
                text-decoration: none;
                font-size: 13px;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: var(--transition);
                border: 2px solid var(--primary);
                justify-content: center;
            }

            .btn-nav-secondary:hover {
                background: var(--primary);
                color: var(--white);
            }
        }

        /* Small Screens - Ultra Compact */
        @media (max-width: 576px) {
            body { padding-top: 110px; }
            
            .header-container { height: 60px; gap: 10px; }
            
            .brand-info h1 { font-size: 16px; }
            .brand-info span { font-size: 8px; letter-spacing: 1px; }
            .brand-logo { width: 40px; height: 40px; border-radius: 10px; }
            
            .action-btna { padding: 8px; }
            .action-btna i { font-size: 18px; }
            
            .lang-btn { padding: 6px 10px; }
            .lang-btn span { display: none; } /* Hide text, keep flag */
            
            .mobile-menu-btn { width: 36px; height: 36px; font-size: 18px; }
            
            .main-nav {
                top: 60px !important;
                height: calc(100vh - 60px);
                width: 90%;
            }
        }

        /* Extra Small - Minimal */
        @media (max-width: 360px) {
            .brand-info h1 { font-size: 14px; }
            .top-bar-left { gap: 8px; }
            .top-bar a { font-size: 10px; }
        }

        /* Landscape Mobile Optimization */
        @media (max-height: 500px) and (max-width: 992px) {
            .main-nav { overflow-y: auto; }
            .nav-link { padding: 12px 16px; }
            .mobile-cta { margin-top: 16px; padding-top: 16px; }
        }

        /* Tablet Landscape - Horizontal Nav */
        @media (min-width: 992px) and (max-width: 1200px) {
            .nav-menu { gap: 2px; }
            .nav-link { padding: 8px 12px; font-size: 12px; }
            .nav-cta { margin-left: 10px; }
            .btn-nav-primary { padding: 8px 16px; font-size: 12px; }
        }

            .search-container {
    position: relative;
}

.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    margin-top: 5px;
}

.result-category {
    padding: 8px 12px;
    background-color: #f5f5f5;
    font-weight: bold;
    font-size: 0.9em;
    color: #0B4F2E;
    border-bottom: 1px solid #eee;
}

.result-item {
    display: block;
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    text-decoration: none;
    color: #333;
    transition: background 0.2s;
}

.result-item:hover {
    background-color: #f9f9f9;
}

.result-title {
    display: block;
    font-weight: 500;
    margin-bottom: 4px;
    color: #0B4F2E;
}

.result-desc {
    display: block;
    font-size: 0.85em;
    color: #666;
}

.result-no {
    padding: 15px;
    text-align: center;
    color: #999;
}

.result-view-all {
    display: block;
    padding: 10px;
    text-align: center;
    background-color: #f0f0f0;
    color: #0B4F2E;
    font-weight: bold;
    text-decoration: none;
    border-top: 1px solid #ddd;
}

.result-view-all:hover {
    background-color: #e0e0e0;
}

.search-loading, .search-error {
    padding: 15px;
    text-align: center;
    color: #999;
}
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar" id="topBar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <a href="tel:+25779666439">
                        <i class="bi bi-telephone-fill"></i>
                        <span><?= $this->Model->get_setting('contact_whatsapp', '+257 79 666 439')?></span>
                    </a>
                    <div class="top-bar-divider"></div>
                    <a href="mailto:info@agf-phytomedicine.com">
                        <i class="bi bi-envelope-fill"></i>
                        <span><?= $this->Model->get_setting('contact_email_info', 'info@agf-phytomed.com')?></span>
                    </a>
                </div>
                <div class="top-bar-right">
                    <a href="#">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><?= $this->Model->get_setting('adresse_siege', 'Bujumbura, Burundi')?></span>
                    </a>
                    <div class="top-bar-divider"></div>
                    <a href="#">
                        <i class="bi bi-clock-fill"></i>
                        <span><?= $this->Model->get_setting('horaires_travail', 'Lun - Ven: 8h - 17h') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header" id="mainHeader">
        <div class="container">
            <div class="header-container">
                <!-- Logo -->
                <a href="<?= base_url('') ?>" class="brand">
                    <div class="brand-logo">
                        <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" alt="AGF Phytomed">
                    </div>
                    <div class="brand-info">
                        <h1><?= $this->Model->get_setting('site_name', 'AGF Phytomed') ?></h1>
                    </div>
                </a>

                <!-- Search -->
<div class="search-container">
    <div class="search-box">
        <input type="text" id="search-input" class="search-input" placeholder="Search products, Categories, news..." autocomplete="off">
        <button class="search-btna" id="search-button">
            <i class="bi bi-search"></i>
        </button>
    </div>
    <!-- Conteneur pour les résultats instantanés -->
    <div id="search-results" class="search-results-dropdown"></div>
</div>

                <!-- Actions -->
                <div class="header-actions">
                   <?php
// Récupération des données de session (à ajuster selon votre système)
$logged_in = $this->session->userdata('logged_in') === TRUE;
$user_id = $this->session->userdata('user_id');
$user_name = $this->session->userdata('username');
$user_photo = $this->session->userdata('photo'); // nom du fichier photo
$initials = '?'; // pour l'avatar par défaut
if ($logged_in && !empty($user_name)) {
    $parts = explode(' ', trim($user_name));
    if (count($parts) >= 2) {
        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    } else {
        $initials = strtoupper(substr($user_name, 0, 2));
    }
}
?>

<a href="<?= $logged_in ? base_url('home-patient') : base_url('Auth') ?>" 
   class="action-btna" 
   title="<?= $logged_in ? 'Mon compte' : 'Se connecter' ?>">
    <?php if ($logged_in && !empty($user_photo) && file_exists(FCPATH . 'attachments/Users/' . $user_photo)): ?>
        <img src="<?= base_url('attachments/Users/' . $user_photo) ?>" 
             alt="Avatar" 
             class="rounded-circle" 
             style="width: 32px; height: 32px; object-fit: cover;">
    <?php elseif ($logged_in): ?>
        <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
             style="width: 32px; height: 32px; font-size: 14px; font-weight: bold;">
            <?= $initials ?>
        </div>
    <?php else: ?>
        <i class="bi bi-person-circle"></i>
    <?php endif; ?>
    <span><?= $logged_in ? 'Mon compte' : 'Connexion' ?></span>
</a>
                    <a href="#" class="action-btna cart" title="Panier">
                        <i class="bi bi-cart3"></i>
                        <span class="cart-count">3</span>
                    </a>

                    <div class="lang-selector">
                        <button class="lang-btn" aria-label="Changer de langue">
                            <img src="https://flagcdn.com/w20/fr.png" alt="FR">
                            <span>FR</span>
                            <i class="bi bi-chevron-down" style="font-size: 10px;"></i>
                        </button>
                        <div class="lang-dropdown">
                            <a href="#" class="lang-option">
                                <img src="https://flagcdn.com/w20/fr.png" alt="FR">
                                <span>Français</span>
                            </a>
                            <a href="#" class="lang-option">
                                <img src="https://flagcdn.com/w20/us.png" alt="EN">
                                <span>English</span>
                            </a>
                            <a href="#" class="lang-option">
                                <img src="https://flagcdn.com/w20/tz.png" alt="SW">
                                <span>Kiswahili</span>
                            </a>
                        </div>
                    </div>

                    <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Menu">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Navigation -->
    <nav class="main-nav" id="mainNav">
        <div class="container nav-container">
            <ul class="nav-menu">
                
                <!-- 1. HOME -->
                <li class="nav-item">
                    <a href="<?= base_url('') ?>" class="nav-link">
                        <i class="bi bi-house-door"></i> 
                        <span>Home</span>
                    </a>
                </li>

                <!-- 2. ABOUT US -->
                <li class="nav-item mega-menu">
                    <a href="#" class="nav-link" onclick="toggleDropdown(event, this)">
                        <span>About Us</span> 
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <div class="mega-dropdown">
                        <div class="mega-grid">
                            <div class="mega-column">
                                <h3><i class="bi bi-building"></i> Corporate</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('Profile-Entreprise') ?>"><i class="bi bi-chevron-right"></i> <span>Corporate Profile</span></a></li>
                                    <li><a href="<?= base_url('background-strategic-rationale') ?>"><i class="bi bi-chevron-right"></i> <span>Background & Strategic Rationale</span></a></li>
                                    <li><a href="<?= base_url('corporate-structure-governance') ?>"><i class="bi bi-chevron-right"></i> <span>Corporate Structure & Governance</span></a></li>
                                    <li><a href="<?= base_url('vision-mission') ?>"><i class="bi bi-chevron-right"></i> <span>Vision & Mission</span></a></li>
                                    <li><a href="#"><i class="bi bi-chevron-right"></i> <span>Leadership</span></a></li>
                                </ul>
                            </div>
                            <div class="mega-column">
                                <h3><i class="bi bi-leaf"></i> Sustainability</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('Frontend/Esg_Sustainability') ?>"><i class="bi bi-chevron-right"></i> <span>ESG & Sustainability</span></a></li>
                                    <li><a href="<?= base_url('risk-analysis') ?>"><i class="bi bi-chevron-right"></i> <span>Risk Analysis & Mitigation</span></a></li>
                                    <li><a href="<?= base_url('Frontend/Research_Innovation') ?>"><i class="bi bi-chevron-right"></i> <span>Research & Innovation</span></a></li>
                                </ul>
                            </div>
                            <div class="mega-column">
                                <h3><i class="bi bi-gear-wide-connected"></i> Facility</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('agf-phytomed-facility') ?>"><i class="bi bi-chevron-right"></i> <span>AGF-PHYTOMED Facility</span></a></li>
                                    <li><a href="#"><i class="bi bi-chevron-right"></i> <span>Manufacturing Facility</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 3. E-COMMERCE -->
                <li class="nav-item mega-menu">
                    <a href="<?= base_url('boutique') ?>" class="nav-link" onclick="toggleDropdown(event, this)">
                        <span>E-Commerce</span> 
                        <i class="bi bi-chevron-down"></i>
                    </a>
                </li>

                <!-- 4. TELECONSULTATION -->
                <li class="nav-item mega-menu">
                    <a href="#" class="nav-link" onclick="toggleDropdown(event, this)">
                        <span>Teleconsultation</span> 
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <div class="mega-dropdown">
                        <div class="mega-grid">
                            <div class="mega-column">
                                <h3><i class="bi bi-graph-up"></i> Market</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('market-outlook') ?>"><i class="bi bi-chevron-right"></i> <span>Market & Industry Outlook</span></a></li>
                                    <li><a href="#"><i class="bi bi-chevron-right"></i> <span>Industry Report</span></a></li>
                                    <li><a href="#"><i class="bi bi-chevron-right"></i> <span>Publications</span></a></li>
                                </ul>
                            </div>
                            <div class="mega-column">
                                <h3><i class="bi bi-laptop"></i> Digital Platform</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('digital-growth') ?>"><i class="bi bi-chevron-right"></i> <span>Digital Growth & Expansion</span></a></li>
                                    <li><a href="<?= base_url('Medicins') ?>"><i class="bi bi-chevron-right"></i> <span>Digital Health Consultation</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 5. INVESTMENT -->
                <li class="nav-item mega-menu">
                    <a href="#" class="nav-link" onclick="toggleDropdown(event, this)">
                        <span>Investment</span> 
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <div class="mega-dropdown">
                        <div class="mega-grid">
                            <div class="mega-column">
                                <h3><i class="bi bi-piggy-bank"></i> Capital</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('investment-projection') ?>"><i class="bi bi-chevron-right"></i> <span>Phased Investment Projection</span></a></li>
                                    <li><a href="#"><i class="bi bi-chevron-right"></i> <span>Seed Capital Allocation</span></a></li>
                                    <li><a href="#"><i class="bi bi-chevron-right"></i> <span>Financial Projections</span></a></li>
                                </ul>
                            </div>
                            <div class="mega-column">
                                <h3><i class="bi bi-handshake"></i> Partnerships</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('investor-commitment') ?>"><i class="bi bi-chevron-right"></i> <span>Investor & Partner Commitment</span></a></li>
                                    <li><a href="<?= base_url('strategic-partnerships') ?>"><i class="bi bi-chevron-right"></i> <span>Strategic Partnerships</span></a></li>
                                    <li><a href="#"><i class="bi bi-chevron-right"></i> <span>Institutional Partners</span></a></li>
                                </ul>
                            </div>
                            <div class="mega-column">
                                <h3><i class="bi bi-bank"></i> Relations</h3>
                                <ul class="mega-list">
                                    <li><a href="<?= base_url('broker-commission') ?>"><i class="bi bi-chevron-right"></i> <span>Commission Fee to Brokers</span></a></li>
                                    <li><a href="<?= base_url('Brokers-form') ?>"><i class="bi bi-chevron-right"></i><span>Become Our brokers <span class="badge-pro">Pro</span></span></a></li>
                                     <li><a href="<?= base_url('Investors-form') ?>"><i class="bi bi-chevron-right"></i><span>Become Our partners <span class="badge-pro">Pro</span></span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 6. NEWS -->
                <li class="nav-item">
                    <a href="<?= base_url('Home/Media') ?>" class="nav-link">
                        <span>News</span> 
                    </a>
                </li>

            </ul>

            <!-- Desktop CTA -->
            <div class="nav-cta d-none d-lg-flex">
                <a href="<?= base_url('Home/Contact') ?>" class="btn-nav-primary">
                    <i class="bi bi-headset"></i> 
                    <span>Contact Us</span>
                </a>
                <a href="#" class="btn-nav-primary">
                    <i class="bi bi-file-earmark-text"></i> 
                    <span>Investor Deck</span>
                </a>
            </div>

            <!-- Mobile CTA -->
            <div class="mobile-cta d-lg-none">
                <a href="#" class="btn-nav-primary">
                    <i class="bi bi-file-earmark-text"></i> 
                    <span>Investor Deck</span>
                </a>
                <a href="<?= base_url('Home/Contact') ?>" class="btn-nav-secondary">
                    <i class="bi bi-headset"></i> 
                    <span>Contact Us</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <script>
// ============================================
// Comportement du header intelligent
// ============================================
(function() {
    'use strict';

    // Éléments DOM
    const header = document.getElementById('mainHeader');
    const topBar = document.getElementById('topBar');
    const nav = document.getElementById('mainNav');
    const mobileOverlay = document.getElementById('mobileOverlay');

    // Gestion du scroll
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            header.classList.add('scrolled');
            topBar.classList.add('hidden');
        } else {
            header.classList.remove('scrolled');
            topBar.classList.remove('hidden');
        }
    });

    // Fonction pour ouvrir/fermer le menu mobile (globale pour l'attribut onclick)
    window.toggleMobileMenu = function() {
        const isOpen = nav.classList.toggle('active');
        mobileOverlay.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    // Fonction pour basculer les dropdowns en mobile (globale pour l'attribut onclick)
    window.toggleDropdown = function(event, element) {
        // Seulement en dessous de 992px
        if (window.innerWidth >= 992) return;

        event.preventDefault();
        event.stopPropagation();

        const parent = element.closest('.nav-item');
        const wasActive = parent.classList.contains('active');

        // Fermer tous les autres items actifs
        const siblings = parent.parentElement.querySelectorAll('.nav-item.active');
        siblings.forEach(function(item) {
            if (item !== parent) {
                item.classList.remove('active');
            }
        });

        // Basculer l'état actuel
        parent.classList.toggle('active', !wasActive);
    };

    // Fermer le menu mobile lors du redimensionnement au-dessus de 992px
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            nav.classList.remove('active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';

            // Réinitialiser tous les dropdowns ouverts
            document.querySelectorAll('.nav-item.active').forEach(function(item) {
                item.classList.remove('active');
            });
        }
    });

    // Fermer les dropdowns mobiles en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            const isNav = e.target.closest('.main-nav');
            const isToggle = e.target.closest('.mobile-menu-btn');
            if (!isNav && !isToggle) {
                document.querySelectorAll('.nav-item.active').forEach(function(item) {
                    item.classList.remove('active');
                });
            }
        }
    });

    // Touche Échap pour fermer le menu mobile
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && nav.classList.contains('active')) {
            toggleMobileMenu();
        }
    });

    // ============================================
    // Recherche en direct (AJAX)
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const resultsContainer = document.getElementById('search-results');
        const searchButton = document.getElementById('search-button');

        if (!searchInput || !resultsContainer || !searchButton) return; // Sécurité

        let typingTimer;
        const doneTypingInterval = 300; // délai après la frappe (ms)

        // Cacher les résultats si on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                resultsContainer.style.display = 'none';
            }
        });

        // Recherche en direct lors de la saisie
        searchInput.addEventListener('keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                const query = searchInput.value.trim();
                if (query.length < 2) {
                    resultsContainer.innerHTML = '';
                    resultsContainer.style.display = 'none';
                    return;
                }
                performSearch(query);
            }, doneTypingInterval);
        });

        // Clic sur le bouton de recherche (redirection vers la page de résultats)
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query.length > 0) {
                window.location.href = '<?= base_url("search/index?q=") ?>' + encodeURIComponent(query);
            }
        });

        // Touche Entrée (redirection vers la page de résultats)
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query.length > 0) {
                    window.location.href = '<?= base_url("search/index?q=") ?>' + encodeURIComponent(query);
                }
            }
        });

        // Fonction pour effectuer la requête AJAX
        function performSearch(query) {
            resultsContainer.innerHTML = '<div class="search-loading">Recherche...</div>';
            resultsContainer.style.display = 'block';

            const url = '<?= base_url("search/ajax_search") ?>?q=' + encodeURIComponent(query);

            fetch(url)
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(function(data) {
                    displayResults(data);
                })
                .catch(function(error) {
                    resultsContainer.innerHTML = '<div class="search-error">Erreur de recherche</div>';
                    console.error('Erreur:', error);
                });
        }

        // Fonction pour afficher les résultats
        function displayResults(data) {
            let html = '';
            let hasResults = false;

            // Produits
            if (data.produits && data.produits.length > 0) {
                html += '<div class="result-category">Produits</div>';
                data.produits.slice(0, 5).forEach(function(item) {
                    html += '<a href="<?= base_url("boutique/detail/") ?>' + item.slug + '" class="result-item">' +
                            '<span class="result-title">' + escapeHtml(item.titre) + '</span>' +
                            '<span class="result-desc">' + (item.extrait ? escapeHtml(item.extrait.substring(0, 60)) + '...' : '') + '</span>' +
                            '</a>';
                    hasResults = true;
                });
            }

            // Actualités
            if (data.actualites && data.actualites.length > 0) {
                html += '<div class="result-category">Actualités</div>';
                data.actualites.slice(0, 3).forEach(function(item) {
                    html += '<a href="<?= base_url("actualite/lire/") ?>' + item.slug + '" class="result-item">' +
                            '<span class="result-title">' + escapeHtml(item.titre) + '</span>' +
                            '<span class="result-desc">' + (item.extrait ? escapeHtml(item.extrait.substring(0, 60)) + '...' : '') + '</span>' +
                            '</a>';
                    hasResults = true;
                });
            }

            // Pages
            if (data.pages && data.pages.length > 0) {
                html += '<div class="result-category">Pages</div>';
                data.pages.slice(0, 3).forEach(function(item) {
                    html += '<a href="<?= base_url("/") ?>' + item.slug + '" class="result-item">' +
                            '<span class="result-title">' + escapeHtml(item.titre) + '</span>' +
                            '<span class="result-desc">' + (item.extrait ? escapeHtml(item.extrait.substring(0, 60)) + '...' : '') + '</span>' +
                            '</a>';
                    hasResults = true;
                });
            }

            // Services
            if (data.services && data.services.length > 0) {
                html += '<div class="result-category">Services</div>';
                data.services.slice(0, 3).forEach(function(item) {
                    html += '<a href="<?= base_url("service/detail/") ?>' + item.slug + '" class="result-item">' +
                            '<span class="result-title">' + escapeHtml(item.titre) + '</span>' +
                            '<span class="result-desc">' + (item.extrait ? escapeHtml(item.extrait.substring(0, 60)) + '...' : '') + '</span>' +
                            '</a>';
                    hasResults = true;
                });
            }

            if (!hasResults) {
                html = '<div class="result-no">Aucun résultat trouvé</div>';
            } else {
                // Ajouter un lien "Voir tous les résultats"
                const currentQuery = searchInput.value.trim();
                html += '<a href="<?= base_url("search/index?q=") ?>' + encodeURIComponent(currentQuery) + '" class="result-view-all">Voir tous les résultats</a>';
            }

            resultsContainer.innerHTML = html;
            resultsContainer.style.display = 'block';
        }

        // Fonction utilitaire pour échapper le HTML (prévention XSS)
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
})();
</script>
