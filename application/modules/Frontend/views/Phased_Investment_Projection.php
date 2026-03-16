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
    --success: #10b981;
    --warning: #f59e0b;
    --info: #3b82f6;
    --shadow: 0 10px 20px rgba(0,0,0,0.1);
    --shadow-lg: 0 20px 40px rgba(0,0,0,0.15);
    --shadow-hover: 0 30px 50px rgba(15, 76, 58, 0.25);
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    --border-radius-sm: 12px;
    --border-radius-md: 20px;
    --font-primary: 'Inter', sans-serif;
    --font-secondary: 'Playfair Display', serif;
}

.section { padding: 80px 0; position: relative; }
.section-container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
.section-header { text-align: center; margin-bottom: 60px; }
.section-tag { display: inline-block; background: var(--accent-soft); color: var(--primary); padding: 8px 20px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }
.section-title { font-family: var(--font-secondary); font-size: clamp(2rem, 4vw, 3rem); color: var(--dark); margin-bottom: 20px; position: relative; display: inline-block; }
.section-title::after { content: ''; position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); width: 60px; height: 3px; background: var(--accent); }

/* HERO */
.page-hero { position: relative; min-height: 60vh; background: linear-gradient(135deg, #0a4c3a, #1e6b52); display: flex; align-items: center; justify-content: center; text-align: center; overflow: hidden; }
.page-hero.with-image { background: linear-gradient(rgba(10, 52, 38, 0.7), rgba(15, 76, 58, 0.8)), var(--hero-image) no-repeat center center/cover; background-attachment: fixed; }
.page-hero-content { position: relative; z-index: 2; max-width: 900px; padding: 80px 20px; }
.page-hero-title { font-family: var(--font-secondary); font-size: clamp(2.5rem, 6vw, 4rem); font-weight: 700; color: white; margin-bottom: 20px; }
.page-hero-title span { color: var(--accent); }
.page-hero-subtitle { font-size: clamp(1.1rem, 2vw, 1.4rem); color: rgba(255,255,255,0.95); max-width: 700px; margin: 0 auto; line-height: 1.8; }

/* LISTE_CARD - Cartes Partenaires */
.partner-grid-section { background: linear-gradient(135deg, var(--light) 0%, var(--gray-soft) 100%); }
.partner-card { background: white; border-radius: var(--border-radius-md); padding: 35px 30px; box-shadow: var(--shadow); transition: var(--transition); height: 100%; position: relative; overflow: hidden; border-top: 4px solid transparent; display: flex; flex-direction: column; }
.partner-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-hover); border-top-color: var(--accent); }
.partner-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-soft) 0%, transparent 100%); opacity: 0; transition: var(--transition); }
.partner-card:hover::before { opacity: 1; }

.partner-icon-wrapper { width: 70px; height: 70px; border-radius: var(--border-radius-sm); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 25px; position: relative; z-index: 1; transition: var(--transition); }
.partner-card:hover .partner-icon-wrapper { transform: scale(1.1) rotate(5deg); }
.partner-title { font-family: var(--font-secondary); font-size: 1.25rem; color: var(--primary); margin-bottom: 20px; font-weight: 700; position: relative; z-index: 1; line-height: 1.4; }
.partner-purpose { color: var(--gray); font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--gray-light); position: relative; z-index: 1; font-style: italic; }
.partner-tiers { display: flex; flex-direction: column; gap: 10px; position: relative; z-index: 1; flex: 1; }

