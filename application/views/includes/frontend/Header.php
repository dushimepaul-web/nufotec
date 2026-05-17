<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php 
    // Vérifier si on est sur une page produit
    $is_product_page = (isset($product) && !empty($product) && isset($product['title']));
    
    if ($is_product_page): 
        $product_title = htmlspecialchars($product['title']) . ' - ' . htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'));
        $product_desc = !empty($product['description']) ? substr(htmlspecialchars($product['description']), 0, 160) : htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Produit NUFOTEC'));
        $product_image = base_url('attachments/Products/'.$product['main_image']);
        $product_url = base_url('Products/detail/'.($product['slug'] ?? $product['id']));
    ?>
    
    <title><?= $product_title ?></title>
    <meta name="description" content="<?= $product_desc ?>">
    <meta name="keywords" content="<?= htmlspecialchars($product['title']) ?>, phytomédicaments, NUFOTEC, Burundi">
    <link rel="canonical" href="<?= $product_url ?>">
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?= $product_url ?>">
    <meta property="og:title" content="<?= htmlspecialchars($product['title']) ?>">
    <meta property="og:description" content="<?= $product_desc ?>">
    <meta property="og:image" content="<?= $product_image ?>">
    <meta property="og:image:secure_url" content="<?= $product_image ?>">
    <meta property="og:site_name" content="NUFOTEC">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($product['title']) ?>">
    <meta name="twitter:description" content="<?= $product_desc ?>">
    <meta name="twitter:image" content="<?= $product_image ?>">
    <?php if (!empty($product['price'])): ?>
    <meta property="product:price:amount" content="<?= preg_replace('/[^0-9.,]/', '', $product['price']) ?>">
    <meta property="product:price:currency" content="BIF">
    <?php endif; ?>
    
    <?php else: 
        $site_title = htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'));
        $site_desc = htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi'));
        $site_logo = $this->Model->get_setting('site_logo', 'assets/fro.png');
        $site_image = base_url('attachments/Configurations/' . $site_logo);
    ?>
    
    <title><?= $site_title ?></title>
    <meta name="description" content="<?= $site_desc ?>">
    <meta name="keywords" content="<?= htmlspecialchars($this->Model->get_setting('site_keywords', 'phytomédicaments, agro-industrie, Burundi, santé naturelle, nutrition, NUFOTEC')) ?>">
    <link rel="canonical" href="<?= base_url() ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url() ?>">
    <meta property="og:title" content="<?= $site_title ?>">
    <meta property="og:description" content="<?= $site_desc ?>">
    <meta property="og:image" content="<?= $site_image ?>">
    <meta property="og:image:secure_url" content="<?= str_replace('http://', 'https://', $site_image) ?>">
    <meta property="og:site_name" content="<?= $site_title ?>">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $site_title ?>">
    <meta name="twitter:description" content="<?= $site_desc ?>">
    <meta name="twitter:image" content="<?= $site_image ?>">
    
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
/* ============================================ */
/* CACHER COMPLÈTEMENT L'INTERFACE GOOGLE TRANSLATE */
/* ============================================ */

