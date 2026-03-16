<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
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
    --shadow: 0 10px 20px rgba(0,0,0,0.1);
    --shadow-lg: 0 20px 40px rgba(0,0,0,0.15);
    --shadow-hover: 0 30px 50px rgba(15, 76, 58, 0.25);
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    --border-radius-sm: 12px;
    --border-radius-md: 20px;
    --border-radius-lg: 30px;
    --font-primary: 'Inter', sans-serif;
    --font-secondary: 'Playfair Display', serif;
}

/* ============================================
   SECTION STYLES
   ============================================ */
.section { 
    padding: 80px 0; 
    position: relative; 
}

.section:nth-child(even) { 
    background: var(--gray-soft); 
}

.section-container { 
    max-width: 1400px; 
    margin: 0 auto; 
    padding: 0 20px; 
}

.section-header { 
    text-align: center; 
    margin-bottom: 60px; 
}

.section-tag { 
    display: inline-block; 
    background: var(--accent-soft); 
    color: var(--primary); 
    padding: 8px 20px; 
    border-radius: 50px; 
    font-size: 0.85rem; 
    font-weight: 600; 
    text-transform: uppercase; 
    letter-spacing: 1px; 
    margin-bottom: 15px; 
}

.section-title { 
    font-family: var(--font-secondary); 
    font-size: clamp(2rem, 4vw, 3rem); 
    color: var(--dark); 
    margin-bottom: 20px; 
    position: relative; 
    display: inline-block; 
}

.section-title::after { 
    content: ''; 
    position: absolute; 
    bottom: -10px; 
    left: 50%; 
    transform: translateX(-50%); 
    width: 60px; 
    height: 3px; 
    background: var(--accent); 
}

/* ============================================
   HERO STYLES
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

.page-hero.with-image {
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
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
    z-index: 1;
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
    opacity: 0;
    transform: translateY(-30px);
    transition: var(--transition);
}

.page-hero-title.visible {
    opacity: 1;
    transform: translateY(0);
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
    margin: 20px auto 0;
    line-height: 1.8;
    opacity: 0;
    transform: translateY(30px);
    transition: var(--transition);
    transition-delay: 0.3s;
}

.page-hero-subtitle.visible {
    opacity: 1;
    transform: translateY(0);
}

/* ============================================
   TYPE 1: LISTE_SIMPLE - Liste avec puces
   ============================================ */
.liste-simple {
    max-width: 800px;
    margin: 0 auto;
}

.liste-simple ul {
    list-style: none;
    padding: 0;
}

.liste-simple li {
    padding: 15px 20px;
    margin-bottom: 10px;
    background: white;
    border-radius: var(--border-radius-sm);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: var(--transition);
    opacity: 0;
    transform: translateX(-30px);
}

.liste-simple li.visible {
    opacity: 1;
    transform: translateX(0);
}

.liste-simple li:hover {
    transform: translateX(10px);
    box-shadow: var(--shadow-hover);
    background: var(--primary-soft);
}

.liste-simple li i {
    font-size: 1.5rem;
    color: var(--accent);
    min-width: 40px;
    text-align: center;
}

.liste-simple li span {
    flex: 1;
    font-size: 1rem;
    line-height: 1.6;
}

/* ============================================
   TYPE 2: LISTE_INLINE - Liste horizontale
   ============================================ */
.liste-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
}

.liste-inline-item {
    background: white;
    border-radius: 50px;
    padding: 12px 25px;
    box-shadow: var(--shadow);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: var(--transition);
    opacity: 0;
    transform: scale(0.9);
}

.liste-inline-item.visible {
    opacity: 1;
    transform: scale(1);
}

.liste-inline-item:hover {
    transform: scale(1.1);
    background: var(--primary);
    color: white;
    box-shadow: var(--shadow-hover);
}

.liste-inline-item:hover i {
    color: var(--accent);
}

.liste-inline-item i {
    font-size: 1.2rem;
    color: var(--accent);
    transition: var(--transition);
}

/* ============================================
   TYPE 3: LISTE_CARD - Cartes horizontales
   ============================================ */
.liste-card-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-width: 900px;
    margin: 0 auto;
}

.liste-card-item {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 25px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: flex-start;
    gap: 20px;
    transition: var(--transition);
    opacity: 0;
    transform: translateY(30px);
}

.liste-card-item.visible {
    opacity: 1;
    transform: translateY(0);
}

