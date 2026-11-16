<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================
   VARIABLES GLOBALES
   ============================================ */
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --primary-soft: rgba(15, 76, 58, 0.1);
    --accent: #d4af37;
    --accent-hover: #b8962e;
    --accent-soft: rgba(212, 175, 55, 0.15);
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --gray-light: #dee2e6;
    --gray-soft: #f1f3f5;
    --shadow-sm: 0 4px 6px rgba(0,0,0,0.05);
    --shadow: 0 10px 20px rgba(0,0,0,0.1);
    --shadow-lg: 0 20px 40px rgba(0,0,0,0.15);
    --shadow-xl: 0 30px 60px rgba(0,0,0,0.2);
    --shadow-hover: 0 30px 50px rgba(15, 76, 58, 0.25);
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    --border-radius-sm: 12px;
    --border-radius-md: 20px;
    --border-radius-lg: 30px;
    --border-radius-xl: 40px;
    --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-secondary: 'Playfair Display', serif;
}

/* ============================================
   SCROLL PROGRESS & BACK TO TOP
   ============================================ */
.scroll-progress {
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    height: 4px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
    z-index: 9999;
    transition: width 0.1s;
}

.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    box-shadow: var(--shadow);
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 100;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: var(--accent);
    transform: translateY(-5px);
}

/* ============================================
   SECTION HERO PAGE - VERSION CORRIGÉE
   ============================================ */
.page-hero {
    position: relative;
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
}

/* Image de fond - PREMIER PLAN */
.hero-bg-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.8;
    z-index: 1;
}

/* Dégradé de superposition - DEUXIÈME PLAN */
.hero-gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(10, 51, 38, 0.7), rgba(15, 76, 58, 0.6));
    z-index: 2;
    pointer-events: none;
}

/* Contenu - TROISIÈME PLAN */
.page-hero-content {
    position: relative;
    z-index: 3;
    max-width: 800px;
    padding: 60px 20px;
}

.page-hero-title {
    font-family: var(--font-secondary);
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 700;
    color: white;
    margin-bottom: 20px;
    animation: fadeInDown 1s ease;
}

.page-hero-title span {
    color: var(--accent);
    position: relative;
    display: inline-block;
}

.page-hero-title span::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--accent);
    animation: expandWidth 1s ease 0.5s forwards;
    transform-origin: left;
    transform: scaleX(0);
}

@keyframes expandWidth {
    to { transform: scaleX(1); }
}

.page-hero-subtitle {
    font-size: clamp(1rem, 2vw, 1.2rem);
    color: rgba(255,255,255,0.9);
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.8;
    animation: fadeInUp 1s ease 0.3s forwards;
    opacity: 0;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ============================================
   SECTION GÉNÉRIQUE
   ============================================ */
.section {
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.section:nth-child(even) {
    background: linear-gradient(135deg, #ffffff, var(--gray-soft));
}

.section-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-container-fluid {
    width: 70%;
    max-width: 100%;
    margin: 0 auto;
    padding: 0;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
    opacity: 0;
    transform: translateY(30px);
    transition: var(--transition);
}

.section-header.visible {
    opacity: 1;
    transform: translateY(0);
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 20px;
    background: var(--accent-soft);
    color: var(--primary);
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 50px;
    margin-bottom: 20px;
    border: 1px solid var(--accent);
}

.section-tag::before {
    content: '';
    width: 8px;
    height: 8px;
    background: var(--accent);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.7; }
}

.section-title {
    font-family: var(--font-secondary);
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 15px;
    line-height: 1.2;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--gray);
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.8;
}

/* ============================================
   GRID CARDS (Style unifié)
   ============================================ */
.grid-card {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 30px 25px;
    height: 100%;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid transparent;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.grid-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
    border-color: var(--accent);
}

.grid-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.grid-card:hover::before {
    transform: translateX(0);
}

.grid-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary-soft), var(--accent-soft));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 28px;
    color: var(--primary);
    transition: var(--transition);
}

.grid-card:hover .grid-icon {
    transform: scale(1.1) rotate(5deg);
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
}

.grid-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 15px;
}

.grid-description {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
}

/* ============================================
   FACT SHEET STYLES
   ============================================ */
.fact-sheet {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    margin-bottom: 40px;
    transform: translateY(30px);
    opacity: 0;
    transition: var(--transition);
}

.fact-sheet.visible {
    transform: translateY(0);
    opacity: 1;
}

.fact-sheet-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 25px 30px;
    position: relative;
    overflow: hidden;
}

.fact-sheet-header::before {
    content: '';
    position: absolute;
    right: 20px;
    bottom: -20px;
    font-size: 120px;
    font-weight: 800;
    opacity: 0.1;
    color: white;
    pointer-events: none;
}

.fact-sheet-header h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
    position: relative;
    z-index: 1;
}

.fact-sheet-header p {
    font-size: 1rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.fact-sheet-body {
    padding: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.fact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: var(--gray-soft);
    border-radius: var(--border-radius-sm);
    transition: var(--transition);
}

.fact-item:hover {
    transform: translateX(10px);
    background: var(--accent-soft);
}

.fact-icon {
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
    transition: var(--transition);
}

.fact-item:hover .fact-icon {
    transform: rotate(360deg);
    background: var(--accent);
    color: var(--primary);
}

.fact-content {
    flex: 1;
}

.fact-label {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--gray);
    margin-bottom: 5px;
}

.fact-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark);
    line-height: 1.4;
}

.fact-value.highlight {
    color: var(--primary);
    font-size: 1.3rem;
    font-weight: 700;
}

/* ============================================
   IMAGE + TEXTE LAYOUT
   ============================================ */
.content-wrapper {
    padding: 20px;
}