/* Cacher la barre en haut */
.goog-te-banner-frame.skiptranslate,
.goog-te-banner-frame,
.goog-te-banner,
.goog-te-banner-frame.skiptranslate iframe,
body > .skiptranslate {
    display: none !important;
    height: 0 !important;
    width: 0 !important;
    visibility: hidden !important;
    opacity: 0 !important;
    position: absolute !important;
    top: -9999px !important;
    left: -9999px !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Supprimer l'espace que Google Translate laisse en haut */
body {
    top: 0 !important;
    position: relative !important;
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* Cacher les notifications */
.goog-te-spinner-pos,
.goog-tooltip,
.goog-text-highlight,
.goog-te-balloon-frame,
.goog-te-balloon-frame div,
.yt-uix-overlay {
    display: none !important;
}

/* Forcer le body à ne pas avoir de marge */
body.skiptranslate {
    top: 0 !important;
    position: static !important;
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* Cacher les iframes flottantes */
iframe.goog-te-banner-frame,
div.goog-te-banner-frame,
div[class*="goog-te-banner"] {
    display: none !important;
}

/* Cacher le logo Google */
.goog-logo-link {
    display: none !important;
}

.goog-te-gadget {
    color: transparent !important;
    font-size: 0 !important;
}

/* Cacher la barre de chargement */
.goog-te-spinner-pos {
    display: none !important;
}

/* Réinitialiser les marges du body */
html body {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* ============================================ */
/* BOUTON DE LANGUE PERSONNALISÉ - DESIGN MODERNE */
/* ============================================ */
.lang-selector-custom {
    position: relative;
    margin-left: 8px;
}

.custom-language-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--white, #ffffff);
    border: 1px solid var(--gray-light, #e2e8f0);
    border-radius: 12px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--dark, #1a1a1a);
    transition: all 0.2s ease;
}

.custom-language-btn:hover {
    border-color: var(--accent, #d4af37);
    background: var(--primary-lighter, #e8f5f0);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.custom-language-btn img {
    width: 20px;
    height: 15px;
    border-radius: 2px;
    object-fit: cover;
}

.custom-language-btn i {
    font-size: 10px;
    color: var(--gray, #64748b);
    transition: transform 0.2s ease;
}

.custom-language-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: var(--white, #ffffff);
    border-radius: 16px;
    box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.15);
    padding: 8px;
    min-width: 220px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 1000;
    border: 1px solid var(--gray-light, #e2e8f0);
}

.custom-language-dropdown.active {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

.lang-option-custom {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 10px 14px !important;
    border-radius: 10px !important;
    text-decoration: none !important;
    color: var(--dark, #1a1a1a) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    width: 100% !important;
    border: none !important;
    background: transparent !important;
    text-align: left !important;
}

.lang-option-custom:hover {
    background: var(--primary-lighter, #e8f5f0) !important;
    color: var(--primary, #0f4c3a) !important;
    transform: translateX(4px) !important;
}

.lang-option-custom img {
    width: 22px !important;
    height: 16px !important;
    border-radius: 3px !important;
    object-fit: cover !important;
}

/* ============================================ */
/* MENU MOBILE - VERSION AVEC SOUS-MENU LANGUE */
/* ============================================ */
@media (max-width: 992px) {
    /* Cacher le sélecteur de langue desktop sur mobile */
    .desktop-lang-selector {
        display: none !important;
    }
    
    /* Barre de recherche: cachée par défaut, visible quand active */
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
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
    }
    
    /* Style des options de langue dans le sous-menu mobile */
    .mobile-lang-option {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 12px 16px !important;
        border-radius: 10px !important;
        width: 100% !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        color: var(--dark, #1a1a1a) !important;
        transition: all 0.2s ease !important;
        text-align: left !important;
    }
    
    .mobile-lang-option:hover {
        background: var(--primary-lighter, #e8f5f0) !important;
        transform: translateX(4px) !important;
    }
    
    .mobile-lang-option img {
        width: 24px !important;
        height: 16px !important;
        border-radius: 3px !important;
        object-fit: cover !important;
    }
    
    /* Forcer l'affichage correct du sous-menu langue */
    #submenu-mobile-lang {
        padding-left: 0 !important;
    }
    
    #submenu-mobile-lang .mobile-lang-option {
        padding-left: 48px !important;
    }
}

/* Desktop: afficher le sélecteur normal */
@media (min-width: 993px) {
    .desktop-lang-selector {
        display: block;
    }
    
    /* Sur desktop, la recherche est toujours visible */
    .search-container {
        opacity: 1 !important;
        visibility: visible !important;
        position: relative !important;
        transform: none !important;
    }
}

/* Mobile responsive pour le bouton */
@media (max-width: 768px) {
    .custom-language-btn {
        padding: 6px 10px;
        font-size: 12px;
    }
    .custom-language-btn img {
        width: 18px;
        height: 12px;
    }
    .custom-language-dropdown {
        min-width: 200px;
    }
}

/* ============================================ */
/* STYLES EXISTANTS (suite) */
/* ============================================ */
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
    display: flex;
    align-items: center;
    gap: 6px;
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

@media (min-width: 1400px) {
    .nav-link { padding: 10px 20px; font-size: 14px; }
    .mega-dropdown { max-width: 1200px; padding: 40px; }
}

@media (max-width: 1200px) {
    .search-container { max-width: 350px; }
    .nav-link { padding: 10px 14px; font-size: 12px; }
    .brand-info h1 { font-size: 18px; }
}

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
    
    .action-btn span { display: none; }
    .action-btn { padding: 8px; }
    .action-btn i { font-size: 22px; }
    
    .mobile-menu-btn { display: flex; }
    
    .bottom-nav { display: block; }
    
    /* Sur mobile, la recherche n'a plus de largeur fixe */
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
}

@media (max-width: 576px) {
    :root {
        --header-height: 60px;
        --bottom-nav-height: 60px;
    }
    
    .header-container { padding: 0 12px; gap: 8px; }
    
    .brand-info h1 { font-size: 16px; }
    .brand-info span { display: none; }
    .brand-logo { width: 38px; height: 38px; }
    
    .header-actions { gap: 4px; }
    
    .mobile-menu-btn { 
        width: 36px; 
        height: 36px; 
        font-size: 20px; 
    }
    
    .mobile-nav-panel { width: 90%; }
}

@media (max-width: 360px) {
    .brand-info h1 { font-size: 14px; }
    .bottom-nav-link { font-size: 10px; }
    .bottom-nav-link i { font-size: 20px; }
}

@media (max-height: 500px) and (max-width: 992px) {
    .bottom-nav { display: none; }
    body { padding-bottom: 0; }
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

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

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loader-hidden {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease, visibility 0.4s ease;
}
</style>
</head>
<body>

<!-- Google Translate caché (nécessaire pour la traduction) -->
<div class="google-translate-container">
    <div id="google_translate_element"></div>
</div>

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
                <span><?= $this->Model->get_setting('horaires_travail', 'Lun-Ven: 8h-17h') ?></span>
            </a>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header" id="mainHeader">
    <div class="header-container">
        <a href="<?= base_url() ?>" class="brand">
            <div class="brand-logo">
                <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" alt="NUFOTEC">
            </div>
            <div class="brand-info">
                <h1><?= $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI') ?></h1>
                <span><?= $this->Model->get_setting('span_site_name', 'Natural Health') ?></span>
            </div>
        </a>

        <div class="search-container" id="searchContainer">
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="Rechercher..." autocomplete="off">
                <button class="search-btn" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div class="search-results-dropdown" id="searchResults"></div>
        </div>

        <div class="header-actions">
            <button class="action-btn d-lg-none" id="searchToggle" title="Rechercher">
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
            <a href="<?= $logged_in ? base_url('home-patient') : base_url('auth') ?>" class="action-btn d-none d-lg-flex" title="<?= $logged_in ? 'Mon compte' : 'Connexion' ?>">
                <?php if ($logged_in && !empty($user_photo) && file_exists(FCPATH . 'attachments/Users/' . $user_photo)): ?>
                    <img src="<?= base_url('attachments/Users/' . $user_photo) ?>" alt="Avatar" class="avatar">
                <?php elseif ($logged_in): ?>
                    <img src="<?= base_url('assets/img/default-avatar.png') ?>" alt="Avatar" class="avatar default-avatar">
                <?php else: ?>
                    <i class="bi bi-person-circle"></i>
                <?php endif; ?>
                <span class="d-none d-lg-inline"><?= $logged_in ? 'Mon compte' : 'Connexion' ?></span>
            </a>

            <!-- BOUTON DE LANGUE PERSONNALISÉ - UNIQUEMENT SUR DESKTOP (31 LANGUES) -->
<div class="lang-selector-custom desktop-lang-selector">
    <button class="custom-language-btn" id="customLanguageBtn">
        <img src="https://flagcdn.com/w20/fr.png" alt="Français" id="currentLangFlag">
        <span id="currentLangLabel">Français</span>
        <i class="bi bi-chevron-down"></i>
    </button>
    <div class="custom-language-dropdown" id="customLanguageDropdown" style="max-height: 400px; overflow-y: auto;">
        <!-- Langues principales -->
        <button class="lang-option-custom" data-lang="fr" data-flag="fr" data-label="Français">
            <img src="https://flagcdn.com/w20/fr.png" alt="Français"> Français
        </button>
        <button class="lang-option-custom" data-lang="en" data-flag="us" data-label="English">
            <img src="https://flagcdn.com/w20/us.png" alt="English"> English
        </button>
        <button class="lang-option-custom" data-lang="rn" data-flag="bi" data-label="Kirundi">
            <img src="https://flagcdn.com/w20/bi.png" alt="Kirundi"> Kirundi
        </button>
        <button class="lang-option-custom" data-lang="sw" data-flag="tz" data-label="Kiswahili">
            <img src="https://flagcdn.com/w20/tz.png" alt="Kiswahili"> Kiswahili
        </button>
        
        <!-- 20+ langues supplémentaires -->
        <button class="lang-option-custom" data-lang="ar" data-flag="sa" data-label="العربية">
            <img src="https://flagcdn.com/w20/sa.png" alt="العربية"> العربية
        </button>
        <button class="lang-option-custom" data-lang="de" data-flag="de" data-label="Deutsch">
            <img src="https://flagcdn.com/w20/de.png" alt="Deutsch"> Deutsch
        </button>
        <button class="lang-option-custom" data-lang="es" data-flag="es" data-label="Español">
            <img src="https://flagcdn.com/w20/es.png" alt="Español"> Español
        </button>
        <button class="lang-option-custom" data-lang="pt" data-flag="pt" data-label="Português">
            <img src="https://flagcdn.com/w20/pt.png" alt="Português"> Português
        </button>
        <button class="lang-option-custom" data-lang="it" data-flag="it" data-label="Italiano">
            <img src="https://flagcdn.com/w20/it.png" alt="Italiano"> Italiano
        </button>
        <button class="lang-option-custom" data-lang="zh-CN" data-flag="cn" data-label="中文">
            <img src="https://flagcdn.com/w20/cn.png" alt="中文"> 中文
        </button>
        <button class="lang-option-custom" data-lang="ru" data-flag="ru" data-label="Русский">
            <img src="https://flagcdn.com/w20/ru.png" alt="Русский"> Русский
        </button>
        <button class="lang-option-custom" data-lang="nl" data-flag="nl" data-label="Nederlands">
            <img src="https://flagcdn.com/w20/nl.png" alt="Nederlands"> Nederlands
        </button>
        <button class="lang-option-custom" data-lang="pl" data-flag="pl" data-label="Polski">
            <img src="https://flagcdn.com/w20/pl.png" alt="Polski"> Polski
        </button>
        <button class="lang-option-custom" data-lang="tr" data-flag="tr" data-label="Türkçe">
            <img src="https://flagcdn.com/w20/tr.png" alt="Türkçe"> Türkçe
        </button>
        <button class="lang-option-custom" data-lang="ja" data-flag="jp" data-label="日本語">
            <img src="https://flagcdn.com/w20/jp.png" alt="日本語"> 日本語
        </button>
        <button class="lang-option-custom" data-lang="ko" data-flag="kr" data-label="한국어">
            <img src="https://flagcdn.com/w20/kr.png" alt="한국어"> 한국어
        </button>
        <button class="lang-option-custom" data-lang="hi" data-flag="in" data-label="हिन्दी">
            <img src="https://flagcdn.com/w20/in.png" alt="हिन्दी"> हिन्दी
        </button>
        <button class="lang-option-custom" data-lang="vi" data-flag="vn" data-label="Tiếng Việt">
            <img src="https://flagcdn.com/w20/vn.png" alt="Tiếng Việt"> Tiếng Việt
        </button>
        <button class="lang-option-custom" data-lang="th" data-flag="th" data-label="ภาษาไทย">
            <img src="https://flagcdn.com/w20/th.png" alt="ภาษาไทย"> ภาษาไทย
        </button>
        <button class="lang-option-custom" data-lang="el" data-flag="gr" data-label="Ελληνικά">
            <img src="https://flagcdn.com/w20/gr.png" alt="Ελληνικά"> Ελληνικά
        </button>
        <button class="lang-option-custom" data-lang="he" data-flag="il" data-label="עברית">
            <img src="https://flagcdn.com/w20/il.png" alt="עברית"> עברית
        </button>
        <button class="lang-option-custom" data-lang="sv" data-flag="se" data-label="Svenska">
            <img src="https://flagcdn.com/w20/se.png" alt="Svenska"> Svenska
        </button>
        <button class="lang-option-custom" data-lang="da" data-flag="dk" data-label="Dansk">
            <img src="https://flagcdn.com/w20/dk.png" alt="Dansk"> Dansk
        </button>
        <button class="lang-option-custom" data-lang="no" data-flag="no" data-label="Norsk">
            <img src="https://flagcdn.com/w20/no.png" alt="Norsk"> Norsk
        </button>
        <button class="lang-option-custom" data-lang="fi" data-flag="fi" data-label="Suomi">
            <img src="https://flagcdn.com/w20/fi.png" alt="Suomi"> Suomi
        </button>
        <button class="lang-option-custom" data-lang="cs" data-flag="cz" data-label="Čeština">
            <img src="https://flagcdn.com/w20/cz.png" alt="Čeština"> Čeština
        </button>
        <button class="lang-option-custom" data-lang="hu" data-flag="hu" data-label="Magyar">
            <img src="https://flagcdn.com/w20/hu.png" alt="Magyar"> Magyar
        </button>
        <button class="lang-option-custom" data-lang="ro" data-flag="ro" data-label="Română">
            <img src="https://flagcdn.com/w20/ro.png" alt="Română"> Română
        </button>
        <button class="lang-option-custom" data-lang="uk" data-flag="ua" data-label="Українська">
            <img src="https://flagcdn.com/w20/ua.png" alt="Українська"> Українська
        </button>
    </div>
</div>

            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">
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
                <a href="<?= base_url('') ?>" class="nav-link">
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
                                <!--<li><a href="<?= base_url('Profile-Entreprise') ?>"><i class="bi bi-chevron-right"></i> <?= t('corporate_profile') ?></a></li>-->
                                <li><a href="<?= base_url('background-strategic-rationale') ?>"><i class="bi bi-chevron-right"></i> <?= t('background_strategy') ?></a></li>
                                <li><a href="<?= base_url('corporate-structure-governance') ?>"><i class="bi bi-chevron-right"></i> <?= t('governance') ?></a></li>
                                <li><a href="<?= base_url('vision-mission') ?>"><i class="bi bi-chevron-right"></i> <?= t('vision_mission') ?></a></li>
                            </ul>
                        </div>
                        <div class="mega-column">
                            <h3><i class="bi bi-leaf"></i> <?= t('sustainability') ?></h3>
                            <ul class="mega-list">
                                <li><a href="<?= base_url('esg_Sustainability') ?>"><i class="bi bi-chevron-right"></i> <?= t('esg_sustainability') ?></a></li>
                                <li><a href="<?= base_url('risk-analysis') ?>"><i class="bi bi-chevron-right"></i> <?= t('risk_analysis') ?></a></li>
                                <li><a href="<?= base_url('Research_Innovation') ?>"><i class="bi bi-chevron-right"></i> <?= t('research_innovation') ?></a></li>
                                <li><a href="<?= base_url('market-outlook') ?>"><i class="bi bi-chevron-right"></i> <?= t('market_outlook') ?></a></li>
                                <li><a href="<?= base_url('digital-growth') ?>"><i class="bi bi-chevron-right"></i> <?= t('digital_growth') ?></a></li>
                            </ul>
                        </div>
                        <div class="mega-column">
                            <h3><i class="bi bi-gear-wide-connected"></i> <?= t('facilities') ?></h3>
                            <ul class="mega-list">
                                <li><a href="<?= base_url('nufotec-phytomed-facility') ?>"><i class="bi bi-chevron-right"></i> <?= t('nufotec_facility') ?></a></li>
                                <!--<li><a href="<?= base_url('manufacturing-facility') ?>"><i class="bi bi-chevron-right"></i> <?= t('manufacturing_facility') ?></a></li>-->
                            </ul>
                        </div>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('Products') ?>" class="nav-link">
                    <span><?= t('shop') ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('Medicins') ?>" class="nav-link">
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
                                <!--<li><a href="<?= base_url('Brokers-form') ?>"><i class="bi bi-chevron-right"></i> <?= t('become_broker') ?> <span class="badge-pro">Pro</span></a></li>
                                <li><a href="<?= base_url('Investors-form') ?>"><i class="bi bi-chevron-right"></i> <?= t('become_partner') ?> <span class="badge-pro">Pro</span></a></li>-->
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
            <a href="<?= base_url('Home/Contact') ?>" class="btn-nav-primary">
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
                <h4><?= $logged_in ? htmlspecialchars($user_name) : 'Invité' ?></h4>
                <p><?= $logged_in ? 'Connecté' : 'Connectez-vous' ?></p>
            </div>
        </div>
    </div>

    <div class="mobile-nav-content">
        <div class="mobile-nav-section">
            <div class="mobile-nav-title">Menu principal</div>
            <ul class="mobile-nav-list">
                <li class="mobile-nav-item">
                    <a href="<?= base_url() ?>" class="mobile-nav-link">
                        <i class="bi bi-house-door"></i>
                        <span>Accueil</span>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <button class="mobile-nav-link has-submenu" data-submenu="about">
                        <i class="bi bi-building"></i>
                        <span>À propos</span>
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <div class="mobile-submenu" id="submenu-about">
                        <a href="<?= base_url('Profile-Entreprise') ?>" class="mobile-submenu-item">Profil entreprise</a>
                        <a href="<?= base_url('vision-mission') ?>" class="mobile-submenu-item">Vision & Mission</a>
                        <a href="<?= base_url('corporate-structure-governance') ?>" class="mobile-submenu-item">Gouvernance</a>
                        <a href="<?= base_url('esg_Sustainability') ?>" class="mobile-submenu-item">Durabilité ESG</a>
                    </div>
                </li>
                <li class="mobile-nav-item">
                    <a href="<?= base_url('Products') ?>" class="mobile-nav-link">
                        <i class="bi bi-box-seam"></i>
                        <span>Produits</span>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="<?= base_url('Medicins') ?>" class="mobile-nav-link">
                        <i class="bi bi-camera-video"></i>
                        <span>Téléconsultation</span>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="<?= base_url('Home/Contact') ?>" class="mobile-nav-link">
                        <i class="bi bi-envelope"></i>
                        <span>Contact</span>
                    </a>
                </li>
            </ul>
        </div>

       <!-- Section Langue dans le menu mobile - AVEC SOUS-MENU (31 LANGUES) -->
<div class="mobile-nav-section">
    <div class="mobile-nav-title">
        <i class="bi bi-globe"></i> Langue
    </div>
    <ul class="mobile-nav-list">
        <li class="mobile-nav-item">
            <button class="mobile-nav-link has-submenu" data-submenu="mobile-lang">
                <i class="bi bi-translate"></i>
                <span>Changer de langue</span>
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <div class="mobile-submenu" id="submenu-mobile-lang" style="max-height: 400px; overflow-y: auto;">
                <!-- Langues principales -->
                <button class="mobile-submenu-item mobile-lang-option" data-lang="fr" data-flag="fr" data-label="Français">
                    <img src="https://flagcdn.com/w20/fr.png" alt="Français"> Français (Français)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="en" data-flag="us" data-label="English">
                    <img src="https://flagcdn.com/w20/us.png" alt="English"> English (Anglais)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="rn" data-flag="bi" data-label="Kirundi">
                    <img src="https://flagcdn.com/w20/bi.png" alt="Kirundi"> Kirundi (Kirundi)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="sw" data-flag="tz" data-label="Kiswahili">
                    <img src="https://flagcdn.com/w20/tz.png" alt="Kiswahili"> Kiswahili (Swahili)
                </button>
                
                <!-- Langues supplémentaires (20+ langues) -->
                <button class="mobile-submenu-item mobile-lang-option" data-lang="ar" data-flag="sa" data-label="العربية">
                    <img src="https://flagcdn.com/w20/sa.png" alt="العربية"> العربية (Arabe)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="de" data-flag="de" data-label="Deutsch">
                    <img src="https://flagcdn.com/w20/de.png" alt="Deutsch"> Deutsch (Allemand)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="es" data-flag="es" data-label="Español">
                    <img src="https://flagcdn.com/w20/es.png" alt="Español"> Español (Espagnol)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="pt" data-flag="pt" data-label="Português">
                    <img src="https://flagcdn.com/w20/pt.png" alt="Português"> Português (Portugais)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="it" data-flag="it" data-label="Italiano">
                    <img src="https://flagcdn.com/w20/it.png" alt="Italiano"> Italiano (Italien)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="zh-CN" data-flag="cn" data-label="中文">
                    <img src="https://flagcdn.com/w20/cn.png" alt="中文"> 中文 (Chinois)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="ru" data-flag="ru" data-label="Русский">
                    <img src="https://flagcdn.com/w20/ru.png" alt="Русский"> Русский (Russe)
                </button>
                
                <!-- Nouvelles langues ajoutées -->
                <button class="mobile-submenu-item mobile-lang-option" data-lang="nl" data-flag="nl" data-label="Nederlands">
                    <img src="https://flagcdn.com/w20/nl.png" alt="Nederlands"> Nederlands (Néerlandais)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="pl" data-flag="pl" data-label="Polski">
                    <img src="https://flagcdn.com/w20/pl.png" alt="Polski"> Polski (Polonais)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="tr" data-flag="tr" data-label="Türkçe">
                    <img src="https://flagcdn.com/w20/tr.png" alt="Türkçe"> Türkçe (Turc)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="ja" data-flag="jp" data-label="日本語">
                    <img src="https://flagcdn.com/w20/jp.png" alt="日本語"> 日本語 (Japonais)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="ko" data-flag="kr" data-label="한국어">
                    <img src="https://flagcdn.com/w20/kr.png" alt="한국어"> 한국어 (Coréen)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="hi" data-flag="in" data-label="हिन्दी">
                    <img src="https://flagcdn.com/w20/in.png" alt="हिन्दी"> हिन्दी (Hindi)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="vi" data-flag="vn" data-label="Tiếng Việt">
                    <img src="https://flagcdn.com/w20/vn.png" alt="Tiếng Việt"> Tiếng Việt (Vietnamien)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="th" data-flag="th" data-label="ภาษาไทย">
                    <img src="https://flagcdn.com/w20/th.png" alt="ภาษาไทย"> ภาษาไทย (Thaï)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="el" data-flag="gr" data-label="Ελληνικά">
                    <img src="https://flagcdn.com/w20/gr.png" alt="Ελληνικά"> Ελληνικά (Grec)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="he" data-flag="il" data-label="עברית">
                    <img src="https://flagcdn.com/w20/il.png" alt="עברית"> עברית (Hébreu)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="sv" data-flag="se" data-label="Svenska">
                    <img src="https://flagcdn.com/w20/se.png" alt="Svenska"> Svenska (Suédois)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="da" data-flag="dk" data-label="Dansk">
                    <img src="https://flagcdn.com/w20/dk.png" alt="Dansk"> Dansk (Danois)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="no" data-flag="no" data-label="Norsk">
                    <img src="https://flagcdn.com/w20/no.png" alt="Norsk"> Norsk (Norvégien)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="fi" data-flag="fi" data-label="Suomi">
                    <img src="https://flagcdn.com/w20/fi.png" alt="Suomi"> Suomi (Finnois)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="cs" data-flag="cz" data-label="Čeština">
                    <img src="https://flagcdn.com/w20/cz.png" alt="Čeština"> Čeština (Tchèque)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="hu" data-flag="hu" data-label="Magyar">
                    <img src="https://flagcdn.com/w20/hu.png" alt="Magyar"> Magyar (Hongrois)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="ro" data-flag="ro" data-label="Română">
                    <img src="https://flagcdn.com/w20/ro.png" alt="Română"> Română (Roumain)
                </button>
                <button class="mobile-submenu-item mobile-lang-option" data-lang="uk" data-flag="ua" data-label="Українська">
                    <img src="https://flagcdn.com/w20/ua.png" alt="Українська"> Українська (Ukrainien)
                </button>
            </div>
        </li>
    </ul>
</div>
        
    </div>

    <div class="mobile-nav-footer">
        <a href="<?= base_url('Home/Contact') ?>" class="mobile-cta-btn">
            <i class="bi bi-headset"></i> Nous contacter
        </a>
        <?php if (!$logged_in): ?>
            <a href="<?= base_url('auth') ?>" class="mobile-cta-btn secondary">
                <i class="bi bi-box-arrow-in-right"></i> Connexion
            </a>
        <?php else: ?>
            <a href="<?= base_url('auth/logout') ?>" class="mobile-cta-btn secondary">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Bottom Navigation Mobile -->
<div class="bottom-nav">
    <ul class="bottom-nav-list">
        <li class="bottom-nav-item">
            <a href="<?= base_url() ?>" class="bottom-nav-link">
                <i class="bi bi-house-door"></i>
                <span>Accueil</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="<?= base_url('Products') ?>" class="bottom-nav-link">
                <i class="bi bi-box-seam"></i>
                <span>Produits</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="<?= base_url('Medicins') ?>" class="bottom-nav-link">
                <i class="bi bi-camera-video"></i>
                <span>Consultation</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="<?= base_url('Home/Contact') ?>" class="bottom-nav-link">
                <i class="bi bi-envelope"></i>
                <span>Contact</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="<?= $logged_in ? base_url('home-patient') : base_url('auth') ?>" class="bottom-nav-link">
                <i class="bi bi-person"></i>
                <span><?= $logged_in ? 'Compte' : 'Connexion' ?></span>
            </a>
        </li>
    </ul>
</div>

<script>
// ============================================
// GESTION DE LA BARRE DE RECHERCHE SUR MOBILE
// ============================================
const searchToggle = document.getElementById('searchToggle');
const searchContainer = document.getElementById('searchContainer');

if (searchToggle) {
    searchToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        if (searchContainer) {
            searchContainer.classList.toggle('active');
            if (searchContainer.classList.contains('active')) {
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            }
        }
    });
}

// Fermer la recherche si on clique en dehors
document.addEventListener('click', function(e) {
    if (searchContainer && !searchContainer.contains(e.target) && !searchToggle?.contains(e.target)) {
        searchContainer.classList.remove('active');
    }
});

// ============================================
// GESTION DU BOUTON DE LANGUE PERSONNALISÉ (DESKTOP)
// ============================================

// Éléments DOM
const langBtn = document.getElementById('customLanguageBtn');
const langDropdown = document.getElementById('customLanguageDropdown');
const currentLangFlag = document.getElementById('currentLangFlag');
const currentLangLabel = document.getElementById('currentLangLabel');

// Vérifier si une langue est sauvegardée et l'appliquer
const savedLang = localStorage.getItem('preferred_language');
const savedFlag = localStorage.getItem('preferred_flag');
const savedLabel = localStorage.getItem('preferred_label');

// Appliquer la langue sauvegardée au chargement
if (savedLang && savedFlag && savedLabel && savedLang !== 'fr') {
    document.cookie = `googtrans=/fr/${savedLang}; path=/; max-age=31536000`;
}

// Fonction pour ouvrir/fermer le dropdown
function toggleDropdown() {
    if (langDropdown) langDropdown.classList.toggle('active');
}

// Fermer le dropdown si on clique ailleurs
document.addEventListener('click', function(event) {
    if (langBtn && langDropdown && !langBtn.contains(event.target) && !langDropdown.contains(event.target)) {
        langDropdown.classList.remove('active');
    }
});

// Ouvrir/fermer au clic sur le bouton
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



// Ajouter les événements de clic sur chaque option de langue desktop
document.querySelectorAll('.lang-option-custom').forEach(option => {
    option.addEventListener('click', function(event) {
        event.stopPropagation();
        const langCode = this.getAttribute('data-lang');
        const flagCode = this.getAttribute('data-flag');
        const label = this.getAttribute('data-label');
        changeLanguage(langCode, flagCode, label);
    });
});

// Ajouter les événements de clic sur les options de langue du menu mobile
document.querySelectorAll('.mobile-lang-option').forEach(option => {
    option.addEventListener('click', function(event) {
        event.stopPropagation();
        const langCode = this.getAttribute('data-lang');
        const flagCode = this.getAttribute('data-flag');
        const label = this.getAttribute('data-label');
        changeLanguage(langCode, flagCode, label);
    });
});

// Mettre à jour l'affichage du bouton avec la langue sauvegardée
if (savedLang && savedFlag && savedLabel && currentLangFlag && currentLangLabel) {
    currentLangFlag.src = `https://flagcdn.com/w20/${savedFlag}.png`;
    currentLangLabel.textContent = savedLabel;
}

// ============================================
// SOUS-MENUS MOBILES
// ============================================
document.querySelectorAll('.has-submenu').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const submenuId = this.dataset.submenu;
        const submenu = document.getElementById(`submenu-${submenuId}`);
        
        document.querySelectorAll('.has-submenu').forEach(btn => {
            if (btn !== this) {
                btn.classList.remove('active');
                const otherId = btn.dataset.submenu;
                const otherSubmenu = document.getElementById(`submenu-${otherId}`);
                if (otherSubmenu) otherSubmenu.classList.remove('open');
            }
        });
        
        this.classList.toggle('active');
        if (submenu) submenu.classList.toggle('open');
    });
});

// ============================================
// MENU MOBILE
// ============================================
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileNavPanel = document.getElementById('mobileNavPanel');
const mobileOverlay = document.getElementById('mobileOverlay');
const mobileNavClose = document.getElementById('mobileNavClose');

function openMobileMenu() {
    if (mobileNavPanel) mobileNavPanel.classList.add('active');
    if (mobileOverlay) mobileOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    if (mobileNavPanel) mobileNavPanel.classList.remove('active');
    if (mobileOverlay) mobileOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileMenu);
if (mobileNavClose) mobileNavClose.addEventListener('click', closeMobileMenu);
if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileMenu);

// ============================================
// HEADER SCROLL EFFECT
// ============================================
let lastScroll = 0;
const mainHeader = document.getElementById('mainHeader');

window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        if (mainHeader) mainHeader.classList.add('scrolled');
        if (currentScroll > lastScroll && currentScroll > 200) {
            if (mainHeader) mainHeader.classList.add('nav-hidden');
        } else {
            if (mainHeader) mainHeader.classList.remove('nav-hidden');
        }
    } else {
        if (mainHeader) mainHeader.classList.remove('scrolled', 'nav-hidden');
    }
    lastScroll = currentScroll;
});

