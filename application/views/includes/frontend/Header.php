<!D<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php 
    // Vérifier si on est sur une page produit (contrôleur Products, méthode detail)
    $is_product_page = (isset($product) && !empty($product) && isset($product['title']));
    
    if ($is_product_page): 
        // Page produit - Meta tags spécifiques au produit
        $product_title = htmlspecialchars($product['title']) . ' - ' . htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'));
        $product_desc = !empty($product['description']) ? substr(htmlspecialchars($product['description']), 0, 160) : htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Produit NUFOTEC'));
        $product_image = base_url('attachments/Products/'.$product['main_image']);
        $product_url = base_url('Products/detail/'.($product['slug'] ?? $product['id']));
    ?>
    
    <!-- Titre spécifique produit -->
    <title><?= $product_title ?></title>
    
    <!-- Meta description produit -->
    <meta name="description" content="<?= $product_desc ?>">
    <meta name="keywords" content="<?= htmlspecialchars($product['title']) ?>, phytomédicaments, NUFOTEC, Burundi">
    
    <!-- URL canonique produit -->
    <link rel="canonical" href="<?= $product_url ?>">
    
    <!-- Open Graph / Facebook - PRODUIT -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?= $product_url ?>">
    <meta property="og:title" content="<?= htmlspecialchars($product['title']) ?>">
    <meta property="og:description" content="<?= $product_desc ?>">
    <meta property="og:image" content="<?= $product_image ?>">
    <meta property="og:image:secure_url" content="<?= $product_image ?>">
    <meta property="og:image:width" content="800">
    <meta property="og:image:height" content="800">
    <meta property="og:image:alt" content="<?= htmlspecialchars($product['title']) ?>">
    <meta property="og:site_name" content="NUFOTEC">
    <meta property="og:locale" content="fr_FR">
    
    <!-- Twitter Card - PRODUIT -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= $product_url ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($product['title']) ?>">
    <meta name="twitter:description" content="<?= $product_desc ?>">
    <meta name="twitter:image" content="<?= $product_image ?>">
    
    <!-- Prix produit (Open Graph) -->
    <?php if (!empty($product['price'])): ?>
    <meta property="product:price:amount" content="<?= preg_replace('/[^0-9.,]/', '', $product['price']) ?>">
    <meta property="product:price:currency" content="BIF">
    <?php endif; ?>
    
    <?php else: 
        // Page normale - Meta tags génériques du site
        $site_title = htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'));
        $site_desc = htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi'));
        
        // ✅ CORRECTION: Image OG avec fallback
        $site_logo = $this->Model->get_setting('site_logo', 'assets/fro.png');
        $site_image = base_url('attachments/Configurations/' . $site_logo);
    ?>
    
    <!-- Titre général du site -->
    <title><?= $site_title ?></title>
    
    <!-- Meta description générale -->
    <meta name="description" content="<?= $site_desc ?>">
    <meta name="keywords" content="<?= htmlspecialchars($this->Model->get_setting('site_keywords', 'phytomédicaments, agro-industrie, Burundi, santé naturelle, nutrition, NUFOTEC')) ?>">
    
    <!-- URL canonique -->
    <link rel="canonical" href="<?= base_url() ?>">
    
    <!-- ✅ Open Graph général - OPTIMISÉ -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url() ?>">
    <meta property="og:title" content="<?= $site_title ?>">
    <meta property="og:description" content="<?= $site_desc ?>">
    <meta property="og:image" content="<?= $site_image ?>">
    <meta property="og:image:secure_url" content="<?= str_replace('http://', 'https://', $site_image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Logo <?= $site_title ?>">
    <meta property="og:site_name" content="<?= $site_title ?>">
    <meta property="og:locale" content="fr_FR">
    
    <!-- ✅ Twitter Card général - OPTIMISÉ -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= base_url() ?>">
    <meta name="twitter:title" content="<?= $site_title ?>">
    <meta name="twitter:description" content="<?= $site_desc ?>">
    <meta name="twitter:image" content="<?= $site_image ?>">
    <meta name="twitter:image:alt" content="Logo <?= $site_title ?>">
    
    <?php endif; ?>

    <meta name="author" content="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="<?= htmlspecialchars($this->Model->get_setting('theme_color', '#2c7a4b')) ?>">

    <!-- Favicône -->
    <link rel="icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('favicon_ico', 'assets/fro.png')) ?>">

    <!-- Polices -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="<?= base_url('assets/backend/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">

    <script type="application/ld+json">
<?php
$jsonData = [
    "@context" => "https://schema.org",
    "@type" => $is_product_page ? "Product" : "Organization"
];