.image-wrapper {
    position: relative;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.image-wrapper img {
    width: 100%;
    height: auto;
    transition: var(--transition);
}

.image-wrapper:hover img {
    transform: scale(1.05);
}

/* ============================================
   timeline STYLES
   ============================================ */
.timeline-container {
    position: relative;
    padding: 40px 0;
    margin: 40px 0;
}

.timeline-line {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 100%;
    top: 0;
    background: linear-gradient(to bottom, var(--primary), var(--accent), var(--primary));
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 50px;
    width: 100%;
}

.timeline-item.left {
    flex-direction: row;
}

.timeline-item.left .timeline-content {
    margin-right: 50%;
    padding-right: 50px;
}

.timeline-item.right {
    flex-direction: row-reverse;
}

.timeline-item.right .timeline-content {
    margin-left: 50%;
    padding-left: 50px;
}

.timeline-marker {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border: 4px solid var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    z-index: 3;
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.3);
    transition: var(--transition);
}

.timeline-item:hover .timeline-marker {
    transform: translateX(-50%) scale(1.15);
    background: linear-gradient(135deg, var(--accent), var(--accent-hover));
    border-color: var(--primary);
}

.timeline-year-badge {
    font-size: 0.9rem;
    font-weight: 700;
    color: white;
}

.timeline-marker i {
    font-size: 1.8rem;
    color: white;
}

.timeline-content {
    position: relative;
    width: 45%;
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid transparent;
    overflow: hidden;
}

.timeline-content:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: var(--shadow-hover);
    border-color: var(--accent);
}

.timeline-year {
    display: inline-block;
    padding: 5px 15px;
    background: var(--accent);
    color: var(--primary-dark);
    font-weight: 700;
    border-radius: 25px;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.timeline-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 15px;
    line-height: 1.3;
}

.timeline-description {
    color: var(--gray);
    font-size: 1rem;
    line-height: 1.6;
}

.timeline-icon-decoration {
    position: absolute;
    bottom: 15px;
    right: 20px;
    font-size: 3rem;
    color: var(--accent-soft);
    opacity: 0.3;
    transition: var(--transition);
}

.timeline-content:hover .timeline-icon-decoration {
    opacity: 0.6;
    transform: scale(1.1) rotate(5deg);
}

/* ============================================
   CTA SECTION
   ============================================ */
.cta-section {
    position: relative;
    padding: 80px 0;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, transparent 70%);
    animation: rotate 30s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 40px;
    background: var(--accent);
    color: var(--primary-dark);
    font-weight: 700;
    border-radius: 50px;
    text-decoration: none;
    transition: var(--transition);
    position: relative;
    z-index: 1;
}

.cta-button:hover {
    background: var(--accent-hover);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
}

/* ============================================
   BUTTONS
   ============================================ */
.btn-primary-custom {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 30px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    font-weight: 600;
    border-radius: 50px;
    text-decoration: none;
    transition: var(--transition);
    border: none;
}

.btn-primary-custom:hover {
    background: linear-gradient(135deg, var(--accent), var(--accent-hover));
    color: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}

/* ============================================
   TINYMCE CONTENT - STYLES COMPLÈTS
   ============================================ */
.tinymce-content {
    font-size: 1rem;
    line-height: 1.8;
    color: var(--dark);
}

.tinymce-content p {
    margin-bottom: 1rem;
}

.tinymce-content h1, 
.tinymce-content h2, 
.tinymce-content h3, 
.tinymce-content h4, 
.tinymce-content h5, 
.tinymce-content h6 {
    color: var(--primary);
    font-family: var(--font-secondary);
    margin-bottom: 1rem;
    font-weight: 700;
}

.tinymce-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
}

.tinymce-content h2 {
    font-size: 2rem;
    margin-bottom: 1.2rem;
    border-bottom: 2px solid var(--accent);
    padding-bottom: 10px;
    display: inline-block;
}

.tinymce-content h3 {
    font-size: 1.5rem;
    color: var(--primary-light);
}

.tinymce-content h4 {
    font-size: 1.25rem;
}

.tinymce-content h5 {
    font-size: 1.1rem;
}

.tinymce-content h6 {
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.tinymce-content ul, 
.tinymce-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.tinymce-content ul {
    list-style-type: disc;
}

.tinymce-content ol {
    list-style-type: decimal;
}

.tinymce-content li {
    margin-bottom: 0.5rem;
}

.tinymce-content li::marker {
    color: var(--accent);
}

.tinymce-content strong, 
.tinymce-content b {
    color: var(--primary);
    font-weight: 700;
}

.tinymce-content em, 
.tinymce-content i {
    font-style: italic;
    color: var(--gray);
}

.tinymce-content u {
    text-decoration: underline;
    text-decoration-color: var(--accent);
    text-decoration-thickness: 2px;
}

.tinymce-content s, 
.tinymce-content strike {
    text-decoration: line-through;
    color: var(--gray);
}

.tinymce-content a {
    color: var(--primary);
    text-decoration: none;
    border-bottom: 2px solid var(--accent);
    transition: var(--transition);
}

.tinymce-content a:hover {
    color: var(--accent);
    border-bottom-color: var(--primary);
}

.tinymce-content img {
    max-width: 100%;
    height: auto;
    border-radius: var(--border-radius-md);
    box-shadow: var(--shadow);
    margin: 1.5rem 0;
}

.tinymce-content img.align-left {
    float: left;
    margin-right: 20px;
    margin-bottom: 20px;
}

.tinymce-content img.align-right {
    float: right;
    margin-left: 20px;
    margin-bottom: 20px;
}

.tinymce-content img.align-center {
    display: block;
    margin-left: auto;
    margin-right: auto;
}

.tinymce-content blockquote {
    border-left: 4px solid var(--accent);
    padding: 20px 20px 20px 30px;
    margin: 2rem 0;
    font-style: italic;
    color: var(--gray);
    background: var(--gray-soft);
    border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
}

.tinymce-content blockquote p:last-child {
    margin-bottom: 0;
}

.tinymce-content pre {
    background: var(--dark);
    color: var(--light);
    padding: 20px;
    border-radius: var(--border-radius-sm);
    overflow-x: auto;
    font-family: monospace;
    margin-bottom: 1.5rem;
}

.tinymce-content code {
    background: var(--gray-soft);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    color: var(--primary);
}

.tinymce-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    box-shadow: var(--shadow);
    border-radius: var(--border-radius-sm);
    overflow: hidden;
}

.tinymce-content th {
    background: var(--primary);
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    border: none;
}

.tinymce-content td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--gray-light);
}