.liste-card-item:hover {
    transform: translateX(20px);
    box-shadow: var(--shadow-hover);
    background: linear-gradient(135deg, white, var(--primary-soft));
}

.liste-card-icon {
    min-width: 60px;
    height: 60px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--primary);
    transition: var(--transition);
}

.liste-card-item:hover .liste-card-icon {
    background: var(--accent);
    color: white;
    transform: rotate(360deg);
}

.liste-card-content {
    flex: 1;
}

.liste-card-title {
    font-size: 1.2rem;
    color: var(--primary);
    margin-bottom: 10px;
    font-weight: 700;
}

.liste-card-text {
    color: var(--gray);
    line-height: 1.6;
    font-size: 0.95rem;
}

/* ============================================
   TYPE 4: GRILLE_CARD - Grille de cartes verticales
   ============================================ */
.grille-card-items {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}

.grille-card-item {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 30px 25px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    text-align: center;
    opacity: 0;
    transform: translateY(30px);
    border-bottom: 4px solid transparent;
}

.grille-card-item.visible {
    opacity: 1;
    transform: translateY(0);
}

.grille-card-item:hover {
    transform: translateY(-15px);
    box-shadow: var(--shadow-hover);
    border-bottom-color: var(--accent);
}

.grille-card-icon {
    width: 80px;
    height: 80px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 20px;
    color: var(--primary);
    transition: var(--transition);
}

.grille-card-item:hover .grille-card-icon {
    background: var(--accent);
    color: white;
    transform: rotate(360deg) scale(1.1);
}

.grille-card-title {
    font-size: 1.3rem;
    color: var(--primary);
    margin-bottom: 15px;
    font-weight: 700;
}

.grille-card-text {
    color: var(--gray);
    line-height: 1.6;
    font-size: 0.95rem;
    margin-bottom: 20px;
}

.grille-card-footer {
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid var(--gray-light);
}

/* ============================================
   TYPE 5: GRILLE_HORIZONTALE - Grille horizontale
   ============================================ */
.grille-horizontale {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.grille-horizontale-item {
    background: white;
    border-radius: var(--border-radius-sm);
    padding: 15px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: var(--transition);
    opacity: 0;
    transform: scale(0.95);
}

.grille-horizontale-item.visible {
    opacity: 1;
    transform: scale(1);
}

.grille-horizontale-item:hover {
    transform: scale(1.02) translateY(-5px);
    background: var(--primary);
    color: white;
    box-shadow: var(--shadow-hover);
}

.grille-horizontale-item:hover .grille-horizontale-icon {
    background: var(--accent);
    color: var(--primary);
}

.grille-horizontale-icon {
    min-width: 40px;
    height: 40px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: var(--primary);
    transition: var(--transition);
}

.grille-horizontale-content {
    flex: 1;
}

.grille-horizontale-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 3px;
}

.grille-horizontale-text {
    font-size: 0.85rem;
    opacity: 0.9;
}

/* ============================================
   TYPE 6: GRILLE_MOSAIC - Grille mosaïque
   ============================================ */