if ($is_product_page) {
    $jsonData["name"] = $product['title'];
    $jsonData["image"] = $product_image;
    $jsonData["description"] = $product_desc;
    $jsonData["url"] = $product_url;
    $jsonData["offers"] = [
        "@type" => "Offer",
        "price" => preg_replace('/[^0-9.,]/', '', $product['price'] ?? '0'),
        "priceCurrency" => "BIF",
        "availability" => "https://schema.org/InStock"
    ];
} else {
    $jsonData["name"] = $site_title;
    $jsonData["url"] = base_url();
    $jsonData["logo"] = $site_image;
    $jsonData["description"] = $site_desc;
}
echo json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
</script>

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
            --danger: #dc3545;
            --success: #198754;
            
            --header-height: 70px;
            --header-height-mobile: 60px;
            --top-bar-height: 36px;
            --nav-height: 56px;
            --bottom-nav-height: 64px;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            
            --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html { scroll-behavior: smooth; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            padding-top: calc(var(--header-height) + var(--nav-height));
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ============================================
           TOP BAR - Minimaliste & Intelligent
           ============================================ */
        .top-bar {
            background: var(--primary-dark);
            color: rgba(255,255,255,0.9);
            font-size: 12px;
            height: var(--top-bar-height);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            transition: transform var(--transition), opacity var(--transition);
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .top-bar.hidden {
            transform: translateY(-100%);
            opacity: 0;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0 16px;
        }

        .top-bar-left, .top-bar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .top-bar a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition-fast);
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

        /* ============================================
           MAIN HEADER - Style LinkedIn/YouTube
           ============================================ */
        .main-header {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            position: fixed;
            top: var(--top-bar-height);
            left: 0;
            right: 0;
            height: var(--header-height);
            z-index: 1020;
            box-shadow: var(--shadow);
            transition: transform var(--transition-slow), top var(--transition);
        }

        .main-header.nav-hidden {
            transform: translateY(-100%);
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
            height: 100%;
            padding: 0 16px;
            max-width: 1400px;
            margin: 0 auto;
            gap: 16px;
            position: relative;
        }

        /* Logo Premium */
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
            transition: var(--transition-fast);
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(15, 76, 58, 0.25);
            transition: var(--transition-fast);
            overflow: hidden;
            flex-shrink: 0;
        }

        .brand:hover .brand-logo {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(15, 76, 58, 0.35);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            line-height: 1.2;
            white-space: nowrap;
        }

        .brand-info span {
            font-size: 9px;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            display: block;
        }

        /* Search - Style YouTube/LinkedIn */
        .search-container {
            flex: 1;
            max-width: 480px;
            position: relative;
            transition: var(--transition);
        }

        .search-box {
            position: relative;
            width: 100%;
        }

        .search-input {
            width: 100%;
            height: 42px;
            padding: 0 44px 0 18px;
            border: 2px solid var(--gray-light);
            border-radius: 21px;
            font-size: 14px;
            background: var(--light);
            transition: var(--transition-fast);
            color: var(--dark);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(15, 76, 58, 0.1);
        }

        .search-btn {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            background: var(--primary);
            border: none;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-fast);
        }

        .search-btn:hover {
            background: var(--accent);
            transform: translateY(-50%) scale(1.05);
        }

        /* Search Results Dropdown */
        .search-results-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            border: 1px solid var(--gray-light);
        }

        .search-results-dropdown.active { display: block; }

        .result-category {
            padding: 8px 16px;
            background: var(--primary-lighter);
            font-weight: 600;
            font-size: 11px;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition-fast);
            border-bottom: 1px solid var(--gray-light);
        }

        .result-item:hover {
            background: var(--light);
            padding-left: 20px;
        }

        .result-item i {
            color: var(--primary);
            font-size: 16px;
        }

        .result-content {
            flex: 1;
        }

        .result-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
            color: var(--dark);
        }

        .result-desc {
            font-size: 12px;
            color: var(--gray);
        }

        .result-view-all {
            display: block;
            padding: 12px;
            text-align: center;
            background: var(--primary);
            color: white;
            font-weight: 600;
            text-decoration: none;
            font-size: 13px;
            transition: var(--transition-fast);
        }

        .result-view-all:hover {
            background: var(--primary-light);
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--dark);
            font-size: 13px;
            font-weight: 600;
            transition: var(--transition-fast);
            background: transparent;
            border: none;
            cursor: pointer;
            position: relative;
        }

        .action-btn:hover {
            background: var(--primary-lighter);
            color: var(--primary);
        }

        .action-btn i {
            font-size: 20px;
            color: var(--primary);
            transition: var(--transition-fast);
        }

        .action-btn:hover i { color: var(--accent); }

        .action-btn .badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: var(--danger);
            color: white;
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        /* Avatar */
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gray-light);
            transition: var(--transition-fast);
        }

        .action-btn:hover .avatar {
            border-color: var(--accent);
            transform: scale(1.05);
        }

        .avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            border: 2px solid var(--gray-light);
        }

        /* Language Selector */
        .lang-selector { position: relative; }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            border: 2px solid var(--gray-light);
            background: var(--white);
            cursor: pointer;
            transition: var(--transition-fast);
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
            transition: var(--transition-fast);
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

        /* Mobile Menu Toggle */
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
            font-size: 24px;
            cursor: pointer;
            transition: var(--transition-fast);
            flex-shrink: 0;
        }

        .mobile-menu-btn:hover {
            background: var(--primary);
            color: var(--white);
        }

        .mobile-menu-btn.active {
            background: var(--primary);
            color: var(--white);
        }

        /* ============================================
           MAIN NAVIGATION - Desktop
           ============================================ */
        .main-nav {
            background: var(--white);
            border-top: 1px solid var(--gray-light);
            border-bottom: 1px solid var(--gray-light);
            position: fixed;
            top: calc(var(--top-bar-height) + var(--header-height));
            left: 0;
            right: 0;
            height: var(--nav-height);
            z-index: 1015;
            transition: transform var(--transition-slow), top var(--transition);
        }

        .main-header.nav-hidden + .main-nav {
            transform: translateY(calc(-1 * var(--header-height)));
            top: var(--top-bar-height);
        }

        .main-header.scrolled + .main-nav {
            top: var(--header-height);
        }

        .main-header.scrolled.nav-hidden + .main-nav {
            transform: translateY(calc(-1 * (var(--header-height) + var(--nav-height))));
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 16px;
        }

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
            border-radius: 8px;
            transition: var(--transition-fast);
            white-space: nowrap;
            background: transparent;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary-lighter);
            color: var(--primary);
            border-color: var(--accent);
        }

        .nav-link i { 
            font-size: 12px; 
            transition: transform var(--transition-fast);
        }

        .nav-link:hover i { 
            transform: rotate(180deg); 
            color: var(--accent); 
        }

        /* Dropdowns Desktop */
        .dropdown-menu-custom {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            padding: 12px;
            min-width: 280px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            z-index: 1000;
        }

        .nav-item:hover .dropdown-menu-custom {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            color: var(--gray);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition-fast);
            margin-bottom: 4px;
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
            font-size: 16px;
        }

        /* Mega Menu */
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
            padding: 32px;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            z-index: 1000;
        }

        .mega-menu:hover .mega-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .mega-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 32px;
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

        .mega-list { list-style: none; padding: 0; margin: 0; }
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
            transition: var(--transition-fast);
        }

        .mega-list a:hover {
            background: var(--primary-lighter);
            color: var(--primary);
            padding-left: 16px;
        }

        .mega-list a i { font-size: 11px; color: var(--accent); }

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

        /* CTA Nav Desktop */
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
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition-fast);
            border: none;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(15, 76, 58, 0.25);
        }

        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 76, 58, 0.35);
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }

        /* ============================================
           MOBILE NAVIGATION - App Native Style
           ============================================ */
        .mobile-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 76, 58, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1008;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Menu Panel - Style App Native */
        .mobile-nav-panel {
            position: fixed;
            top: 0;
            left: -100%;
            width: 85%;
            max-width: 360px;
            height: 100vh;
            background: var(--white);
            z-index: 1010;
            transition: left var(--transition-slow);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-xl);
        }

        .mobile-nav-panel.active {
            left: 0;
        }

        .mobile-nav-header {
            padding: 20px;
            background: var(--primary);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .mobile-nav-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
        }

        .mobile-nav-close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
        }

        .mobile-user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            object-fit: cover;
        }

        .mobile-user-details h4 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .mobile-user-details p {
            font-size: 12px;
            opacity: 0.9;
            margin: 4px 0 0 0;
        }

        .mobile-nav-content {
            flex: 1;
            padding: 16px;
        }

        .mobile-nav-section {
            margin-bottom: 24px;
        }

        .mobile-nav-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
            margin-bottom: 12px;
            padding-left: 12px;
        }

        .mobile-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mobile-nav-item {
            margin-bottom: 4px;
        }

        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 12px;
            color: var(--dark);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            border-radius: 12px;
            transition: var(--transition-fast);
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .mobile-nav-link:hover, .mobile-nav-link.active {
            background: var(--primary-lighter);
            color: var(--primary);
        }

        .mobile-nav-link i {
            font-size: 20px;
            color: var(--primary);
            width: 24px;
            text-align: center;
        }

        .mobile-nav-link .chevron {
            margin-left: auto;
            font-size: 14px;
            transition: transform var(--transition-fast);
        }

        .mobile-nav-link.active .chevron {
            transform: rotate(90deg);
        }

        /* Submenu Accordion Mobile */
        .mobile-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-slow);
            padding-left: 48px;
        }

        .mobile-submenu.open {
            max-height: 500px;
        }

        .mobile-submenu-item {
            display: block;
            padding: 12px 16px;
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
            border-left: 2px solid var(--gray-light);
            transition: var(--transition-fast);
        }

        .mobile-submenu-item:hover {
            color: var(--primary);
            border-left-color: var(--accent);
            padding-left: 20px;
        }

        .mobile-nav-footer {
            padding: 20px;
            border-top: 1px solid var(--gray-light);
            background: var(--light);
        }

        .mobile-cta-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            text-align: center;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 12px;
            transition: var(--transition-fast);
        }

        .mobile-cta-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .mobile-cta-btn.secondary {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .mobile-cta-btn.secondary:hover {
            background: var(--primary);
            color: white;
        }

        /* ============================================
           BOTTOM NAVIGATION - Mobile App Style
           ============================================ */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--bottom-nav-height);
            background: var(--white);
            border-top: 1px solid var(--gray-light);
            z-index: 1030;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
        }

        .bottom-nav-list {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 100%;
            list-style: none;
            margin: 0;
            padding: 0 8px;
        }

        .bottom-nav-item {
            flex: 1;
            text-align: center;
        }

        .bottom-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 8px;
            color: var(--gray);
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            transition: var(--transition-fast);
            border-radius: 12px;
            height: 100%;
        }

        .bottom-nav-link:hover, .bottom-nav-link.active {
            color: var(--primary);
            background: var(--primary-lighter);
        }

        .bottom-nav-link i {
            font-size: 22px;
        }

        .bottom-nav-link .badge {
            position: absolute;
            top: 6px;
            right: calc(50% - 16px);
            background: var(--danger);
            color: white;
            font-size: 9px;
            font-weight: 700;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ============================================
           RESPONSIVE BREAKPOINTS
           ============================================ */
        
        /* XL Screens */
        @media (min-width: 1400px) {
            .nav-link { padding: 10px 20px; font-size: 14px; }
            .mega-dropdown { max-width: 1200px; padding: 40px; }
        }

        /* Large Screens */
        @media (max-width: 1200px) {
            .search-container { max-width: 350px; }
            .nav-link { padding: 10px 14px; font-size: 12px; }
            .brand-info h1 { font-size: 18px; }
        }

        /* Medium Screens - Tablette */
        @media (max-width: 992px) {
            :root {
                --header-height: 64px;
            }
            
            body {
                padding-top: var(--header-height);
                padding-bottom: var(--bottom-nav-height);
            }
            
            .top-bar { display: none; }
            
            .main-header {
                top: 0;
                height: var(--header-height);
            }
            
            .main-nav { display: none; }
            
            .search-container { max-width: 280px; }
            
            .action-btn span { display: none; }
            .action-btn { padding: 8px; }
            .action-btn i { font-size: 22px; }
            
            .lang-btn span { display: none; }
            
            .mobile-menu-btn { display: flex; }
            
            .bottom-nav { display: block; }
        }

        /* Small Screens - Mobile */
        @media (max-width: 576px) {
            :root {
                --header-height: 60px;
                --bottom-nav-height: 60px;
            }
            
            .header-container { padding: 0 12px; gap: 8px; }
            
            .brand-info h1 { font-size: 16px; }
            .brand-info span { display: none; }
            .brand-logo { width: 38px; height: 38px; }
            
            .search-container { 
                position: absolute;
                top: calc(var(--header-height) + 8px);
                left: 12px;
                right: 12px;
                max-width: none;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: var(--transition);
                z-index: 1018;
            }
            
            .search-container.active {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
            
            .search-input {
                height: 46px;
                box-shadow: var(--shadow-lg);
                border-color: var(--primary);
            }
            
            .header-actions { gap: 4px; }
            
            .mobile-menu-btn { 
                width: 36px; 
                height: 36px; 
                font-size: 20px; 
            }
            
            .mobile-nav-panel { width: 90%; }
        }

        /* Extra Small */
        @media (max-width: 360px) {
            .brand-info h1 { font-size: 14px; }
            .bottom-nav-link { font-size: 10px; }
            .bottom-nav-link i { font-size: 20px; }
        }

        /* Landscape Mobile */
        @media (max-height: 500px) and (max-width: 992px) {
            .bottom-nav { display: none; }
            body { padding-bottom: 0; }
        }

        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Print */
        @media print {
            .main-header, .main-nav, .bottom-nav, .mobile-nav-panel, .top-bar {
                display: none !important;
            }
            body { padding-top: 0; }
        }

       .loading-spinner {
        position: fixed;
        inset: 0;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .spinner-box {
        position: relative;
        width: 120px;
        height: 120px;
    }

    .logo-center {
        position: absolute;
        width: 70px;
        height: 70px;
        object-fit: contain;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border-radius: 50%;
    }

    /* cercle qui tourne avec les couleurs Nufotec */
    .spinner-ring {
        width: 120px;
        height: 120px;
        border: 4px solid var(--gray-light);
        border-top: 4px solid var(--accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .loader-text {
        margin-top: 20px;
        font-weight: 700;
        letter-spacing: 3px;
        color: var(--primary);
        font-size: 1.2rem;
        text-transform: uppercase;
    }

    /* animation rotation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* disparition en douceur */
    .loader-hidden {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.4s ease, visibility 0.4s ease;
    }
    </style>
</head>
<body>
    <!-- Loading Spinner - UNIQUEMENT sur la page d'accueil -->




<!-- Top Bar -->
<div class="top-bar" id="topBar">
    <div class="top-bar-content">
        <div class="top-bar-left">
            <a href="tel:<?= $this->Model->get_setting('site_phone', '+257 79 666 439') ?>">
                <i class="bi bi-telephone-fill"></i>
            </a>
            <div class="top-bar-divider"></div>
            <a href="mailto:<?= $this->Model->get_setting('contact_email_invest', 'nufotecburundi2026@gmail.com') ?>">
                <i class="bi bi-envelope-fill"></i>
                <span><?= $this->Model->get_setting('contact_email_invest', 'nufotecburundi2026@gmail.com') ?></span>
            </a>
        </div>
        <div class="top-bar-right">
            <a href="#">
                <i class="bi bi-geo-alt-fill"></i>
                <span><?= $this->Model->get_setting('adresse_siege', 'Bujumbura, République du Burundi') ?></span>
            </a>
            <div class="top-bar-divider"></div>
            <a href="#">
                <i class="bi bi-clock-fill"></i>
                <span><?= $this->Model->get_setting('horaires_travail', t('working_hours')) ?></span>
            </a>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header" id="mainHeader">
    <div class="header-container">
        <a href="<?= base_url() ?>" class="brand">
            <div class="brand-logo">
                <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" alt="AGF Phytomed">
            </div>
            <div class="brand-info">
                <h1><?= $this->Model->get_setting('site_name', 'AGF Phytomed') ?></h1>
                <span><?= $this->Model->get_setting('span_site_name', 'Natural Health') ?></span>
            </div>
        </a>

        <div class="search-container" id="searchContainer">
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="<?= t('search') ?>" autocomplete="off">
                <button class="search-btn" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div class="search-results-dropdown" id="searchResults"></div>
        </div>

        <div class="header-actions">
            <button class="action-btn d-lg-none" id="searchToggle" title="<?= t('search') ?>">
                <i class="bi bi-search"></i>
            </button>

            <?php
            $logged_in = $this->session->userdata('logged_in') === TRUE;
            $user_name = $this->session->userdata('username');
            $user_photo = $this->session->userdata('photo');
            $initials = '?';
            if ($logged_in && !empty($user_name)) {
                $parts = explode(' ', trim($user_name));
                $initials = count($parts) >= 2 ? 
                    strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1)) : 
                    strtoupper(substr($user_name, 0, 2));
            }
            ?>
            <a href="<?= $logged_in ? base_url('home-patient') : base_url('auth') ?>" class="action-btn d-none d-lg-flex" title="<?= $logged_in ? t('my_account') : t('sign_in') ?>">
                <?php if ($logged_in && !empty($user_photo) && file_exists(FCPATH . 'attachments/Users/' . $user_photo)): ?>
                    <img src="<?= base_url('attachments/Users/' . $user_photo) ?>" alt="Avatar" class="avatar">
                <?php elseif ($logged_in): ?>
                    <img src="<?= base_url('assets/img/default-avatar.png') ?>" alt="Avatar" class="avatar default-avatar">
                <?php else: ?>
                    <i class="bi bi-person-circle"></i>
                <?php endif; ?>
                <span class="d-none d-lg-inline"><?= $logged_in ? t('my_account') : t('sign_in') ?></span>
            </a>

            <div class="lang-selector d-none d-lg-block">
                <?php 
                $current_lang_code = $this->session->userdata('lang') ?? 'fr';
                $languages = [
                    'fr' => ['flag' => 'fr', 'label' => 'FR', 'name' => 'Français'],
                    'en' => ['flag' => 'us', 'label' => 'EN', 'name' => 'English'],
                    'sw' => ['flag' => 'tz', 'label' => 'SW', 'name' => 'Kiswahili']
                ];
                ?>
                <button class="lang-btn" aria-label="<?= t('language') ?>">
                    <img src="https://flagcdn.com/w20/<?= $languages[$current_lang_code]['flag'] ?>.png" 
                         alt="<?= $languages[$current_lang_code]['label'] ?>">
                    <span><?= $languages[$current_lang_code]['label'] ?></span>
                    <i class="bi bi-chevron-down" style="font-size: 10px;"></i>
                </button>
                <div class="lang-dropdown">
                    <?php foreach($languages as $code => $lang_info): ?>
                        <a href="<?= base_url('switch_lang/' . $code) ?>" 
                           class="lang-option <?= $code == $current_lang_code ? 'active' : '' ?>">
                            <img src="https://flagcdn.com/w20/<?= $lang_info['flag'] ?>.png" 
                                 alt="<?= $lang_info['label'] ?>">
                            <span><?= $lang_info['name'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="<?= t('menu') ?>">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</header>

<!-- Navigation Desktop -->
<nav class="main-nav" id="mainNav">
    <div class="nav-container">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?= base_url() ?>" class="nav-link">
                    <i class="bi bi-house-door d-lg-none"></i>
                    <span><?= t('home') ?></span>
                </a>
            </li>

            <li class="nav-item mega-menu">
                <a href="#" class="nav-link">
                    <span><?= t('about') ?></span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="mega-dropdown">
                    <div class="mega-grid">
                        <div class="mega-column">
                            <h3><i class="bi bi-building"></i> <?= t('corporate') ?></h3>
                            <ul class="mega-list">
                                <li><a href="<?= base_url('profile-entreprise') ?>"><i class="bi bi-chevron-right"></i> <?= t('corporate_profile') ?></a></li>
                                <li><a href="<?= base_url('background-strategic-rationale') ?>"><i class="bi bi-chevron-right"></i> <?= t('background_strategy') ?></a></li>
                                <li><a href="<?= base_url('corporate-structure-governance') ?>"><i class="bi bi-chevron-right"></i> <?= t('governance') ?></a></li>
                                <li><a href="<?= base_url('vision-mission') ?>"><i class="bi bi-chevron-right"></i> <?= t('vision_mission') ?></a></li>
                            </ul>
                        </div>
                        <div class="mega-column">
                            <h3><i class="bi bi-leaf"></i> <?= t('sustainability') ?></h3>
                            <ul class="mega-list">
                                <li><a href="<?= base_url('esg_sustainability') ?>"><i class="bi bi-chevron-right"></i> <?= t('esg_sustainability') ?></a></li>
                                <li><a href="<?= base_url('risk-analysis') ?>"><i class="bi bi-chevron-right"></i> <?= t('risk_analysis') ?></a></li>
                                <li><a href="<?= base_url('research_innovation') ?>"><i class="bi bi-chevron-right"></i> <?= t('research_innovation') ?></a></li>
                                <li><a href="<?= base_url('market-outlook') ?>"><i class="bi bi-chevron-right"></i> <?= t('market_outlook') ?></a></li>
                                <li><a href="<?= base_url('digital-growth') ?>"><i class="bi bi-chevron-right"></i> <?= t('digital_growth') ?></a></li>
                            </ul>
                        </div>
                        <div class="mega-column">
                            <h3><i class="bi bi-gear-wide-connected"></i> <?= t('facilities') ?></h3>
                            <ul class="mega-list">
                                <li><a href="<?= base_url('nufotec-phytomed-facility') ?>"><i class="bi bi-chevron-right"></i> <?= t('nufotec_facility') ?></a></li>
                                <li><a href="<?= base_url('manufacturing-facility') ?>"><i class="bi bi-chevron-right"></i> <?= t('manufacturing_facility') ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('products') ?>" class="nav-link">
                    <span><?= t('shop') ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('medicins') ?>" class="nav-link">
                    <span><?= t('teleconsultation') ?></span>
                </a>
            </li>

            <li class="nav-item mega-menu">
                <a href="#" class="nav-link">
                    <span><?= t('investment') ?></span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="mega-dropdown">
                    <div class="mega-grid">
                        <div class="mega-column">
                            <h3><i class="bi bi-handshake"></i> <?= t('partnerships') ?></h3>
                            <ul class="mega-list">
                                <li><a href="<?= base_url('investment-projection') ?>"><i class="bi bi-chevron-right"></i> <?= t('investment_projection') ?></a></li>
                                <li><a href="<?= base_url('investor-commitment') ?>"><i class="bi bi-chevron-right"></i> <?= t('investor_commitment') ?></a></li>
                                <li><a href="<?= base_url('strategic-partnerships') ?>"><i class="bi bi-chevron-right"></i> <?= t('strategic_partnerships') ?></a></li>
                            </ul>
                        </div>
                        <div class="mega-column">
                            <h3><i class="bi bi-bank"></i> <?= t('relations') ?></h3>
                            <ul class="mega-list">
                                <li><a href="<?= base_url('broker-commission') ?>"><i class="bi bi-chevron-right"></i> <?= t('broker_commission') ?></a></li>
                                <li><a href="<?= base_url('brokers-form') ?>"><i class="bi bi-chevron-right"></i> <?= t('become_broker') ?> <span class="badge-pro">Pro</span></a></li>
                                <li><a href="<?= base_url('investors-form') ?>"><i class="bi bi-chevron-right"></i> <?= t('become_partner') ?> <span class="badge-pro">Pro</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('media') ?>" class="nav-link">
                    <span><?= t('media') ?></span>
                </a>
            </li>
        </ul>

        <div class="nav-cta">
            <a href="<?= base_url('contact') ?>" class="btn-nav-primary">
                <i class="bi bi-headset"></i>
                <span><?= t('contact') ?></span>
            </a>
        </div>
    </div>
</nav>

<!-- Mobile Navigation Panel -->
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="mobile-nav-panel" id="mobileNavPanel">
    <div class="mobile-nav-header">
        <button class="mobile-nav-close" id="mobileNavClose">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="mobile-user-info">
            <?php if ($logged_in && !empty($user_photo) && file_exists(FCPATH . 'attachments/Users/' . $user_photo)): ?>
                <img src="<?= base_url('attachments/Users/' . $user_photo) ?>" alt="Avatar" class="mobile-user-avatar">
            <?php else: ?>
                <div class="mobile-user-avatar" style="background: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700;">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            <div class="mobile-user-details">
                <h4><?= $logged_in ? $user_name : t('guest') ?></h4>
                <p><?= $logged_in ? t('connected') : t('sign_in_message') ?></p>
            </div>
        </div>
    </div>

    <div class="mobile-nav-content">
        <div class="mobile-nav-section">
            <div class="mobile-nav-title"><?= t('main_menu') ?></div>
            <ul class="mobile-nav-list">
                <li class="mobile-nav-item">
                    <a href="<?= base_url() ?>" class="mobile-nav-link">
                        <i class="bi bi-house-door"></i>
                        <span><?= t('home') ?></span>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <button class="mobile-nav-link has-submenu" data-submenu="about">
                        <i class="bi bi-building"></i>
                        <span><?= t('about') ?></span>
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <div class="mobile-submenu" id="submenu-about">
                        <a href="<?= base_url('profile-entreprise') ?>" class="mobile-submenu-item"><?= t('corporate_profile') ?></a>
                        <a href="<?= base_url('background-strategic-rationale') ?>" class="mobile-submenu-item"><?= t('background_strategy') ?></a>
                        <a href="<?= base_url('corporate-structure-governance') ?>" class="mobile-submenu-item"><?= t('governance') ?></a>
                        <a href="<?= base_url('vision-mission') ?>" class="mobile-submenu-item"><?= t('vision_mission') ?></a>
                        <a href="<?= base_url('esg_sustainability') ?>" class="mobile-submenu-item"><?= t('esg_sustainability') ?></a>
                        <a href="<?= base_url('risk-analysis') ?>" class="mobile-submenu-item"><?= t('risk_analysis') ?></a>
                        <a href="<?= base_url('research_innovation') ?>" class="mobile-submenu-item"><?= t('research_innovation') ?></a>
                        <a href="<?= base_url('nufotec-phytomed-facility') ?>" class="mobile-submenu-item"><?= t('nufotec_facility') ?></a>
                        <a href="<?= base_url('digital-growth') ?>" class="mobile-submenu-item"><?= t('digital_growth') ?></a>
                        <a href="<?= base_url('market-outlook') ?>" class="mobile-submenu-item"><?= t('market_outlook') ?></a>
                    </div>
                </li>
                <li class="mobile-nav-item">
                    <a href="<?= base_url('medicins') ?>" class="mobile-nav-link">
                        <i class="bi bi-camera-video"></i>
                        <span><?= t('teleconsultation') ?></span>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <button class="mobile-nav-link has-submenu" data-submenu="investment">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span><?= t('investment') ?></span>
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <div class="mobile-submenu" id="submenu-investment">
                        <a href="<?= base_url('investment-projection') ?>" class="mobile-submenu-item"><?= t('investment_projection') ?></a>
                        <a href="<?= base_url('investor-commitment') ?>" class="mobile-submenu-item"><?= t('investor_commitment') ?></a>
                        <a href="<?= base_url('strategic-partnerships') ?>" class="mobile-submenu-item"><?= t('strategic_partnerships') ?></a>
                        <a href="<?= base_url('broker-commission') ?>" class="mobile-submenu-item"><?= t('broker_commission') ?></a>
                        <a href="<?= base_url('brokers-form') ?>" class="mobile-submenu-item"><?= t('become_broker') ?></a>
                        <a href="<?= base_url('investors-form') ?>" class="mobile-submenu-item"><?= t('become_partner') ?></a>
                    </div>
                </li>
                <li class="mobile-nav-item">
                    <a href="<?= base_url('blog') ?>" class="mobile-nav-link">
                        <i class="bi bi-newspaper"></i>
                        <span><?= t('news') ?></span>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="<?= base_url('faq') ?>" class="mobile-nav-link">
                        <i class="bi bi-question-circle"></i>
                        <span><?= t('faq') ?></span>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="<?= base_url('contact') ?>" class="mobile-nav-link">
                        <i class="bi bi-envelope"></i>
                        <span><?= t('contact') ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="mobile-nav-section">
            <div class="mobile-nav-title"><?= t('settings') ?></div>
            <ul class="mobile-nav-list">
                <li class="mobile-nav-item">
                    <button class="mobile-nav-link has-submenu" data-submenu="lang">
                        <i class="bi bi-globe"></i>
                        <span><?= t('language') ?></span>
                        <span style="margin-left: auto; margin-right: 8px; font-size: 12px; color: var(--gray);">
                            <?= strtoupper($this->session->userdata('lang') ?? 'fr') ?>
                        </span>
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <div class="mobile-submenu" id="submenu-lang">
                        <?php foreach($languages as $code => $lang_info): ?>
                            <a href="<?= base_url('switch_lang/' . $code) ?>" 
                               class="mobile-submenu-item" 
                               style="display: flex; align-items: center; gap: 8px;">
                                <img src="https://flagcdn.com/w20/<?= $lang_info['flag'] ?>.png" 
                                     alt="<?= $lang_info['label'] ?>" 
                                     style="width: 20px;"> 
                                <?= $lang_info['name'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="mobile-nav-footer" style="margin-bottom: 30px;">
        <a href="<?= base_url('contact') ?>" class="mobile-cta-btn">
            <i class="bi bi-headset"></i> <?= t('contact_us') ?>
        </a>
        <?php if (!$logged_in): ?>
            <a href="<?= base_url('auth') ?>" class="mobile-cta-btn secondary">
                <i class="bi bi-box-arrow-in-right"></i> <?= t('sign_in') ?>
            </a>
        <?php else: ?>
            <a href="<?= base_url('auth/logout') ?>" class="mobile-cta-btn secondary">
                <i class="bi bi-box-arrow-right"></i> <?= t('sign_out') ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
// ============================================
// RECHERCHE AJAX - URLs sans préfixe
// ============================================
function performSearch(query) {
    searchResults.innerHTML = '<div class="search-loading">Recherche...</div>';
    searchResults.classList.add('active');

    fetch('<?= base_url("search/ajax_search") ?>?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => displayResults(data, query))
        .catch(error => {
            console.error('Erreur recherche:', error);
            searchResults.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--danger);">Erreur de recherche</div>';
        });
}

function displayResults(data, query) {
    let html = '';
    let hasResults = false;

    if (data.produits?.length > 0) {
        html += '<div class="result-category">Produits</div>';
        data.produits.slice(0, 4).forEach(item => {
            html += `<a href="<?= base_url('product/') ?>${item.slug}" class="result-item">
                        <i class="bi bi-box-seam"></i>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.titre)}</div>
                            <div class="result-desc">${escapeHtml(item.extrait?.substring(0, 50) || '')}</div>
                        </div>
                    </a>`;
            hasResults = true;
        });
    }

    if (data.actualites?.length > 0) {
        html += '<div class="result-category">Actualités</div>';
        data.actualites.slice(0, 3).forEach(item => {
            html += `<a href="<?= base_url('actualite/') ?>${item.slug}" class="result-item">
                        <i class="bi bi-newspaper"></i>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.titre)}</div>
                            <div class="result-desc">${escapeHtml(item.extrait?.substring(0, 50) || '')}</div>
                        </div>
                    </a>`;
            hasResults = true;
        });
    }

    if (data.pages?.length > 0) {
        html += '<div class="result-category">Pages</div>';
        data.pages.slice(0, 3).forEach(item => {
            html += `<a href="<?= base_url('') ?>${item.slug}" class="result-item">
                        <i class="bi bi-file-text"></i>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.titre)}</div>
                            <div class="result-desc">${escapeHtml(item.extrait?.substring(0, 50) || '')}</div>
                        </div>
                    </a>`;
            hasResults = true;
        });
    }

    if (!hasResults) {
        html = `<div style="padding: 30px; text-align: center;">Aucun résultat trouvé pour "${escapeHtml(query)}"</div>`;
    }
    searchResults.innerHTML = html;
}

// Le reste de votre JS (swipe, header scroll, etc.) reste identique
</script>
</body>
</html>