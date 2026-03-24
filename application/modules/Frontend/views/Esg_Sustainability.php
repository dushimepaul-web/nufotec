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
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
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

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-primary);
    color: var(--dark);
    overflow-x: hidden;
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
   PAGE HERO
   ============================================ */
.page-hero {
    position: relative;
    min-height: 60vh;
    background: linear-gradient(135deg, #0a4c3a, #1e6b52);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
}

.page-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><path d="M20 20 L80 20 L80 80 L20 80 Z" fill="none" stroke="%23d4af37" stroke-width="2"/><circle cx="50" cy="50" r="20" fill="none" stroke="%23d4af37" stroke-width="2"/><path d="M30 30 L70 70 M70 30 L30 70" stroke="%23d4af37" stroke-width="2"/></svg>');
    background-size: 100px 100px;
    animation: moveBackground 25s linear infinite;
}

@keyframes moveBackground {
    from { transform: translateY(0) rotate(0); }
    to { transform: translateY(-80px) rotate(5deg); }
}

.page-hero-content {
    position: relative;
    z-index: 2;
    max-width: 900px;
    padding: 80px 20px;
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
    font-size: clamp(1.1rem, 2vw, 1.4rem);
    color: rgba(255,255,255,0.95);
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.8;
    animation: fadeInUp 1s ease 0.3s forwards;
    opacity: 0;
    animation-fill-mode: forwards;
}

/* ============================================
   SECTION STYLES
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
    font-size: 13px;
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
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 15px;
    line-height: 1.2;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--gray);
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
}

/* ============================================
   COMMITMENT CARDS
   ============================================ */
.commitment-card {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 25px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-left: 4px solid var(--accent);
}

.commitment-card:hover {
    transform: translateX(10px);
    box-shadow: var(--shadow-lg);
}

.commitment-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

/* ============================================
   ISO SHOWCASE
   ============================================ */
.iso-showcase {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--border-radius-xl);
    padding: 60px 40px;
    position: relative;
    overflow: hidden;
}

.iso-showcase::before {
    content: 'ISO';
    position: absolute;
    right: -50px;
    bottom: -50px;
    font-size: 250px;
    font-weight: 900;
    opacity: 0.1;
    color: white;
    transform: rotate(-15deg);
}

.iso-item {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius-lg);
    border: 1px solid rgba(255,255,255,0.2);
    transition: var(--transition);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 30px 20px;
}

.iso-item:hover {
    transform: translateY(-10px);
    background: rgba(255,255,255,0.15);
    border-color: var(--accent);
}

.iso-number {
    color: var(--accent);
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 10px;
}

.iso-name {
    color: rgba(255,255,255,0.9);
    font-size: 1rem;
    line-height: 1.5;
}

/* ============================================
   LICENSING GRID
   ============================================ */
.licensing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin: 40px 0;
}

.license-card {
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

.license-card:hover {
    transform: translateX(10px);
    border-left-color: var(--accent);
    box-shadow: var(--shadow-lg);
}

.license-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary);
    flex-shrink: 0;
    transition: var(--transition);
}

.license-card:hover .license-icon {
    background: var(--accent);
    color: white;
    transform: rotate(360deg);
}

.license-content {
    flex: 1;
}

.license-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 5px;
}

.license-authority {
    font-size: 0.85rem;
    color: var(--gray);
    font-weight: 500;
}

/* ============================================
   INTEGRITY SECTION
   ============================================ */
.integrity-section {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: var(--border-radius-xl);
    padding: 50px;
    margin: 40px 0;
    box-shadow: var(--shadow-lg);
}

.integrity-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 30px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-bottom: 4px solid transparent;
}

.integrity-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-lg);
    border-bottom-color: var(--accent);
}

.integrity-icon {
    transition: var(--transition);
}

.integrity-card:hover .integrity-icon i {
    transform: scale(1.1) rotate(5deg);
    color: var(--primary) !important;
}

/* ============================================
   POSITIONING STATEMENT
   ============================================ */
