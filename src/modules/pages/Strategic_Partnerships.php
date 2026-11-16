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
   CARD STYLES - Pour tous les types de cartes
   ============================================ */
.card-item {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 25px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    height: 100%;
    opacity: 0;
    transform: translateY(30px);
}

.card-item.visible {
    opacity: 1;
    transform: translateY(0);
}

.card-item:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.card-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 20px;
    color: var(--primary);
    transition: var(--transition);
}

.card-item:hover .card-icon {
    background: var(--accent);
    color: white;
    transform: rotate(360deg);
}

.card-title {
    font-family: var(--font-secondary);
    font-size: 1.2rem;
    color: var(--primary);
    margin-bottom: 15px;
    font-weight: 700;
}

.card-text {
    color: var(--gray);
    line-height: 1.6;
    font-size: 0.95rem;
}

/* ============================================
   TABLE STYLES
   ============================================ */
.table-custom {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: var(--border-radius-md);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.table-custom thead {
    background: var(--primary);
    color: white;
}

.table-custom th {
    padding: 15px 20px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.table-custom td {
    padding: 15px 20px;
    border-bottom: 1px solid var(--gray-light);
}

.table-custom tbody tr:hover {
    background: var(--gray-soft);
}

/* ============================================
   LIST STYLES
   ============================================ */
.list-custom {
    list-style: none;
    padding: 0;
}

.list-custom li {
    padding: 12px 0;
    border-bottom: 1px dashed var(--gray-light);
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.list-custom li i {
    color: var(--accent);
    font-size: 1.2rem;
    margin-top: 2px;
}

.list-custom li strong {
    color: var(--primary);
    min-width: 200px;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 991px) { 
    .section { padding: 60px 0; } 
}

@media (max-width: 768px) {
    .card-icon { width: 50px; height: 50px; font-size: 1.5rem; }
    .card-title { font-size: 1.1rem; }
}

@media (max-width: 576px) {
    .section { padding: 40px 0; }
    .list-custom li { flex-direction: column; gap: 5px; }
    .list-custom li strong { min-width: auto; }
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

function get_column_class($columns) {
    $map = [1 => 'col-12', 2 => 'col-md-6', 3 => 'col-md-6 col-lg-4', 4 => 'col-md-6 col-lg-3'];
    return $map[$columns] ?? 'col-md-6 col-lg-4';
}

function get_gap_class($gap) {
    $map = ['sm' => 'g-3', 'md' => 'g-4', 'lg' => 'g-5'];
    return $map[$gap] ?? 'g-4';
}

/**
 * Données prédéfinies pour les partenaires (à remplacer par vos données)
 */
function get_partners_data() {
    return [
        [
            'icon' => 'microscope',
            'title' => 'Research & Scientific Validation',
            'purpose' => 'Validate plant-based compounds for chronic diseases including cancer, diabetes, and cardiovascular conditions.',
            'domestic' => 'University of Zambia – preclinical research, biomarker identification, and translational studies.',
            'regional' => 'University of Cape Town – pharmacology studies and chronic disease modeling.',
            'international' => 'Harvard Medical School, University of Oxford, Karolinska Institutet – mechanism validation, translational research.'
        ],
        [
            'icon' => 'hospital',
            'title' => 'Clinical Trials & Healthcare Networks',
            'purpose' => 'Conduct human studies to demonstrate safety and efficacy.',
            'domestic' => 'Lusaka hospitals and NCD clinics – patient recruitment and clinical monitoring.',
            'regional' => 'Regional referral hospitals – coordination and management of clinical studies.',
            'international' => 'MD Anderson Cancer Center, Mayo Clinic – oncology and metabolic trial collaboration.'
        ],
        [
            'icon' => 'shield-check',
            'title' => 'Regulatory & Market Authorization',
            'purpose' => 'Ensure compliance with domestic and international standards.',
            'domestic' => 'Zambia Medicines Regulatory Authority – product registration and pharmacovigilance guidance.',
            'regional' => 'COMESA regulatory authorities – regional alignment and compliance support.',
            'international' => 'World Health Organization – guidance on traditional medicine integration.'
        ],
        [
            'icon' => 'gear-wide-connected',
            'title' => 'Manufacturing & Quality Assurance',
            'purpose' => 'Industrial-scale production under internationally recognized GMP standards.',
            'domestic' => 'Local GMP consultants – facility audits, process validation, and quality management.',
            'regional' => 'Regional certification firms – batch standardization and quality assurance.',
            'international' => 'SGS, Bureau Veritas, TÜV Rheinland – GMP certification, process validation.'
        ],
        [
            'icon' => 'shield-plus',
            'title' => 'Safety & Preclinical Testing',
            'purpose' => 'Confirm regulatory-grade safety of products before human trials.',
            'domestic' => 'Local preclinical labs – initial toxicity and safety screening.',
            'regional' => 'Regional GLP labs – organ toxicity and chronic exposure studies.',
            'international' => 'Charles River Laboratories, Evotec – GLP-compliant toxicology, pharmacokinetics.'
        ],
        [
            'icon' => 'cash-coin',
            'title' => 'Financial & Development Partners',
            'purpose' => 'Provide funding, ESG guidance, and impact measurement.',
            'domestic' => 'Bank of Zambia, local DFIs – seed funding and ESG advisory.',
            'regional' => 'African Development Bank – capital mobilization, ESG frameworks.',
            'international' => 'International Finance Corporation, Bill & Melinda Gates Foundation – impact funding.'
        ]
    ];
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
            // TYPE 2: LISTE_CARD - Cartes avec icônes
            // ============================================
            case 'liste_card':
                $partners = get_partners_data();
                $columns = $options['columns'] ?? 3;
                $bg_color = $options['bg_color'] ?? '#ffffff';
                $gap = $options['gap'] ?? 'lg';
                $col_class = get_column_class($columns);
                $gap_class = get_gap_class($gap);
    ?>
                <section class="section <?php echo $custom_class; ?>" style="background: <?php echo $bg_color; ?>;">
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
                        
                        <div class="row <?php echo $gap_class; ?>">
                            <?php foreach ($partners as $index => $partner): ?>
                            <div class="<?php echo $col_class; ?>">
                                <div class="card-item h-100">
                                    <div class="card-icon">
                                        <i class="bi bi-<?php echo $partner['icon']; ?>"></i>
                                    </div>
                                    <h3 class="card-title"><?php echo $partner['title']; ?></h3>
                                    <p class="card-text mb-3"><i class="bi bi-bullseye me-2" style="color: var(--accent);"></i> <?php echo $partner['purpose']; ?></p>
                                    <div class="mt-3">
                                        <p class="mb-2"><strong class="text-success">Domestic:</strong> <?php echo $partner['domestic']; ?></p>
                                        <p class="mb-2"><strong class="text-warning">Regional:</strong> <?php echo $partner['regional']; ?></p>
                                        <p class="mb-0"><strong class="text-info">International:</strong> <?php echo $partner['international']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
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
                </section>
    <?php
                break;

            // ============================================
            // TYPE 3: GRILLE - Grille simple
            // ============================================
            case 'grille':
                $items = [];
                // Essayer d'extraire les éléments
                if (strpos($raw_content, '<li>') !== false) {
                    preg_match_all('/<li>(.*?)<\/li>/s', $raw_content, $matches);
                    $items = $matches[1] ?? [];
                } else {
                    $lines = explode("\n", strip_tags($raw_content));
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line) && strlen($line) > 10) {
                            $items[] = $line;
                        }
                    }
                }
                
                $columns = $options['columns'] ?? 2;
                $col_class = get_column_class($columns);
                $gap_class = get_gap_class($options['gap'] ?? 'lg');
    ?>
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
                        
                        <div class="row <?php echo $gap_class; ?>">
                            <?php foreach ($items as $index => $item): ?>
                            <div class="<?php echo $col_class; ?>">
                                <div class="card-item h-100">
                                    <div class="card-text">
                                        <?php echo fix_content_images($item); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // TYPE 4: TABLEAU - Tableau structuré
            // ============================================
            case 'tableau':
                $data = get_partners_data();
    ?>
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
                        
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Purpose</th>
                                        <th>Domestic</th>
                                        <th>Regional</th>
                                        <th>International</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row): ?>
                                    <tr>
                                        <td><strong><?php echo $row['title']; ?></strong></td>
                                        <td><?php echo $row['purpose']; ?></td>
                                        <td><?php echo $row['domestic']; ?></td>
                                        <td><?php echo $row['regional']; ?></td>
                                        <td><?php echo $row['international']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // TYPE 5: LISTE - Liste simple
            // ============================================
            case 'liste':
                $categories = get_partners_data();
                $columns = $options['columns'] ?? 2;
                $col_class = get_column_class($columns);
    ?>
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
                        
                        <div class="row g-4">
                            <?php foreach ($categories as $category): ?>
                            <div class="<?php echo $col_class; ?>">
                                <div class="card-item h-100">
                                    <h3 class="card-title">
                                        <i class="bi bi-<?php echo $category['icon']; ?> me-2" style="color: var(--accent);"></i>
                                        <?php echo $category['title']; ?>
                                    </h3>
                                    <ul class="list-custom">
                                        <li><i class="bi bi-bullseye"></i> <strong>Purpose:</strong> <?php echo $category['purpose']; ?></li>
                                        <li><i class="bi bi-geo-alt-fill text-success"></i> <strong>Domestic:</strong> <?php echo $category['domestic']; ?></li>
                                        <li><i class="bi bi-globe-africa text-warning"></i> <strong>Regional:</strong> <?php echo $category['regional']; ?></li>
                                        <li><i class="bi bi-globe text-info"></i> <strong>International:</strong> <?php echo $category['international']; ?></li>
                                    </ul>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                    </div>
                </section>
    <?php
                break;

            // ============================================
            // TYPE 6: HTML - HTML brut
            // ============================================
            case 'html':
    ?>
                <section class="section <?php echo $custom_class; ?>">
                    <div class="section-container">
                        <?php echo $content_with_fixed_images; ?>
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
    // Observer pour les animations au scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { 
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    });

    // Observer les éléments hero
    document.querySelectorAll('.page-hero-title, .page-hero-subtitle').forEach(el => {
        observer.observe(el);
    });

    // Observer les cartes avec délai progressif
    document.querySelectorAll('.card-item').forEach((el, index) => {
        el.style.transitionDelay = (index * 0.1) + 's';
        observer.observe(el);
    });

    // Observer les lignes de tableau
    document.querySelectorAll('.table-custom tbody tr').forEach((el, index) => {
        el.style.transition = 'all 0.3s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateX(0)';
        }, index * 100);
    });

    // Barre de progression du scroll
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

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>