.tinymce-content tr:last-child td {
    border-bottom: none;
}

.tinymce-content tr:hover {
    background: var(--gray-soft);
}

.tinymce-content hr {
    border: none;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent), transparent);
    margin: 2rem 0;
}

.tinymce-content sup {
    vertical-align: super;
    font-size: smaller;
    color: var(--accent);
}

.tinymce-content sub {
    vertical-align: sub;
    font-size: smaller;
    color: var(--accent);
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 992px) {
    .section {
        padding: 60px 0;
    }
    
    .timeline-line {
        left: 30px;
    }
    
    .timeline-item.left,
    .timeline-item.right {
        flex-direction: row;
        justify-content: flex-start;
        margin-left: 60px;
    }
    
    .timeline-item.left .timeline-content,
    .timeline-item.right .timeline-content {
        margin-left: 80px;
        margin-right: 0;
        width: calc(100% - 80px);
        padding: 25px;
    }
    
    .timeline-marker {
        left: 30px;
    }
    
    .fact-sheet-body {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-hero {
        min-height: 50vh;
    }
    
    .page-hero-title {
        font-size: 2rem;
    }
    
    .tinymce-content h1 {
        font-size: 1.8rem;
    }
    
    .tinymce-content h2 {
        font-size: 1.5rem;
    }
    
    .tinymce-content h3 {
        font-size: 1.2rem;
    }
    
    .timeline-content {
        padding: 25px;
    }
    
    .timeline-title {
        font-size: 1.2rem;
    }
}
</style>

<!-- Scroll Progress -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- Bouton Retour en haut -->
<button class="back-to-top" id="backToTop">
    <i class="bi bi-arrow-up"></i>
</button>

<?php 
// Fonction pour nettoyer les chemins d'images
function fix_image_path($image_path) {
    if (empty($image_path)) return null;
    
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        return $image_path;
    }
    
    $image_path = preg_replace('/\.\.\//', '', $image_path);
    $image_path = ltrim($image_path, '/');
    
    return base_url($image_path);
}

// Fonction pour corriger les URLs d'images dans le contenu HTML
function fix_content_images($content) {
    if (empty($content)) return '';
    
    $base_url = rtrim(base_url(), '/');
    
    $content = preg_replace(
        '/src=["\'](?:\.\.\/)+(attachments\/[^"\']+)["\']/i',
        'src="/$1"',
        $content
    );
    
    $content = preg_replace(
        '/src=["\'](\/attachments\/[^"\']+)["\']/i',
        'src="' . $base_url . '$1"',
        $content
    );
    
    return $content;
}

// Boucle sur chaque section pour affichage dynamique
foreach ($sections as $section): 
    $type = $section['type_section'];
    $options = !empty($section['options_json']) ? json_decode($section['options_json'], true) : [];
    
    $raw_content = $section['contenu_texte'] ?? '';
    $content_with_fixed_images = fix_content_images($raw_content);
?>

    <?php switch($type):
        
        // ============================================
        // HERO SECTION
        // ============================================
        case 'hero': 
            $image_opacity = $options['image_opacity'] ?? '0.85';
        ?>
            <section class="page-hero <?= $section['custom_class'] ?? '' ?> d-flex align-items-center justify-content-center text-center position-relative overflow-hidden">
                
                <?php if (!empty($section['image_url'])): ?>
                    <div class="hero-bg-image" style="background-image: url('<?= fix_image_path($section['image_url']) ?>'); opacity: <?= $image_opacity ?>;"></div>
                <?php endif; ?>
                
                <div class="hero-gradient-overlay"></div>
                
                <div class="container position-relative" style="z-index: 4;">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-xl-6">
                            <h1 class="page-hero-title">
                                <?= $section['titre_section'] ?>
                                <?php if (!empty($section['sous_titre'])): ?>
                                    <span><?= $section['sous_titre'] ?></span>
                                <?php endif; ?>
                            </h1>
                            
                            <?php if (!empty($raw_content)): ?>
                                <p class="page-hero-subtitle"><?= strip_tags($raw_content) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['bouton_texte'])): ?>
                                <a href="<?= $section['bouton_lien'] ?? '#' ?>" class="cta-button mt-4">
                                    <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php break; ?>

// ============================================
// TEXTE SIMPLE - Version avec classes Bootstrap
// ============================================
<?php case 'texte': 
    // Options de mise en page
    $layout = $options['layout'] ?? 'simple'; // simple, with-image-left, with-image-right
    $has_image = !empty($section['image_url']);
    $image_url = $has_image ? fix_image_path($section['image_url']) : null;
    
    // Classes Bootstrap pour le conteneur
    $container_class = $options['container_class'] ?? 'container-fluid'; // container, container-fluid, container-lg, etc.
    $text_align = $options['text_align'] ?? 'text-start'; // text-start, text-center, text-end, text-justify
    
    // Colonnes pour le contenu
    $content_cols = $options['content_cols'] ?? 'col-lg-10 col-xl-8 mx-auto';
    
    // Padding
    $padding_y = $options['padding_y'] ?? 'py-5';
    $padding_x = $options['padding_x'] ?? 'px-3';