.positioning-statement {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 50px;
    text-align: center;
    box-shadow: var(--shadow-xl);
    margin: 40px 0;
    border: 2px solid var(--accent);
    position: relative;
}

.positioning-statement::before {
    content: '"';
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 100px;
    color: var(--accent);
    font-family: serif;
    background: white;
    width: 80px;
    height: 80px;
    line-height: 120px;
    border-radius: 50%;
    box-shadow: var(--shadow);
}

.positioning-text {
    font-size: 1.3rem;
    line-height: 1.8;
    color: var(--primary);
    font-weight: 500;
    margin-top: 30px;
}

.positioning-highlight {
    color: var(--accent);
    font-weight: 700;
}

/* ============================================
   TINYMCE CONTENT
   ============================================ */
.tinymce-content {
    font-size: 1rem;
    line-height: 1.8;
    color: var(--dark);
}

.tinymce-content p {
    margin-bottom: 1.5rem;
}

.tinymce-content ul, 
.tinymce-content ol {
    margin: 1.5rem 0;
    padding-left: 2rem;
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

/* ============================================
   ANIMATIONS
   ============================================ */
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 768px) {
    .section {
        padding: 60px 0;
    }
    
    .iso-showcase {
        padding: 40px 20px;
    }
    
    .integrity-section {
        padding: 30px 20px;
    }
    
    .positioning-statement {
        padding: 40px 20px;
    }
    
    .positioning-text {
        font-size: 1.1rem;
    }
}

@media (max-width: 576px) {
    .licensing-grid {
        grid-template-columns: 1fr;
    }
    
    .license-card {
        padding: 15px;
    }
}
</style>

<?php
// ============================================
// FONCTIONS DE TRAITEMENT
// ============================================

function fix_image_path($image_path) {
    if (empty($image_path)) return null;
    
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        return $image_path;
    }
    
    $image_path = preg_replace('/\.\.\//', '', $image_path);
    $image_path = ltrim($image_path, '/');
    
    return base_url($image_path);
}

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
?>

<!-- Scroll Progress -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- ============================================
     HERO SECTION DYNAMIQUE
     ============================================ -->
<?php if (!empty($page)): ?>
<section class="page-hero">
    <div class="page-hero-content">
        <h1 class="page-hero-title"><?php echo htmlspecialchars($page['titre_page']); ?> & <span>Sustainability</span></h1>
        <p class="page-hero-subtitle"><?php echo htmlspecialchars($page['meta_description'] ?? 'Environmental, Social and Governance Excellence'); ?></p>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     SECTIONS DYNAMIQUES
     ============================================ -->