.partner-tier { padding: 12px 15px; border-radius: var(--border-radius-sm); font-size: 0.9rem; line-height: 1.5; border-left: 4px solid; transition: var(--transition); }
.partner-tier:hover { transform: translateX(8px); }
.partner-tier-domestic { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left-color: var(--success); color: #155724; }
.partner-tier-regional { background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border-left-color: var(--warning); color: #856404; }
.partner-tier-international { background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%); border-left-color: var(--info); color: #004085; }
.partner-tier-label { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 8px; margin-bottom: 6px; opacity: 0.9; }
.partner-tier-label i { font-size: 1rem; }

@media (max-width: 991px) { .section { padding: 60px 0; } .partner-card { padding: 25px 20px; } }
@media (max-width: 768px) { .partner-icon-wrapper { width: 55px; height: 55px; font-size: 1.5rem; } .partner-title { font-size: 1.1rem; } }
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

function get_column_class($columns) {
    $map = [1 => 'col-12', 2 => 'col-md-6', 3 => 'col-md-6 col-lg-4', 4 => 'col-md-6 col-lg-3'];
    return $map[$columns] ?? 'col-md-6 col-lg-4';
}

function get_gap_class($gap) {
    $map = ['sm' => 'g-3', 'md' => 'g-4', 'lg' => 'g-5'];
    return $map[$gap] ?? 'g-4';
}

/**
 * Parse le contenu HTML Word - chaque <li> devient une carte
 * Structure: <li><span><strong>Titre:</strong></span><p>Purpose:...</p><p>Domestic:...</p><p>Regional:... International:...</p></li>
 */
function parse_partner_cards($html_content) {
    $cards = [];
    if (empty($html_content)) return $cards;
    
    // Décoder et nettoyer
    $html = html_entity_decode($html_content, ENT_QUOTES, 'UTF-8');
    $html = preg_replace('/<o:[^>]+>.*?<\/o:[^>]+>/s', '', $html);
    $html = preg_replace('/<v:[^>]+>.*?<\/v:[^>]+>/s', '', $html);
    $html = preg_replace('/<w:[^>]+>.*?<\/w:[^>]+>/s', '', $html);
    $html = preg_replace('/<m:[^>]+>.*?<\/m:[^>]+>/s', '', $html);
    $html = preg_replace('/<!\[CDATA\[.*?\]\]>/s', '', $html);
    $html = preg_replace('/<!\[if[^>]*>.*?<!\[endif\]>/s', '', $html);
    $html = str_replace(['&ndash;', '&mdash;', '&nbsp;', '&amp;', '&lt;', '&gt;', '&rsquo;', '&ldquo;', '&rdquo;'], ['-', '-', ' ', '&', '<', '>', "'", '"', '"'], $html);
    
    // Extraire chaque <li> complet
    preg_match_all('/<li[^>]*>(.*?)<\/li>/s', $html, $matches);
    if (empty($matches[1])) return $cards;
    
    foreach ($matches[1] as $li_html) {
        $card = [
            'title' => '',
            'purpose' => '',
            'domestic' => '',
            'regional' => '',
            'international' => '',
            'icon' => 'building'
        ];
        
        // === EXTRAIRE LE TITRE ===
        // Pattern: <span style="font-size: 14pt;"><strong>Titre:</strong></span>
        if (preg_match('/<span[^>]*>\s*<strong>([^<]+):<\/strong>\s*<\/span>/i', $li_html, $match)) {
            $card['title'] = trim($match[1]);
        } elseif (preg_match('/<strong>([^<]+):<\/strong>/i', $li_html, $match)) {
            $card['title'] = trim($match[1]);
        }
        
        $card['title'] = preg_replace('/\s+/', ' ', $card['title']);
        $card['icon'] = get_partner_icon($card['title']);
        
        // === EXTRAIRE LES PARAGRAPHES ===
        preg_match_all('/<p[^>]*>(.*?)<\/p>/s', $li_html, $p_matches);
        
        foreach ($p_matches[1] as $p_content) {
            // Nettoyer le contenu du <p>
            $text = preg_replace('/<span[^>]*>(.*?)<\/span>/s', '$1', $p_content);
            $text = strip_tags($text);
            $text = trim($text);
            
            if (empty($text)) continue;
            if (stripos($text, 'Conclusion') === 0 && strlen($text) < 15) continue;
            
            // === IDENTIFIER LE TYPE ===
            // Purpose: ...
            if (preg_match('/^Purpose\s*:\s*(.+)$/i', $text, $m)) {
                $card['purpose'] = trim($m[1]);
            }
            // Domestic: ...
            elseif (preg_match('/^Domestic\s*:\s*(.+)$/i', $text, $m)) {
                $card['domestic'] = trim($m[1]);
            }
            // Regional: ... (peut contenir International: ...)
            elseif (preg_match('/^Regional\s*:\s*(.+)$/i', $text, $m)) {
                $regional_text = trim($m[1]);
                
                // Chercher International: dans la même ligne
                if (preg_match('/^(.+?)\s+International\s*:\s*(.+)$/i', $regional_text, $intl_match)) {
                    $card['regional'] = trim($intl_match[1]);
                    $card['international'] = trim($intl_match[2]);
                } else {
                    $card['regional'] = $regional_text;
                }
            }
            // International: ... (ligne séparée)
            elseif (preg_match('/^International\s*:\s*(.+)$/i', $text, $m)) {
                if (empty($card['international'])) {
                    $card['international'] = trim($m[1]);
                }
            }
        }
        
        if (!empty($card['title'])) {
            $cards[] = $card;
        }
    }
    
    return $cards;
}

/**
 * Retourne l'icône selon le titre
 */
function get_partner_icon($title) {
    $title_lower = strtolower($title);
    if (strpos($title_lower, 'research') !== false || strpos($title_lower, 'scientific') !== false) return 'microscope';
    if (strpos($title_lower, 'clinical') !== false || strpos($title_lower, 'healthcare') !== false) return 'hospital';
    if (strpos($title_lower, 'regulatory') !== false || strpos($title_lower, 'market') !== false) return 'shield-check';
    if (strpos($title_lower, 'manufacturing') !== false || strpos($title_lower, 'quality') !== false) return 'gear';
    if (strpos($title_lower, 'safety') !== false || strpos($title_lower, 'preclinical') !== false) return 'shield-plus';
    if (strpos($title_lower, 'financial') !== false || strpos($title_lower, 'development') !== false) return 'cash-coin';
    return 'building';
}
?>

<!-- ============================================
     SECTIONS DYNAMIQUES
     ============================================ -->
<?php if (!empty($sections)): ?>
    <?php foreach ($sections as $section): 
        $type = $section['type_section'] ?? 'texte';
        $options = json_decode($section['options_json'] ?? '', true) ?: [];
        $content = fix_content_images($section['contenu_texte'] ?? '');
        $custom_class = htmlspecialchars($section['custom_class'] ?? '');
        
        switch($type):
            
            // ============================================
            // HERO
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
                        <?php if (!empty($content)): ?>
                            <div class="tinymce-content text-white mt-4"><?php echo $content; ?></div>
                        <?php endif; ?>
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // LISTE_CARD - Chaque <li> = une carte partenaire
            // ============================================
            case 'liste_card':
                $cards = parse_partner_cards($section['contenu_texte'] ?? '');
                
                if (empty($cards)) {
                    // Fallback: afficher contenu brut
                    echo '<section class="section ' . $custom_class . '">';
                    echo '<div class="section-container">';
                    echo '<div class="tinymce-content">' . $content . '</div>';
                    echo '</div>';
                    echo '</section>';
                    break;
                }
                
                $columns = (int) ($options['columns'] ?? 3);
                $col_class = get_column_class($columns);
                $gap_class = get_gap_class($options['gap'] ?? 'lg');
                
                $icon_colors = [
                    'microscope' => '#28a745',
                    'hospital' => '#dc3545',
                    'shield-check' => '#fd7e14',
                    'gear' => '#6c757d',
                    'shield-plus' => '#17a2b8',
                    'cash-coin' => '#ffc107',
                    'building' => '#0f4c3a'
                ];
    ?>
                <section class="section partner-grid-section <?php echo $custom_class; ?>">
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
                        
                        <div class="row <?php echo $gap_class; ?> animate-container">
                            <?php foreach ($cards as $index => $card): 
                                $icon = $card['icon'];
                                $icon_color = $icon_colors[$icon] ?? '#0f4c3a';
                            ?>
                            <div class="<?php echo $col_class; ?> animate-item" style="--delay: <?php echo $index * 0.1; ?>s">
                                <div class="partner-card h-100">
                                    <div class="partner-icon-wrapper" style="background: <?php echo $icon_color; ?>20; color: <?php echo $icon_color; ?>;">
                                        <i class="bi bi-<?php echo $icon; ?>"></i>
                                    </div>
                                    
                                    <h3 class="partner-title"><?php echo htmlspecialchars($card['title']); ?></h3>
                                    
                                    <?php if (!empty($card['purpose'])): ?>
                                        <p class="partner-purpose">
                                            <i class="bi bi-bullseye me-2" style="color: var(--accent);"></i>
                                            <?php echo htmlspecialchars($card['purpose']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="partner-tiers">
                                        <?php if (!empty($card['domestic'])): ?>
                                            <div class="partner-tier partner-tier-domestic">
                                                <span class="partner-tier-label">
                                                    <i class="bi bi-geo-alt-fill"></i> Domestic
                                                </span>
                                                <div><?php echo htmlspecialchars($card['domestic']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($card['regional'])): ?>
                                            <div class="partner-tier partner-tier-regional">
                                                <span class="partner-tier-label">
                                                    <i class="bi bi-globe-africa"></i> Regional
                                                </span>
                                                <div><?php echo htmlspecialchars($card['regional']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($card['international'])): ?>
                                            <div class="partner-tier partner-tier-international">
                                                <span class="partner-tier-label">
                                                    <i class="bi bi-globe"></i> International
                                                </span>
                                                <div><?php echo htmlspecialchars($card['international']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                            <div class="text-center mt-5 animate-item" style="--delay: <?php echo count($cards) * 0.1; ?>s">
                                <a href="<?php echo htmlspecialchars($section['bouton_lien']); ?>" class="btn btn-primary btn-lg rounded-pill">
                                    <?php echo htmlspecialchars($section['bouton_texte']); ?> 
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // DEFAULT
            // ============================================
            default: 
    ?>
                <section class="section <?php echo $custom_class; ?>">
                    <div class="section-container">
                        <div class="tinymce-content"><?php echo $content; ?></div>
                    </div>
                </section>
    <?php
                break;

        endswitch;
    endforeach;
endif;
?>

<!-- ============================================
     JAVASCRIPT DYNAMIQUE
     ============================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Animation au scroll (Intersection Observer)
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Appliquer aux éléments animables
    document.querySelectorAll('.animate-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease var(--delay, 0s), transform 0.6s ease var(--delay, 0s)';
        observer.observe(el);
    });
    
    // Style pour l'animation visible
    const style = document.createElement('style');
    style.textContent = `.animate-item.is-visible { opacity: 1 !important; transform: translateY(0) !important; }`;
    document.head.appendChild(style);
    
    // Hover effects desktop
    if (window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.partner-card').forEach(card => {
            card.addEventListener('mouseenter', function() { this.style.zIndex = '10'; });
            card.addEventListener('mouseleave', function() { this.style.zIndex = '1'; });
        });
    }
    
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>