?>
    <section class="section <?= $section['custom_class'] ?? '' ?> <?= $padding_y ?>" 
             style="background: <?= $options['bg_color'] ?? 'transparent' ?>;">
        <div class="<?= $container_class ?> <?= $padding_x ?>">
            
            <?php if ($layout == 'with-image-left' && $has_image): ?>
                <!-- Layout avec image à gauche -->
                <div class="row align-items-center g-5">
                    <div class="col-lg-6" data-aos="fade-right">
                        <div class="image-wrapper">
                            <img src="<?= $image_url ?>" 
                                 alt="<?= htmlspecialchars($section['titre_section'] ?? 'Image') ?>" 
                                 class="img-fluid rounded-4 shadow">
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="content-wrapper">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                            <?php endif; ?>
                            
                            <div class="tinymce-content <?= $text_align ?>">
                                <?= $content_with_fixed_images ?>
                            </div>
                            
                            <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                <div class="mt-4">
                                    <a href="<?= htmlspecialchars($section['bouton_lien']) ?>" class="btn btn-primary btn-lg rounded-pill">
                                        <?= htmlspecialchars($section['bouton_texte']) ?> 
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($layout == 'with-image-right' && $has_image): ?>
                <!-- Layout avec image à droite -->
                <div class="row align-items-center g-5">
                    <div class="col-lg-6" data-aos="fade-right">
                        <div class="content-wrapper">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                            <?php endif; ?>
                            
                            <div class="tinymce-content <?= $text_align ?>">
                                <?= $content_with_fixed_images ?>
                            </div>
                            
                            <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                <div class="mt-4">
                                    <a href="<?= htmlspecialchars($section['bouton_lien']) ?>" class="btn btn-primary btn-lg rounded-pill">
                                        <?= htmlspecialchars($section['bouton_texte']) ?> 
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="image-wrapper">
                            <img src="<?= $image_url ?>" 
                                 alt="<?= htmlspecialchars($section['titre_section'] ?? 'Image') ?>" 
                                 class="img-fluid rounded-4 shadow">
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Layout simple (sans image) -->
                <div class="row justify-content-center">
                    <div class="<?= $content_cols ?>">
                        
                        <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                            <div class="text-center mb-5">
                                <?php if (!empty($section['titre_section'])): ?>
                                    <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($section['sous_titre'])): ?>
                                    <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="tinymce-content <?= $text_align ?>">
                            <?= $content_with_fixed_images ?>
                        </div>
                        
                        <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                            <div class="text-center mt-5">
                                <a href="<?= htmlspecialchars($section['bouton_lien']) ?>" class="btn btn-primary btn-lg rounded-pill">
                                    <?= htmlspecialchars($section['bouton_texte']) ?> 
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </section>
<?php break; ?>
// ============================================
// SECTION HTML (contenu riche) - Affichage du contenu TinyMCE
// ============================================
<?php case 'html': 
    // Récupération de l'image si présente (optionnelle)
    $image_url = !empty($section['image_url']) ? fix_image_path($section['image_url']) : null;
?>
    <section class="section <?= $section['custom_class'] ?? '' ?>" 
             style="background: <?= $options['bg_color'] ?? 'transparent' ?>; padding: <?= $options['padding'] ?? '40px' ?> 0;">
        <div class="section-container">
            
            <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                <div class="section-header">
                    <?php if (!empty($section['titre_section'])): ?>
                        <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($section['sous_titre'])): ?>
                        <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($image_url): ?>
                <div class="text-center">
                    <img src="<?= $image_url ?>" 
                         alt="<?= htmlspecialchars($section['titre_section'] ?? 'Image') ?>" 
                         class="img-fluid <?= $options['image_class'] ?? 'rounded shadow-lg' ?>" 
                         style="max-width: <?= $options['max_width'] ?? '100%' ?>;">
                </div>
            <?php endif; ?>
            
            <?php if (!empty($content_with_fixed_images)): ?>
                <div class="tinymce-content mt-4">
                    <?php 
                        // Décodage des entités HTML et affichage du contenu riche (balises, styles, etc.)
                        echo html_entity_decode($content_with_fixed_images, ENT_QUOTES, 'UTF-8'); 
                    ?>
                </div>
            <?php endif; ?>
            
        </div>
    </section>