.grille-mosaic {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.grille-mosaic-item {
    position: relative;
    border-radius: var(--border-radius-md);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    opacity: 0;
    transform: translateY(30px);
}

.grille-mosaic-item.visible {
    opacity: 1;
    transform: translateY(0);
}

.grille-mosaic-item:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.grille-mosaic-item.large {
    grid-column: span 2;
    grid-row: span 2;
}

.grille-mosaic-item.medium {
    grid-column: span 1;
    grid-row: span 2;
}

.grille-mosaic-content {
    background: white;
    padding: 25px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.grille-mosaic-icon {
    font-size: 2rem;
    color: var(--accent);
    margin-bottom: 15px;
}

.grille-mosaic-title {
    font-size: 1.2rem;
    color: var(--primary);
    margin-bottom: 10px;
    font-weight: 700;
}

.grille-mosaic-text {
    color: var(--gray);
    line-height: 1.6;
    font-size: 0.95rem;
    flex: 1;
}

.grille-mosaic-footer {
    margin-top: 15px;
    color: var(--accent);
    font-weight: 600;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 991px) { 
    .section { padding: 60px 0; }
    .grille-mosaic { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .liste-card-item { flex-direction: column; align-items: flex-start; }
    .grille-mosaic { grid-template-columns: 1fr; }
    .grille-mosaic-item.large,
    .grille-mosaic-item.medium { grid-column: span 1; grid-row: span 1; }
}

@media (max-width: 576px) {
    .section { padding: 40px 0; }
    .liste-inline-item { width: 100%; justify-content: center; }
}
</style>

<?php
// ============================================
// FONCTIONS UTILITAIRES
// ============================================

function fix_image_path($image_path) {
    if (empty($image_path)) return null;
    if (filter_var($image_path, FILTER_VALIDATE_URL)) return $image_path;
    $image_path = preg_replace('/\.\.\//', '', $image_path);
    $image_path = ltrim($image_path, '/');
    return base_url($image_path);
}

function fix_content_images($content) {
    if (empty($content)) return '';
    $base_url = rtrim(base_url(), '/');
    $content = preg_replace('/src=["\'](?:\.\.\/)+(attachments\/[^"\']+)["\']/i', 'src="/$1"', $content);
    $content = preg_replace('/src=["\'](\/attachments\/[^"\']+)["\']/i', 'src="' . $base_url . '$1"', $content);
    return $content;
}

function extract_list_items($content) {
    $items = [];
    if (strpos($content, '<li>') !== false) {
        preg_match_all('/<li>(.*?)<\/li>/s', $content, $matches);
        $items = $matches[1] ?? [];
    } else {
        $lines = explode("\n", strip_tags($content));
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strlen($line) > 3) {
                $items[] = $line;
            }
        }
    }
    return $items;
}

function get_icon_for_index($index) {
    $icons = [
        'check-circle', 'star', 'heart', 'award', 'gem', 'lightbulb',
        'rocket', 'shield', 'trophy', 'flag', 'graph-up', 'gear'
    ];
    return $icons[$index % count($icons)];
}

function get_color_class($index) {
    $colors = ['primary', 'success', 'warning', 'info', 'danger', 'accent'];
    return $colors[$index % count($colors)];
}
?>

<!-- ============================================
     SECTIONS DYNAMIQUES
     ============================================ -->
<?php if (!empty($sections)): ?>
    <?php foreach ($sections as $section): 
        $type = $section['type_section'] ?? 'texte';
        $options = json_decode($section['options_json'] ?? '', true) ?: [];
        $raw_content = $section['contenu_texte'] ?? '';
        $content_with_fixed_images = fix_content_images($raw_content);
        $custom_class = htmlspecialchars($section['custom_class'] ?? '');
        
        switch($type):
            
            // ============================================
            // TYPE 1: HERO - Bannière
            // ============================================
            case 'hero': 
                $hero_image = !empty($section['image_url']) ? fix_image_path($section['image_url']) : '';
                $overlay_opacity = $options['overlay_opacity'] ?? 0.7;
                $height = $options['height'] ?? '60vh';
                $hero_style = !empty($hero_image) ? "background: linear-gradient(rgba(10, 52, 38, {$overlay_opacity}), rgba(15, 76, 58, {$overlay_opacity})), url('{$hero_image}') no-repeat center center/cover; background-attachment: fixed;" : '';
    ?>
                <section class="page-hero <?php echo !empty($hero_image) ? 'with-image' : ''; ?> <?php echo $custom_class; ?>" 
                         style="<?php echo $hero_style; ?> min-height: <?php echo $height; ?>;">
                    <div class="page-hero-content">
                        <?php if (!empty($section['titre_section'])): ?>
                            <h1 class="page-hero-title">
                                <?php 
                                $title = htmlspecialchars($section['titre_section']);
                                echo (strpos($title, '&') !== false) 
                                    ? str_replace('&', ' <span>&', $title) . '</span>' 
                                    : $title;
                                ?>
                            </h1>
                        <?php endif; ?>
                        
                        <?php if (!empty($section['sous_titre'])): ?>
                            <p class="page-hero-subtitle"><?php echo htmlspecialchars($section['sous_titre']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($raw_content)): ?>
                            <div class="tinymce-content text-white mt-4"><?php echo $content_with_fixed_images; ?></div>
                        <?php endif; ?>
                    </div>
                </section>
    <?php
                break;

           

 // ============================================
// TYPE : GRILLE_CARD - Grille de cartes verticales (VERSION AMÉLIORÉE)
// ============================================
case 'grille_card':
    $items = extract_list_items($raw_content);
    $columns = $options['columns'] ?? 2;
    $gap = $options['gap'] ?? 25;
    $icon_size = $options['icon_size'] ?? 70;
    
    if ($columns == 2) $grid_class = 'grille-card-items-2';
    elseif ($columns == 4) $grid_class = 'grille-card-items-4';
    else $grid_class = 'grille-card-items-3';
?>
    <style>
    .grille-card-items-2 { 
        display: grid; 
        grid-template-columns: repeat(2, 1fr); 
        gap: <?php echo $gap; ?>px;
    }
    .grille-card-items-3 { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); 
        gap: <?php echo $gap; ?>px;
    }
    .grille-card-items-4 { 
        display: grid; 
        grid-template-columns: repeat(4, 1fr); 
        gap: <?php echo $gap - 5; ?>px;
    }
    
    .grille-card-item {
        background: white;
        border-radius: var(--border-radius-md);
        padding: 30px 25px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        opacity: 0;
        transform: translateY(30px);
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        border-bottom: 4px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .grille-card-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-soft) 0%, transparent 100%);
        opacity: 0;
        transition: var(--transition);
        z-index: 0;
    }
    
    .grille-card-item:hover::before {
        opacity: 1;
    }
    
    .grille-card-item.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    .grille-card-item:hover {
        transform: translateY(-12px);
        box-shadow: var(--shadow-hover);
        border-bottom-color: var(--accent);
    }
    
    .grille-card-icon-wrapper {
        margin-bottom: 25px;
        position: relative;
        z-index: 1;
        width: 100%;
        display: flex;
        justify-content: flex-start;
        border-bottom: 2px solid var(--accent-soft);
        padding-bottom: 20px;
    }
    
    .grille-card-icon {
        width: <?php echo $icon_size; ?>px;
        height: <?php echo $icon_size; ?>px;
        background: var(--primary-soft);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: <?php echo $icon_size * 0.4; ?>px;
        color: var(--primary);
        transition: var(--transition);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .grille-card-item:hover .grille-card-icon {
        background: var(--accent);
        color: white;
        transform: rotate(360deg) scale(1.1);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
    }
    
    .grille-card-title {
        font-size: 1.4rem;
        color: var(--primary);
        margin-bottom: 20px;
        font-weight: 700;
        width: 100%;
        text-align: left;
        position: relative;
        z-index: 1;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--gray-light);
        letter-spacing: -0.02em;
        line-height: 1.3;
    }
    
    .grille-card-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--accent);
        border-radius: 3px;
    }
    
    .grille-card-content {
        position: relative;
        z-index: 1;
        width: 100%;
        flex: 1;
    }
    
    .grille-card-text {
        color: var(--gray);
        line-height: 1.7;
        font-size: 0.95rem;
        margin-bottom: 20px;
        width: 100%;
        text-align: left;
    }
    
    .grille-card-text p {
        margin-bottom: 15px;
        text-align: left;
    }
    
    .grille-card-text p:last-child {
        margin-bottom: 0;
    }
    
    .grille-card-text ul, 
    .grille-card-text ol {
        padding-left: 25px;
        margin: 15px 0;
        text-align: left;
    }
    
    .grille-card-text li {
        text-align: left;
        margin-bottom: 8px;
    }
    
    .grille-card-text li::marker {
        color: var(--accent);
    }
    
    .grille-card-text strong, 
    .grille-card-text b {
        color: var(--primary);
        font-weight: 600;
    }
    
    .grille-card-text h4, 
    .grille-card-text h5 {
        color: var(--primary-dark);
        margin: 20px 0 10px 0;
        font-weight: 600;
    }
    
    .grille-card-text h4:first-child,
    .grille-card-text h5:first-child {
        margin-top: 0;
    }
    
    .grille-card-divider {
        width: 100%;
        height: 1px;
        background: linear-gradient(90deg, var(--accent), transparent);
        margin: 20px 0;
    }
    
    .grille-card-footer {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid var(--gray-light);
        width: 100%;
        text-align: left;
        color: var(--accent);
        font-weight: 600;
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .grille-card-footer i {
        transition: var(--transition);
    }
    
    .grille-card-footer:hover i {
        transform: translateX(10px);
    }
    
    .grille-card-badge {
        display: inline-block;
        background: var(--accent-soft);
        color: var(--primary);
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 15px;
        border: 1px solid var(--accent);
    }
    
    @media (max-width: 991px) {
        .grille-card-items-3 { grid-template-columns: repeat(2, 1fr); }
        .grille-card-items-4 { grid-template-columns: repeat(3, 1fr); }
    }
    
    @media (max-width: 768px) {
        .grille-card-items-2,
        .grille-card-items-3,
        .grille-card-items-4 { 
            grid-template-columns: 1fr; 
            gap: 20px;
        }
        
        .grille-card-item { padding: 25px 20px; }
        .grille-card-title { font-size: 1.3rem; }
        .grille-card-icon { width: 60px; height: 60px; font-size: 1.8rem; }
    }
    
    @media (max-width: 576px) {
        .grille-card-item { padding: 20px 15px; }
        .grille-card-title { font-size: 1.2rem; }
        .grille-card-icon { width: 50px; height: 50px; font-size: 1.5rem; }
    }
    </style>
    
    <section class="section <?php echo $custom_class; ?>">
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
            
            <div class="grille-card-items <?php echo $grid_class; ?>">
                <?php foreach ($items as $index => $item): ?>
                    <?php 
                    // Format amélioré: Titre|Description|Footer|Badge
                    $parts = explode('|', $item, 4);
                    $title = trim($parts[0] ?? '');
                    $desc = trim($parts[1] ?? '');
                    $footer = trim($parts[2] ?? '');
                    $badge = trim($parts[3] ?? '');
                    
                    // Si pas de pipe, tout mettre dans description
                    if (empty($title) && empty($desc) && empty($footer) && empty($badge)) {
                        // Essayer de séparer par sauts de ligne
                        $lines = explode("\n", $item, 2);
                        if (count($lines) > 1) {
                            $title = trim($lines[0]);
                            $desc = trim($lines[1]);
                        } else {
                            $desc = $item;
                        }
                    }
                    ?>
                    <div class="grille-card-item">
                        <!-- Icône séparée avec bordure -->
                        <div class="grille-card-icon-wrapper">
                            <div class="grille-card-icon">
                                <i class="bi bi-<?php echo get_icon_for_index($index); ?>"></i>
                            </div>
                        </div>
                        
                        <!-- Titre avec séparation -->
                        <?php if (!empty($title)): ?>
                            <h3 class="grille-card-title"><?php echo fix_content_images($title); ?></h3>
                        <?php endif; ?>
                        
                        <!-- Contenu principal -->
                        <div class="grille-card-content">
                            <?php if (!empty($desc)): ?>
                                <div class="grille-card-text">
                                    <?php 
                                    // Afficher la description en conservant la structure
                                    if (strpos($desc, '<') !== false) {
                                        // Si contient du HTML, l'afficher directement
                                        echo fix_content_images($desc);
                                    } else {
                                        // Sinon, gérer les retours à la ligne
                                        $desc_lines = preg_split('/\r\n|\r|\n/', $desc);
                                        foreach ($desc_lines as $line) {
                                            $line = trim($line);
                                            if (!empty($line)) {
                                                // Détecter si c'est une liste
                                                if (preg_match('/^[*-]\s+(.+)/', $line, $match)) {
                                                    echo '<p class="ps-3">• ' . fix_content_images($match[1]) . '</p>';
                                                }
                                                // Détecter si c'est un titre (gras avec :)
                                                elseif (preg_match('/^([^:]+):\s*(.+)/', $line, $match)) {
                                                    echo '<p><strong>' . fix_content_images($match[1]) . ':</strong> ' . fix_content_images($match[2]) . '</p>';
                                                }
                                                else {
                                                    echo '<p>' . fix_content_images($line) . '</p>';
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badge optionnel -->
                            <?php if (!empty($badge)): ?>
                                <div class="grille-card-badge">
                                    <?php echo fix_content_images($badge); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Footer avec séparation -->
                        <?php if (!empty($footer)): ?>
                            <div class="grille-card-footer">
                                <span><?php echo fix_content_images($footer); ?></span>
                                <i class="bi bi-arrow-right"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    </section>
<?php
    break;
        
            // ============================================
            // TYPE TEXTE (fallback)
            // ============================================
            case 'texte':
            default: 
    ?>
                <section class="section <?php echo $custom_class; ?>">
                    <div class="section-container">
                        <div class="tinymce-content"><?php echo $content_with_fixed_images; ?></div>
                    </div>
                </section>
    <?php
                break;

        endswitch;
    endforeach;
endif;
?>

<!-- ============================================
     JAVASCRIPT POUR ANIMATIONS DYNAMIQUES
     ============================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.2 });

    // Observer tous les éléments animés
    document.querySelectorAll('.page-hero-title, .page-hero-subtitle, .liste-simple li, .liste-inline-item, .liste-card-item, .grille-card-item, .grille-horizontale-item, .grille-mosaic-item').forEach((el, index) => {
        el.style.transitionDelay = (index * 0.05) + 's';
        observer.observe(el);
    });

    // Barre de progression
    const scrollProgress = document.createElement('div');
    scrollProgress.className = 'scroll-progress';
    scrollProgress.style.cssText = 'position:fixed; top:0; left:0; width:0%; height:4px; background:linear-gradient(90deg, var(--accent), var(--primary)); z-index:9999; transition:width 0.1s;';
    document.body.appendChild(scrollProgress);

    window.addEventListener('scroll', () => {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        scrollProgress.style.width = scrolled + '%';
    });
});
</script>









<!-- ═══════════════════════════════════════════════════════ -->
<!-- APPELS À L'ACTION - GRILLE DYNAMIQUE -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php if (!empty($appels_action)): ?>
<section class="cta-section" id="cta">
    <div class="container-fluid px-4">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <span class="section-tag">Take Action Now</span>
            <h2 class="section-title">Our Calls to Action</h2>
        </div>
        
        <div class="cta-grid">
            <?php 
            $active_ctas = array_filter($appels_action, function($cta) {
                if (!empty($cta['est_active']) && $cta['est_active'] == 0) return false;
                if (!empty($cta['date_expiration']) && $cta['date_expiration'] < date('Y-m-d')) return false;
                return true;
            });

            foreach ($active_ctas as $cta): 
                $bg_style = !empty($cta['image_fond_url']) ? 
                    'style="background-image: url(\'' . htmlspecialchars($cta['image_fond_url']) . '\');"' : '';
            ?>
            
            <div class="cta-item" <?= $bg_style ?> data-aos="zoom-in">
                <div class="cta-overlay">
                    
                    <?php if (!empty($cta['type_public']) && $cta['type_public'] !== 'all'): ?>
                        <span class="cta-badge">
                            <?= htmlspecialchars($cta['type_public']) ?>
                        </span>
                    <?php endif; ?>
                    
                    <div class="cta-content">
                        <h3><?= htmlspecialchars($cta['titre']) ?></h3>
                        <p><?= htmlspecialchars($cta['sous_titre']) ?></p>
                    </div>
                    
                    <a href="<?= htmlspecialchars($cta['bouton_lien']) ?>" class="cta-btn">
                        <?= htmlspecialchars($cta['bouton_texte']) ?>
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                
                <div class="cta-shine"></div>
            </div>
            
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<style>
.cta-section {
    padding: 100px 0;
    background: #f8f9fa;
}

.cta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.cta-item {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    min-height: 400px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    display: flex;
    flex-direction: column;
    transition: var(--transition);
    cursor: pointer;
}

.cta-item[style*="background-image"] {
    background-size: cover;
    background-position: center;
}

.cta-overlay {
    flex: 1;
    padding: 40px 30px;
    background: linear-gradient(to bottom, rgba(15, 76, 58, 0.8), rgba(10, 51, 38, 0.95));
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    z-index: 2;
    transition: var(--transition);
}

.cta-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 60px rgba(0,0,0,0.2);
}