// ============================================
// RECHERCHE (DESKTOP)
// ============================================
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
let searchTimeout;

function performSearch(query) {
    if (!query || query.length < 2) {
        if (searchResults) searchResults.classList.remove('active');
        return;
    }
    
    if (searchResults) {
        searchResults.innerHTML = '<div style="padding: 20px; text-align: center;">Recherche...</div>';
        searchResults.classList.add('active');
    }
    
    fetch(`<?= base_url("search/ajax_search") ?>?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => displayResults(data, query))
        .catch(error => {
            console.error('Erreur recherche:', error);
            if (searchResults) searchResults.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--danger);">Erreur de recherche</div>';
        });
}

function displayResults(data, query) {
    let html = '';
    let hasResults = false;
    
    if (data.produits && data.produits.length > 0) {
        html += '<div class="result-category">Produits</div>';
        data.produits.slice(0, 4).forEach(item => {
            html += `<a href="<?= base_url('product/') ?>${item.slug}" class="result-item">
                        <i class="bi bi-box-seam"></i>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.titre)}</div>
                            <div class="result-desc">${escapeHtml(item.extrait ? item.extrait.substring(0, 50) : '')}</div>
                        </div>
                    </a>`;
            hasResults = true;
        });
    }
    
    if (data.actualites && data.actualites.length > 0) {
        html += '<div class="result-category">Actualités</div>';
        data.actualites.slice(0, 3).forEach(item => {
            html += `<a href="<?= base_url('actualite/') ?>${item.slug}" class="result-item">
                        <i class="bi bi-newspaper"></i>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.titre)}</div>
                            <div class="result-desc">${escapeHtml(item.extrait ? item.extrait.substring(0, 50) : '')}</div>
                        </div>
                    </a>`;
            hasResults = true;
        });
    }
    
    if (data.pages && data.pages.length > 0) {
        html += '<div class="result-category">Pages</div>';
        data.pages.slice(0, 3).forEach(item => {
            html += `<a href="<?= base_url('') ?>${item.slug}" class="result-item">
                        <i class="bi bi-file-text"></i>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.titre)}</div>
                            <div class="result-desc">${escapeHtml(item.extrait ? item.extrait.substring(0, 50) : '')}</div>
                        </div>
                    </a>`;
            hasResults = true;
        });
    }
    
    if (!hasResults) {
        html = `<div style="padding: 30px; text-align: center;">Aucun résultat trouvé pour "${escapeHtml(query)}"</div>`;
    }
    if (searchResults) searchResults.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        searchTimeout = setTimeout(() => performSearch(query), 300);
    });
}

document.addEventListener('click', function(e) {
    if (searchContainer && !searchContainer.contains(e.target) && !searchToggle?.contains(e.target)) {
        if (searchResults) searchResults.classList.remove('active');
    }
});

// Supprimer la barre Google Translate immédiatement
setInterval(function() {
    var banner = document.querySelector('.goog-te-banner-frame');
    if (banner) {
        banner.style.display = 'none';
        banner.style.visibility = 'hidden';
        banner.style.height = '0';
    }
    document.body.style.marginTop = '0';
    document.body.style.top = '0';
    document.body.style.position = 'relative';
}, 100);

window.addEventListener('load', function() {
    setTimeout(function() {
        document.body.style.marginTop = '0';
        document.body.style.top = '0';
        document.body.style.position = 'relative';
        var banner = document.querySelector('.goog-te-banner-frame');
        if (banner) {
            banner.remove();
        }
    }, 500);
});
</script>

<main style="padding:50px;" class="responsive-main">

    <!-- Votre contenu principal ici -->

</main>

<style>
@media(max-width:768px){
    .responsive-main{
        padding:17px !important;
    }
}
</style>