<?php break; ?>
        // ============================================
        // IMAGE + TEXTE
        // ============================================
        <?php case 'image_texte': 
            $image_left = empty($section['image_droite']) || $section['image_droite'] == 0;
            $has_image = !empty($section['image_url']);
            $image_url = $has_image ? fix_image_path($section['image_url']) : null;
        ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <div class="row align-items-center g-5">
                        <?php if ($has_image && $image_left): ?>
                            <!-- Image à gauche -->
                            <div class="col-lg-6" data-aos="fade-right">
                                <div class="image-wrapper">
                                    <img src="<?= $image_url ?>" alt="<?= $section['titre_section'] ?? 'Image' ?>" class="img-fluid">
                                </div>
                            </div>
                            <div class="col-lg-6" data-aos="fade-left">
                                <div class="content-wrapper">
                                    <?php if (!empty($section['titre_section'])): ?>
                                        <span class="section-tag"><?= $section['titre_section'] ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($section['sous_titre'])): ?>
                                        <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                                    <?php endif; ?>
                                    <div class="tinymce-content">
                                        <?= $content_with_fixed_images ?>
                                    </div>
                                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                        <a href="<?= $section['bouton_lien'] ?>" class="btn-primary-custom mt-4">
                                            <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($has_image && !$image_left): ?>
                            <!-- Image à droite -->
                            <div class="col-lg-6" data-aos="fade-right">
                                <div class="content-wrapper">
                                    <?php if (!empty($section['titre_section'])): ?>
                                        <span class="section-tag"><?= $section['titre_section'] ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($section['sous_titre'])): ?>
                                        <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                                    <?php endif; ?>
                                    <div class="tinymce-content">
                                        <?= $content_with_fixed_images ?>
                                    </div>
                                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                        <a href="<?= $section['bouton_lien'] ?>" class="btn-primary-custom mt-4">
                                            <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-6" data-aos="fade-left">
                                <div class="image-wrapper">
                                    <img src="<?= $image_url ?>" alt="<?= $section['titre_section'] ?? 'Image' ?>" class="img-fluid">
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Sans image -->
                            <div class="col-12 text-center">
                                <?php if (!empty($section['titre_section'])): ?>
                                    <span class="section-tag"><?= $section['titre_section'] ?></span>
                                <?php endif; ?>
                                <?php if (!empty($section['sous_titre'])): ?>
                                    <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                                <?php endif; ?>
                                <div class="tinymce-content mx-auto" style="max-width: 800px;">
                                    <?= $content_with_fixed_images ?>
                                </div>
                                <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                    <a href="<?= $section['bouton_lien'] ?>" class="btn-primary-custom mt-4">
                                        <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // GRILLE STANDARD
        // ============================================
        <?php case 'grille': 
            $cols = $options['columns'] ?? 3;
            $col_class = match($cols) {
                2 => 'col-md-6',
                3 => 'col-md-6 col-lg-4',
                4 => 'col-md-6 col-lg-3',
                default => 'col-md-6 col-lg-4'
            };
            
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            $items = [];
            
            if (strpos($decoded_content, '<li>') !== false) {
                preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
                $items = $matches[1] ?? [];
            } else {
                $lines = explode("\n", strip_tags($decoded_content));
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $items[] = $line;
                    }
                }
            }
        ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row g-4">
                        <?php foreach ($items as $item): 
                            if (empty(trim($item))) continue;
                            
                            $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                            $item = trim(strip_tags($item));
                            
                            $title = $item;
                            $description = '';
                            if (strpos($item, ':') !== false) {
                                $parts = explode(':', $item, 2);
                                $title = trim($parts[0]);
                                $description = trim($parts[1]);
                            }
                        ?>
                        <div class="<?= $col_class ?>">
                            <div class="grid-card h-100">
                                <?php if ($options['show_icons'] ?? true): ?>
                                    <div class="grid-icon">
                                        <i class="bi bi-<?= $options['icon'] ?? 'grid' ?>"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <h4 class="grid-title"><?= htmlspecialchars($title) ?></h4>
                                
                                <?php if (!empty($description)): ?>
                                    <div class="grid-description">
                                        <?= htmlspecialchars($description) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                        <div class="text-center mt-5">
                            <a href="<?= $section['bouton_lien'] ?>" class="btn-primary-custom">
                                <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // GRILLE CARD - Version avec cartes plus stylisées
        // ============================================
        <?php case 'grille_card': 
            $cols = $options['columns'] ?? 3;
            $col_class = match($cols) {
                2 => 'col-md-6',
                3 => 'col-md-6 col-lg-4',
                4 => 'col-md-6 col-lg-3',
                default => 'col-md-6 col-lg-4'
            };
            
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            $items = [];
            
            if (strpos($decoded_content, '<li>') !== false) {
                preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
                $items = $matches[1] ?? [];
            } else {
                $lines = explode("\n", strip_tags($decoded_content));
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $items[] = $line;
                    }
                }
            }
        ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row g-4">
                        <?php foreach ($items as $item): 
                            if (empty(trim($item))) continue;
                            
                            $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                            $item = trim(strip_tags($item));
                            
                            $title = $item;
                            $description = '';
                            if (strpos($item, ':') !== false) {
                                $parts = explode(':', $item, 2);
                                $title = trim($parts[0]);
                                $description = trim($parts[1]);
                            }
                            
                            $icon = $options['icons'][$index % count($options['icons'])] ?? 'star';
                        ?>
                        <div class="<?= $col_class ?>">
                            <div class="grid-card h-100">
                                <?php if ($options['show_icons'] ?? true): ?>
                                    <div class="grid-icon">
                                        <i class="bi bi-<?= $icon ?>"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <h4 class="grid-title"><?= htmlspecialchars($title) ?></h4>
                                
                                <?php if (!empty($description)): ?>
                                    <div class="grid-description">
                                        <?= htmlspecialchars($description) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                        <div class="text-center mt-5">
                            <a href="<?= $section['bouton_lien'] ?>" class="btn-primary-custom">
                                <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // GRILLE INLINE - Éléments en ligne
        // ============================================
        <?php case 'grille_inline': 
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            $items = [];
            
            if (strpos($decoded_content, '<li>') !== false) {
                preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
                $items = $matches[1] ?? [];
            } else {
                $lines = explode("\n", strip_tags($decoded_content));
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $items[] = $line;
                    }
                }
            }
        ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-4">
                        <?php foreach ($items as $item): 
                            if (empty(trim($item))) continue;
                            
                            $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                            $item = trim(strip_tags($item));
                        ?>
                        <div class="text-center p-3" style="min-width: 150px;">
                            <?php if ($options['show_icons'] ?? true): ?>
                                <div class="mb-3">
                                    <i class="bi bi-<?= $options['icon'] ?? 'tag' ?>" style="font-size: 2rem; color: var(--primary);"></i>
                                </div>
                            <?php endif; ?>
                            <span class="fw-bold text-primary"><?= htmlspecialchars($item) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // LISTE STANDARD
        // ============================================
        <?php case 'liste': 
            $list_items = [];
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            $clean_content = strip_tags($decoded_content);
            $lines = explode("\n", $clean_content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $list_items[] = $line;
                }
            }
            
            if (empty($list_items) && strpos($clean_content, ';') !== false) {
                $items = explode(';', $clean_content);
                foreach ($items as $item) {
                    $item = trim($item);
                    if (!empty($item)) {
                        $list_items[] = $item;
                    }
                }
            }
        ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($list_items)): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($list_items as $item): 
                                $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                                $label = '';
                                $value = $item;
                                
                                if (strpos($item, ':') !== false) {
                                    $parts = explode(':', $item, 2);
                                    $label = trim($parts[0]);
                                    $value = trim($parts[1]);
                                }
                            ?>
                            <li class="mb-3 d-flex">
                                <i class="bi bi-<?= $options['icon'] ?? 'check-circle-fill' ?> text-<?= $options['icon_color'] ?? 'primary' ?> me-3 flex-shrink-0" style="font-size: 1.2rem;"></i>
                                <div>
                                    <?php if (!empty($label)): ?>
                                        <strong class="text-primary"><?= htmlspecialchars($label) ?>:</strong>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($value) ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                        <div class="text-center mt-4">
                            <a href="<?= $section['bouton_lien'] ?>" class="btn-primary-custom">
                                <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // LISTE CARD - Cartes en liste
        // ============================================
        <?php case 'liste_card': 
            $list_items = [];
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            $clean_content = strip_tags($decoded_content);
            $lines = explode("\n", $clean_content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $list_items[] = $line;
                }
            }
            
            $cols = $options['columns'] ?? 2;
            $col_class = ($cols == 1) ? 'col-12' : 'col-md-4';
        ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($list_items)): ?>
                        <div class="row g-4">
                            <?php foreach ($list_items as $item): 
                                $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                                $label = '';
                                $value = $item;
                                
                                if (strpos($item, ':') !== false) {
                                    $parts = explode(':', $item, 2);
                                    $label = trim($parts[0]);
                                    $value = trim($parts[1]);
                                }
                            ?>
                            <div class="<?= $col_class ?>">
                                <div class="fact-item h-100">
                                    <div class="fact-icon">
                                        <i class="bi bi-<?= $options['icon'] ?? 'check-circle' ?>"></i>
                                    </div>
                                    <div class="fact-content">
                                        <?php if (!empty($label)): ?>
                                            <div class="fact-label"><?= htmlspecialchars($label) ?></div>
                                        <?php endif; ?>
                                        <div class="fact-value"><?= htmlspecialchars($value) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // LISTE INLINE - Liste horizontale
        // ============================================
        <?php case 'liste_inline': 
            $list_items = [];
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            $clean_content = strip_tags($decoded_content);
            $lines = explode("\n", $clean_content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $list_items[] = $line;
                }
            }
        ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($list_items)): ?>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <?php foreach ($list_items as $item): 
                                $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="badge bg-light text-dark p-3 border">
                                <?= htmlspecialchars($item) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // LISTE ITEM - Élément unique
        // ============================================
        <?php case 'liste_item': ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="fact-item">
                                <div class="fact-icon">
                                    <i class="bi bi-<?= $options['icon'] ?? 'info-circle' ?>"></i>
                                </div>
                                <div class="fact-content">
                                    <div class="tinymce-content">
                                        <?= $content_with_fixed_images ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php break; ?>

        // ============================================
        // TABLEAU
        // ============================================
        <?php case 'tableau': ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <?= $content_with_fixed_images ?>
                    </div>
                </div>
            </section>
        <?php break; ?>