.cta-item:hover .cta-overlay {
    background: linear-gradient(to bottom, rgba(15, 76, 58, 0.9), rgba(10, 51, 38, 0.98));
}

.cta-badge {
    align-self: flex-start;
    background: var(--accent);
    color: var(--primary-dark);
    font-size: 0.75rem;
    font-weight: 800;
    padding: 6px 16px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
}

.cta-content h3 {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    line-height: 1.3;
}

.cta-content p {
    color: rgba(255,255,255,0.8);
    font-size: 1rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.cta-btn {
    background: transparent;
    border: 2px solid var(--accent);
    color: var(--accent);
    padding: 14px 28px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.cta-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: var(--accent);
    transition: left 0.3s ease;
    z-index: -1;
}

.cta-btn:hover {
    color: var(--primary-dark);
    transform: translateX(5px);
}

.cta-btn:hover::before {
    left: 0;
}

.cta-btn i {
    transition: transform 0.3s ease;
}

.cta-btn:hover i {
    transform: translateX(5px);
}

.cta-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s ease;
    z-index: 3;
    pointer-events: none;
}

.cta-item:hover .cta-shine {
    left: 150%;
}

@media (max-width: 768px) {
    .cta-section { padding: 60px 0; }
    .cta-grid { grid-template-columns: 1fr; gap: 20px; }
    .cta-item { min-height: 350px; }
    .cta-overlay { padding: 30px 25px; }
}
</style>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({
    duration: 800,
    easing: 'ease-out-cubic',
    once: true,
    offset: 50
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>