<?php if (!empty($sections)): ?>
    <?php foreach ($sections as $section): 
        $type = $section['type_section'];
        $options = !empty($section['options_json']) ? json_decode($section['options_json'], true) : [];
        if (!is_array($options)) $options = [];
        
        $raw_content = $section['contenu_texte'] ?? '';
        $content_with_fixed_images = fix_content_images($raw_content);
        
        switch($type):
            
            // ============================================
            // TYPE 1: TEXTE - Governance, Compliance & Transparency
            // ============================================
            case 'texte': 
                $layout = $options['layout'] ?? 'simple';
                $container_class = $options['container_class'] ?? 'section-container';
                $text_align = $options['text_align'] ?? 'text-start';
                $content_cols = $options['content_cols'] ?? 'col-lg-10 col-xl-8 mx-auto';
    ?>
                <section class="section <?php echo $section['custom_class'] ?? ''; ?>" 
                         style="background: <?php echo $options['bg_color'] ?? 'transparent'; ?>;">
                    <div class="<?php echo $container_class; ?>">
                        <div class="row justify-content-center">
                            <div class="<?php echo $content_cols; ?>">
                                
                                <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                                    <div class="section-header">
                                        <?php if (!empty($section['titre_section'])): ?>
                                            <span class="section-tag"><?php echo htmlspecialchars($section['titre_section']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($section['sous_titre'])): ?>
                                            <h2 class="section-title"><?php echo htmlspecialchars($section['sous_titre']); ?></h2>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="tinymce-content <?php echo $text_align; ?>">
                                    <?php echo $content_with_fixed_images; ?>
                                </div>
                                
                                <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                    <div class="text-center mt-5">
                                        <a href="<?php echo htmlspecialchars($section['bouton_lien']); ?>" class="btn btn-primary btn-lg rounded-pill">
                                            <?php echo htmlspecialchars($section['bouton_texte']); ?> 
                                            <i class="bi bi-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // TYPE 2: LISTE - Governance Commitments & ISO Standards
            // ============================================
            case 'liste': 
                $list_items = [];
                $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
                
                if (strpos($decoded_content, '<li>') !== false) {
                    preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
                    $list_items = $matches[1] ?? [];
                } else {
                    $lines = explode("\n", $decoded_content);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            $list_items[] = $line;
                        }
                    }
                }
                
                $icon = $options['icon'] ?? 'check-circle-fill';
                $icon_color = $options['icon_color'] ?? 'success';
                $columns = $options['columns'] ?? 2;
                $col_class = ($columns == 1) ? 'col-12' : 'col-md-6';
    ?>
                <section class="section <?php echo $section['custom_class'] ?? ''; ?>">
                    <div class="section-container">
                        
                        <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                            <div class="section-header">
                                <?php if (!empty($section['titre_section'])): ?>
                                    <span class="section-tag"><?php echo htmlspecialchars($section['titre_section']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($section['sous_titre'])): ?>
                                    <h2 class="section-title"><?php echo htmlspecialchars($section['sous_titre']); ?></h2>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($list_items)): ?>
                            <div class="row g-4">
                                <?php foreach ($list_items as $item): 
                                    $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                                ?>
                                <div class="<?php echo $col_class; ?>">
                                    <div class="commitment-card h-100">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="commitment-icon">
                                                <i class="bi bi-<?php echo $icon; ?> text-<?php echo $icon_color; ?>"></i>
                                            </div>
                                            <div class="tinymce-content">
                                                <?php echo fix_content_images($item); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // TYPE 3: GRILLE - ISO Certifications Showcase
            // ============================================
            case 'grille': 
                $items = [];
                $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
                
                if (strpos($decoded_content, '<li>') !== false) {
                    preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
                    $items = $matches[1] ?? [];
                } else {
                    $lines = explode("\n", $decoded_content);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            $items[] = $line;
                        }
                    }
                }
                
                $columns = $options['columns'] ?? 3;
                if ($columns == 2) {
                    $col_class = 'col-md-6';
                } elseif ($columns == 4) {
                    $col_class = 'col-md-6 col-lg-3';
                } else {
                    $col_class = 'col-md-6 col-lg-4';
                }
    ?>
                <section class="section <?php echo $section['custom_class'] ?? ''; ?>">
                    <div class="section-container">
                        
                        <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                            <div class="section-header">
                                <?php if (!empty($section['titre_section'])): ?>
                                    <span class="section-tag"><?php echo htmlspecialchars($section['titre_section']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($section['sous_titre'])): ?>
                                    <h2 class="section-title"><?php echo htmlspecialchars($section['sous_titre']); ?></h2>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="iso-showcase">
                            <div class="row g-4">
                                <?php foreach ($items as $item): 
                                    $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                                    $parts = explode(' - ', $item, 2);
                                    $iso_number = $parts[0] ?? $item;
                                    $iso_name = $parts[1] ?? '';
                                ?>
                                <div class="<?php echo $col_class; ?>">
                                    <div class="iso-item text-center">
                                        <div class="iso-number">
                                            <?php echo htmlspecialchars($iso_number); ?>
                                        </div>
                                        <?php if (!empty($iso_name)): ?>
                                            <div class="iso-name">
                                                <?php echo htmlspecialchars($iso_name); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // TYPE 4: LISTE_ITEM - Regulatory Licensing
            // ============================================
            case 'liste_item': 
                $list_items = [];
                $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
                
                if (strpos($decoded_content, '<li>') !== false) {
                    preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
                    $list_items = $matches[1] ?? [];
                } else {
                    $lines = explode("\n", $decoded_content);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            $list_items[] = $line;
                        }
                    }
                }
                
                $columns = $options['columns'] ?? 2;
                if ($columns == 1) {
                    $col_class = 'col-12';
                } elseif ($columns == 3) {
                    $col_class = 'col-md-6 col-lg-4';
                } else {
                    $col_class = 'col-md-6';
                }
                
                $icons = array('building', 'award', 'tree', 'file-check', 'trash', 'geo-alt', 'box', 'shield', 'gear', 'passport', 'flask', 'patch-check', 'droplet', 'heart-pulse');
    ?>
                <section class="section <?php echo $section['custom_class'] ?? ''; ?>">
                    <div class="section-container">
                        
                        <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                            <div class="section-header">
                                <?php if (!empty($section['titre_section'])): ?>
                                    <span class="section-tag"><?php echo htmlspecialchars($section['titre_section']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($section['sous_titre'])): ?>
                                    <h2 class="section-title"><?php echo htmlspecialchars($section['sous_titre']); ?></h2>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($list_items)): ?>
                            <div class="licensing-grid">
                                <?php 
                                $icon_index = 0;
                                foreach ($list_items as $item): 
                                    $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                                    $parts = explode(' – ', $item, 2);
                                    if (count($parts) < 2) {
                                        $parts = explode(' - ', $item, 2);
                                    }
                                    $license_title = $parts[0] ?? $item;
                                    $license_authority = $parts[1] ?? '';
                                    
                                    $icon = $icons[$icon_index % count($icons)];
                                    $icon_index++;
                                ?>
                                <div class="license-card">
                                    <div class="license-icon">
                                        <i class="bi bi-<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="license-content">
                                        <div class="license-title"><?php echo htmlspecialchars($license_title); ?></div>
                                        <?php if (!empty($license_authority)): ?>
                                            <div class="license-authority"><?php echo htmlspecialchars($license_authority); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // TYPE DEFAULT
            // ============================================
            default: 
    ?>
                <section class="section <?php echo $section['custom_class'] ?? ''; ?>">
                    <div class="section-container">
                        
                        <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                            <div class="section-header">
                                <?php if (!empty($section['titre_section'])): ?>
                                    <span class="section-tag"><?php echo htmlspecialchars($section['titre_section']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($section['sous_titre'])): ?>
                                    <h2 class="section-title"><?php echo htmlspecialchars($section['sous_titre']); ?></h2>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="tinymce-content">
                            <?php echo $content_with_fixed_images; ?>
                        </div>
                        
                    </div>
                </section>
    <?php
                break;

        endswitch;
    endforeach;
endif;
?>

<!-- ============================================
     STRATEGIC POSITIONING STATEMENT (fixe)
     ============================================ -->
<section class="section">
    <div class="section-container">
        <div class="positioning-statement">
            <p class="positioning-text">
                <span class="positioning-highlight">AGF-PHYTOMED INDUSTRIES</span> facility is being structured by African Green Farmers Limited as a bankable, audit-ready, ESG-aligned industrial platform capable of absorbing institutional capital responsibility while delivering measurable environmental, social, and financial returns.
            </p>
            <p class="mt-4 fs-5 text-gray">
                Our governance framework reduces systemic risk, strengthens regulatory acceptance, and enhances international market credibility - positioning AGF as a trusted partner for long-term, impact-oriented investment.
            </p>
        </div>
    </div>
</section>

<script>
// Scroll Progress
window.addEventListener('scroll', () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.getElementById('scrollProgress').style.width = scrolled + '%';
    
    // Back to top button
    const backToTop = document.getElementById('backToTop');
    if (winScroll > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

// Back to top
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
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.section-header, .commitment-card, .iso-item, .license-card, .integrity-card').forEach(el => {
        observer.observe(el);
    });
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>