// ============================================
// TIMELINE - Version avec interprétation HTML complète
// ============================================
<?php case 'timeline': 
    $timeline_items = [];
    $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
    
    // Découper par catégories (séparées par des lignes vides)
    $categories = preg_split('/\n\s*\n/', $decoded_content);
    
    foreach ($categories as $cat_index => $category) {
        if (empty(trim($category))) continue;
        
        $lines = explode("\n", trim($category));
        $cat_title = '';
        $cat_items = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Détecter le titre de catégorie (ex: "1) Research & Product Development")
            if (preg_match('/^\d+[\)\.]\s*(.+)$/', $line, $matches)) {
                $cat_title = $matches[1];
            } else {
                // C'est un élément de la catégorie
                $cat_items[] = $line;
            }
        }
        
        if (!empty($cat_title) && !empty($cat_items)) {
            $timeline_items[] = [
                'title' => $cat_title,
                'items' => $cat_items,
                'marker' => $cat_index + 1
            ];
        }
    }
    
    // Fallback si le format ci-dessus ne fonctionne pas
    if (empty($timeline_items)) {
        if (strpos($decoded_content, '<li>') !== false) {
            preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
            $simple_items = $matches[1] ?? [];
            foreach ($simple_items as $index => $item) {
                // Nettoyer mais garder le HTML
                $item = trim($item);
                $timeline_items[] = [
                    'title' => 'Étape ' . ($index + 1),
                    'items' => [$item],
                    'marker' => $index + 1
                ];
            }
        } else {
            $lines = explode("\n", $decoded_content); // Ne pas enlever les balises
            $current_cat = '';
            $current_items = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                if (preg_match('/^\d+[\)\.]\s*(.+)$/', strip_tags($line), $matches)) {
                    if (!empty($current_cat) && !empty($current_items)) {
                        $timeline_items[] = [
                            'title' => $current_cat,
                            'items' => $current_items,
                            'marker' => count($timeline_items) + 1
                        ];
                    }
                    $current_cat = $matches[1];
                    $current_items = [];
                } else {
                    $current_items[] = $line;
                }
            }
            
            if (!empty($current_cat) && !empty($current_items)) {
                $timeline_items[] = [
                    'title' => $current_cat,
                    'items' => $current_items,
                    'marker' => count($timeline_items) + 1
                ];
            }
        }
    }
?>
    <section class="section timeline-section <?= $section['custom_class'] ?? '' ?>" 
             style="background: <?= $options['bg_color'] ?? 'var(--light)' ?>;">
        <div class="section-container">
            
            <!-- En-tête de section -->
            <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                <div class="section-header">
                    <?php if (!empty($section['titre_section'])): ?>
                        <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($section['sous_titre'])): ?>
                        <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Timeline Container style Objectives -->
            <div class="objectives-timeline">
                
                <?php if (!empty($timeline_items)): ?>
                    <?php foreach ($timeline_items as $index => $item): 
                        $side_class = ($index % 2 == 0) ? '' : 'even';
                    ?>
                        <div class="objective-item <?= $side_class ?>">
                            
                            <!-- Contenu de la timeline -->
                            <div class="objective-content">
                                
                                <?php if (!empty($item['title'])): ?>
                                    <h3 class="objective-title"><?= htmlspecialchars($item['title']) ?></h3>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['items'])): ?>
                                    <?php foreach ($item['items'] as $sub_item): 
                                        $sub_item = html_entity_decode($sub_item, ENT_QUOTES, 'UTF-8');
                                        
                                        // Extraire l'année si présente (mais garder le HTML)
                                        $year = '';
                                        $description = $sub_item;
                                        
                                        if (preg_match('/^(\d{4}(?:–\d{4})?)[:\s]\s*(.+)/', strip_tags($sub_item), $matches)) {
                                            $year = $matches[1];
                                            // Remplacer l'année dans la description mais garder le HTML
                                            $description = preg_replace('/^' . preg_quote($matches[0], '/') . '/', '', $sub_item);
                                            if (empty($description)) {
                                                $description = $matches[2];
                                            }
                                        }
                                    ?>
                                        <?php if (!empty($year)): ?>
                                            <span class="objective-year"><?= htmlspecialchars($year) ?></span>
                                        <?php endif; ?>
                                        
                                        <div class="objective-desc tinymce-content">
                                            <?= fix_content_images($description) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                            </div>
                            
                            <!-- Marqueur -->
                            <div class="objective-marker">
                                <?= $item['marker'] ?>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            </div>
            
            <!-- Bouton d'action -->
            <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                <div class="text-center mt-5">
                    <a href="<?= htmlspecialchars($section['bouton_lien']) ?>" class="btn-primary-custom">
                        <?= htmlspecialchars($section['bouton_texte']) ?> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
            
        </div>
    </section>
<?php break; ?>
        // ============================================
        // DEFAULT
        // ============================================
        <?php default: ?>
            <section class="section <?= $section['custom_class'] ?? '' ?>">
                <div class="section-container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="section-header">
                            <?php if (!empty($section['titre_section'])): ?>
                                <span class="section-tag"><?= $section['titre_section'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2 class="section-title"><?= $section['sous_titre'] ?></h2>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="tinymce-content">
                        <?= $content_with_fixed_images ?>
                    </div>
                    
                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                        <div class="text-center mt-4">
                            <a href="<?= $section['bouton_lien'] ?>" class="btn-primary-custom">
                                <?= $section['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php break; ?>

    <?php endswitch; ?>

<?php endforeach; ?>

<script>
// Scroll Progress Indicator
window.addEventListener('scroll', () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.getElementById('scrollProgress').style.width = scrolled + '%';
    
    // Back to top button visibility
    const backToTop = document.getElementById('backToTop');
    if (winScroll > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

// Back to top functionality
document.getElementById('backToTop').addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Intersection Observer for animations
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '50px' });
    
    document.querySelectorAll('.section-header, .fact-sheet, .grid-card, .timeline-content, .timeline-item').forEach(el => {
        observer.observe(el);
    });
    
    // Counter animation
    const counters = document.querySelectorAll('.stat-number[data-target]');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        if (!isNaN(target)) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.innerText = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    counter.innerText = Math.floor(current).toLocaleString();
                }
            }, 30);
        }
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>



























<style>




/* ============================================
   SECTION GÉNÉRIQUE
   ============================================ */
.section {
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.section:nth-child(even) {
    background: linear-gradient(135deg, #ffffff, var(--gray-soft));
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
    opacity: 0;
    transform: translateY(30px);
    transition: var(--transition);
}

.section-header.visible {
    opacity: 1;
    transform: translateY(0);
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 20px;
    background: var(--accent-soft);
    color: var(--primary);
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 50px;
    margin-bottom: 20px;
    border: 1px solid var(--accent);
}

.section-tag::before {
    content: '';
    width: 8px;
    height: 8px;
    background: var(--accent);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.7; }
}

.section-title {
    font-family: var(--font-secondary);
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 15px;
    line-height: 1.2;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--gray);
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.8;
}

/* ============================================
   PROJECT FACT SHEET
   ============================================ */
.fact-sheet {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    margin-bottom: 40px;
    transform: translateY(30px);
    opacity: 0;
    transition: var(--transition);
}

.fact-sheet.visible {
    transform: translateY(0);
    opacity: 1;
}

.fact-sheet-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 25px 30px;
    position: relative;
    overflow: hidden;
}

.fact-sheet-header::before {
    content: 'AGF';
    position: absolute;
    right: 20px;
    bottom: -20px;
    font-size: 120px;
    font-weight: 800;
    opacity: 0.1;
    color: white;
    pointer-events: none;
}

.fact-sheet-header h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
    position: relative;
    z-index: 1;
}

.fact-sheet-header p {
    font-size: 1rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.fact-sheet-body {
    padding: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.fact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: var(--gray-soft);
    border-radius: var(--border-radius-sm);
    transition: var(--transition);
}

.fact-item:hover {
    transform: translateX(10px);
    background: var(--accent-soft);
}

.fact-icon {
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
    transition: var(--transition);
}

.fact-item:hover .fact-icon {
    transform: rotate(360deg);
    background: var(--accent);
    color: var(--primary);
}

.fact-content {
    flex: 1;
}

.fact-label {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--gray);
    margin-bottom: 5px;
}

.fact-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark);
    line-height: 1.4;
}

.fact-value.highlight {
    color: var(--primary);
    font-size: 1.3rem;
    font-weight: 700;
}

/* ============================================
   CORPORATE PROFILE
   ============================================ */
.corporate-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.corporate-card {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 30px;
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.corporate-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.corporate-card:hover::before {
    transform: translateX(0);
}

.corporate-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.corporate-icon {
    width: 80px;
    height: 80px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2rem;
    color: var(--primary);
    transition: var(--transition);
}

.corporate-card:hover .corporate-icon {
    transform: scale(1.1) rotate(360deg);
    background: var(--accent);
    color: white;
}

.corporate-label {
    font-size: 0.9rem;
    color: var(--gray);
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.corporate-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 5px;
}

.corporate-value.small {
    font-size: 1rem;
    color: var(--dark);
}

/* ============================================
   COMPANY OVERVIEW
   ============================================ */
.overview-content {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--border-radius-lg);
    padding: 50px;
    color: white;
    position: relative;
    overflow: hidden;
    margin: 40px 0;
}

.overview-content::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, transparent 70%);
    animation: rotate 30s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.overview-text {
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-top: 40px;
    position: relative;
    z-index: 1;
}

.overview-stat {
    text-align: center;
}

.overview-stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--accent);
    margin-bottom: 10px;
}

.overview-stat-label {
    font-size: 1rem;
    opacity: 0.9;
}

/* ============================================
   CORE ACTIVITIES
   ============================================ */
.activities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 40px;
}

.activity-item {
    background: white;
    border-radius: var(--border-radius-sm);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-left: 4px solid transparent;
}

.activity-item:hover {
    transform: translateX(10px);
    border-left-color: var(--accent);
    box-shadow: var(--shadow-lg);
}

.activity-number {
    width: 40px;
    height: 40px;
    background: var(--primary-soft);
    color: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    flex-shrink: 0;
    transition: var(--transition);
}

.activity-item:hover .activity-number {
    background: var(--accent);
    color: var(--primary-dark);
    transform: scale(1.1);
}

.activity-text {
    font-size: 1rem;
    font-weight: 500;
    color: var(--dark);
    line-height: 1.4;
}

/* ============================================
   VISION MISSION
   ============================================ */
.vision-mission-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin-top: 40px;
}

.vision-card,
.mission-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 40px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.vision-card::before,
.mission-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
}

.vision-card:hover,
.mission-card:hover {
    transform: translateY(-15px);
    box-shadow: var(--shadow-hover);
}

.card-icon {
    width: 80px;
    height: 80px;
    background: var(--accent-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 25px;
    font-size: 2.5rem;
    color: var(--accent);
    transition: var(--transition);
}

.vision-card:hover .card-icon,
.mission-card:hover .card-icon {
    transform: scale(1.1) rotate(360deg);
    background: var(--accent);
    color: white;
}

.card-title {
    font-family: var(--font-secondary);
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 20px;
}

.card-text {
    font-size: 1rem;
    line-height: 1.8;
    color: var(--gray);
}

/* ============================================
   OBJECTIVES SECTION
   ============================================ */
.objectives-timeline {
    position: relative;
    padding: 40px 0;
}

.objectives-timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, var(--primary), var(--accent), var(--primary));
    border-radius: 2px;
}

.objective-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 50px;
    position: relative;
}

.objective-item:nth-child(even) {
    flex-direction: row-reverse;
}

.objective-content {
    width: 45%;
    background: white;
    border-radius: var(--border-radius-md);
    padding: 30px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
}

.objective-content:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-hover);
}

.objective-year {
    display: inline-block;
    padding: 5px 15px;
    background: var(--accent);
    color: var(--primary-dark);
    font-weight: 700;
    border-radius: 20px;
    margin-bottom: 15px;
}

.objective-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 15px;
}

.objective-desc {
    font-size: 0.95rem;
    color: var(--gray);
    line-height: 1.6;
    margin-bottom: 15px;
}

.objective-details {
    list-style: none;
    padding: 0;
}

.objective-details li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.objective-details li i {
    color: var(--accent);
    font-size: 1rem;
}

.objective-marker {
    width: 60px;
    height: 60px;
    background: var(--primary);
    border: 4px solid var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    position: relative;
    z-index: 2;
    box-shadow: var(--shadow);
}

/* ============================================
   CAPITAL ALLOCATION
   ============================================ */
.capital-section {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 60px 0;
}

.capital-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.capital-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius-md);
    padding: 30px;
    border: 1px solid rgba(255,255,255,0.2);
    transition: var(--transition);
}

.capital-card:hover {
    transform: translateY(-10px);
    background: rgba(255,255,255,0.15);
    border-color: var(--accent);
}

.capital-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: var(--accent);
}

.capital-list {
    list-style: none;
    padding: 0;
}

.capital-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.capital-list li i {
    color: var(--accent);
    font-size: 1rem;
}

.capital-percent {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.2);
    text-align: right;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--accent);
}

/* ============================================
   MILESTONES timeline
   ============================================ */
.milestones-timeline {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.milestone-card {
    background: white;
    border-radius: var(--border-radius-md);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
}

.milestone-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: var(--shadow-hover);
}

.milestone-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.milestone-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, transparent 70%);
}

.milestone-year {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--accent);
    position: relative;
    z-index: 1;
}

.milestone-category {
    font-size: 0.9rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.milestone-body {
    padding: 25px;
}

.milestone-list {
    list-style: none;
    padding: 0;
}

.milestone-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 0.95rem;
    color: var(--gray);
}

.milestone-list li i {
    color: var(--accent);
    font-size: 1rem;
}

.milestone-list li strong {
    color: var(--primary);
}

/* ============================================
   EQUIPMENT SHOWCASE
   ============================================ */
.equipment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 40px;
}

.equipment-card {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 25px;
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid transparent;
}

.equipment-card:hover {
    transform: translateY(-10px);
    border-color: var(--accent);
    box-shadow: var(--shadow-hover);
}

.equipment-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-soft), var(--accent-soft));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2rem;
    color: var(--primary);
    transition: var(--transition);
}

.equipment-card:hover .equipment-icon {
    transform: rotateY(360deg);
    background: var(--accent);
    color: white;
}

.equipment-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 10px;
    line-height: 1.4;
}

.equipment-desc {
    font-size: 0.9rem;
    color: var(--gray);
    line-height: 1.6;
}

/* ============================================
   STATS COUNTERS
   ============================================ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin: 50px 0;
}

.stat-box {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: var(--border-radius-md);
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.stat-box:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.stat-box:hover .stat-number {
    color: var(--accent);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 10px;
    transition: var(--transition);
}

.stat-label {
    font-size: 1rem;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */
@media (max-width: 992px) {
    .section {
        padding: 60px 0;
    }
    
    .objectives-timeline::before {
        left: 30px;
    }
    
    .objective-item,
    .objective-item:nth-child(even) {
        flex-direction: column;
        align-items: flex-start;
        margin-left: 60px;
    }
    
    .objective-content {
        width: 100%;
        margin-bottom: 20px;
    }
    
    .objective-marker {
        position: absolute;
        left: -60px;
        top: 0;
    }
    
    .vision-mission-grid {
        grid-template-columns: 1fr;
    }
    
    .overview-content {
        padding: 30px;
    }
}

@media (max-width: 768px) {
    .fact-sheet-body {
        grid-template-columns: 1fr;
    }
    
    .corporate-grid {
        grid-template-columns: 1fr;
    }
    
    .activities-grid {
        grid-template-columns: 1fr;
    }
    
    .capital-grid {
        grid-template-columns: 1fr;
    }
    
    .milestones-timeline {
        grid-template-columns: 1fr;
    }
    
    .equipment-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .overview-stats {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .page-hero-title {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .fact-sheet-header h2 {
        font-size: 1.5rem;
    }
    
    .corporate-card {
        padding: 20px;
    }
    
    .activity-item {
        flex-direction: column;
        text-align: center;
    }
}

/* ============================================
   ANIMATIONS
   ============================================ */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.floating {
    animation: float 4s ease-in-out infinite;
}

/* Scroll Progress */
.scroll-progress {
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    height: 4px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
    z-index: 9999;
    transition: width 0.1s;
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    box-shadow: var(--shadow);
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 100;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: var(--accent);
    transform: translateY(-5px);
}

/* Print Styles */
@media print {
    .back-to-top,
    .scroll-progress {
        display: none;
    }
}
</style>






