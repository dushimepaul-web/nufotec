<?php include VIEWPATH.'includes/frontend/Header.php'; ?>


<section class="hero">
    <div class="hero-content">

        <?php 
            $site_logo = $this->Model->get_setting('site_logo');
            $site_name = $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
        ?>

        <!-- LOGO -->
        <?php if (!empty($site_logo)): ?>
            <div class="hero-logo">
                <img src="<?= base_url('attachments/Configurations/' . $site_logo) ?>" 
                     alt="<?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?>">
            </div>
        <?php endif; ?>

        <h1>Bienvenue sur <?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?></h1>

        <p>Votre plateforme de commerce en ligne rapide, fiable et sécurisée</p>
        
        <div class="hero-buttons">
            <a href="<?= base_url('Medicins') ?>" class="btn-primary">Être consulté</a>
            <a href="<?= base_url('Products') ?>" class="btn-secondary">Acheter les produits</a>
        </div>

    </div>
</section>

<style type="text/css">
    /* HERO SECTION */
.hero {
    height: 90vh;
    background: linear-gradient(
        rgba(15, 76, 58, 0.85),
        rgba(10, 51, 38, 0.9)
    ),
    url('<?= base_url('attachments/Configurations/' . $site_logo) ?>') center/cover no-repeat;

    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
    color: var(--light);
}

/* CONTENU */
.hero-content {
    max-width: 700px;
    animation: fadeUp 1s ease-in-out;
}

/* LOGO */
.hero-logo img {
    width: 110px;
    margin-bottom: 20px;
    filter: drop-shadow(var(--shadow-glow));
    transition: var(--transition);
}

.hero-logo img:hover {
    transform: scale(1.1);
}

/* TITRE */
.hero-content h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 15px;
}

/* TEXTE */
.hero-content p {
    font-size: 1.2rem;
    color: var(--gray-light);
    margin-bottom: 30px;
}

/* BOUTONS */
.hero-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

/* BTN PRIMARY */
.btn-primary {
    background: var(--accent);
    color: var(--dark);
    padding: 12px 28px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.btn-primary:hover {
    background: var(--accent-hover);
    transform: translateY(-3px);
    box-shadow: var(--shadow-xl);
}

/* BTN SECONDARY */
.btn-secondary {
    border: 2px solid var(--accent);
    color: var(--accent);
    padding: 12px 28px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.btn-secondary:hover {
    background: var(--accent);
    color: var(--dark);
    box-shadow: var(--shadow-glow);
}

/* ANIMATION */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .hero {
        height: auto;
        padding: 60px 20px;
    }

    .hero-content h1 {
        font-size: 2rem;
    }

    .hero-logo img {
        width: 90px;
    }
}
</style>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- SECTIONS DE CONTENU DYNAMIQUES - ERGONOMIE OPTIMISÉE -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php
// Fonctions utilitaires pour le traitement du contenu
if (!function_exists('fix_image_path')) {
    function fix_image_path($image_path) {
        if (empty($image_path)) return null;
        $image_path = trim($image_path);
        if (filter_var($image_path, FILTER_VALIDATE_URL)) return $image_path;
        $image_path = preg_replace('/\.\.\//', '', $image_path);
        $image_path = ltrim($image_path, '/');
        return base_url($image_path);
    }
}

if (!function_exists('clean_html_content')) {
    function clean_html_content($content) {
        if (empty($content)) return '';
        $allowed_tags = '<p><br><strong><b><em><i><u><strike><span><div><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img><td><thead><tbody><tfoot><tr><tr><th><caption><pre><code><hr><figure><figcaption>';
        $content = strip_tags($content, $allowed_tags);
        $content = preg_replace('/(<[^>]+)\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '$1', $content);
        $content = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $content);
        return $content;
    }
}

if (!function_exists('get_placeholder_image')) {
    function get_placeholder_image() {
        return base_url('assets/images/placeholder-section.jpg');
    }
}
?>

<section class="content-sections" id="content">
    <div class="container-fluid px-3 px-lg-4">
        
        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $index => $section): ?>
                
                <?php 
                $image_left = empty($section['image_droite']) || $section['image_droite'] == 0; 
                $has_image = !empty($section['image_url']);
                $image_url = $has_image ? fix_image_path($section['image_url']) : null;
                
                $raw_content = $section['contenu_texte'] ?? '';
                $safe_content = clean_html_content($raw_content);
                
                $content_id = 'content-' . $index;
                $text_length = strlen(strip_tags($safe_content));
                $is_long_content = $text_length > 400;
                $section_class = $index % 2 === 0 ? 'bg-white' : 'bg-light';
                ?>

                <article class="content-block <?= $section_class ?>" data-aos="fade-up">
                    <div class="container py-5 py-lg-6">
                        
                        <!-- Type: Texte Seul -->
                        <?php if ($section['type_section'] == 'texte'): ?>
                            <div class="row justify-content-center">
                                <div class="col-lg-10 col-xl-8 text-center">
                                    <?php if (!empty($section['titre_section'])): ?>
                                        <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($section['sous_titre'])): ?>
                                        <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                                    <?php endif; ?>
                                    
                                    <div class="content-wrapper">
                                        <div class="content-text tinymce-content <?= $is_long_content ? 'content-collapsed' : '' ?>" id="<?= $content_id ?>">
                                            <?= $safe_content ?>
                                        </div>
                                        
                                        <?php if ($is_long_content): ?>
                                        <button class="read-more-btn" onclick="toggleContent('<?= $content_id ?>', this)" aria-expanded="false">
                                            <span>Lire la suite</span>
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                        <a href="<?= base_url(ltrim($section['bouton_lien'], '/')) ?>" class="btn btn-primary-custom mt-4">
                                            <?= htmlspecialchars($section['bouton_texte']) ?> 
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <!-- Type: Image + Texte -->
                        <?php elseif ($section['type_section'] == 'image_texte'): ?>
                            <div class="row align-items-center g-4 g-lg-5">
                                
                                <?php if ($has_image && $image_left): ?>
                                    <!-- Image Gauche -->
                                    <div class="col-lg-6" data-aos="fade-right">
                                        <figure class="image-wrapper">
                                            <div class="image-container">
                                                <img src="<?= $image_url ?>" 
                                                     alt="<?= htmlspecialchars($section['titre_section'] ?? 'Image') ?>" 
                                                     loading="lazy"
                                                     onerror="this.src='<?= get_placeholder_image() ?>'">
                                                
                                                <?php if (!empty($section['options_json'])): 
                                                    $options = json_decode($section['options_json'], true);
                                                    if (!empty($options['badge'])): ?>
                                                    <div class="floating-badge">
                                                        <span class="number"><?= htmlspecialchars($options['badge']['number']) ?></span>
                                                        <span class="text"><?= htmlspecialchars($options['badge']['text']) ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </figure>
                                    </div>
                                    
                                    <div class="col-lg-6" data-aos="fade-left">
                                        <div class="content-wrapper">
                                            <?php if (!empty($section['titre_section'])): ?>
                                                <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($section['sous_titre'])): ?>
                                                <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                                            <?php endif; ?>
                                            
                                            <div class="content-text tinymce-content <?= $is_long_content ? 'content-collapsed' : '' ?>" id="<?= $content_id ?>">
                                                <?= $safe_content ?>
                                            </div>
                                            
                                            <?php if ($is_long_content): ?>
                                            <button class="read-more-btn" onclick="toggleContent('<?= $content_id ?>', this)" aria-expanded="false">
                                                <span>Lire la suite</span>
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                                <a href="<?= base_url(ltrim($section['bouton_lien'], '/')) ?>" class="btn btn-primary-custom mt-3">
                                                    <?= htmlspecialchars($section['bouton_texte']) ?> 
                                                    <i class="bi bi-arrow-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                <?php elseif ($has_image && !$image_left): ?>
                                    <!-- Image Droite -->
                                    <div class="col-lg-6 order-2 order-lg-1" data-aos="fade-right">
                                        <div class="content-wrapper">
                                            <?php if (!empty($section['titre_section'])): ?>
                                                <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($section['sous_titre'])): ?>
                                                <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                                            <?php endif; ?>
                                            
                                            <div class="content-text tinymce-content <?= $is_long_content ? 'content-collapsed' : '' ?>" id="<?= $content_id ?>">
                                                <?= $safe_content ?>
                                            </div>
                                            
                                            <?php if ($is_long_content): ?>
                                            <button class="read-more-btn" onclick="toggleContent('<?= $content_id ?>', this)" aria-expanded="false">
                                                <span>Lire la suite</span>
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                                <a href="<?= base_url(ltrim($section['bouton_lien'], '/')) ?>" class="btn btn-primary-custom mt-3">
                                                    <?= htmlspecialchars($section['bouton_texte']) ?> 
                                                    <i class="bi bi-arrow-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-left">
                                        <figure class="image-wrapper">
                                            <div class="image-container">
                                                <img src="<?= $image_url ?>" 
                                                     alt="<?= htmlspecialchars($section['titre_section'] ?? 'Image') ?>" 
                                                     loading="lazy"
                                                     onerror="this.src='<?= get_placeholder_image() ?>'">
                                                
                                                <?php if (!empty($section['options_json'])): 
                                                    $options = json_decode($section['options_json'], true);
                                                    if (!empty($options['badge'])): ?>
                                                    <div class="floating-badge">
                                                        <span class="number"><?= htmlspecialchars($options['badge']['number']) ?></span>
                                                        <span class="text"><?= htmlspecialchars($options['badge']['text']) ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </figure>
                                    </div>

                                <?php else: ?>
                                    <!-- Sans Image -->
                                    <div class="col-12 text-center">
                                        <?php if (!empty($section['titre_section'])): ?>
                                            <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($section['sous_titre'])): ?>
                                            <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                                        <?php endif; ?>
                                        
                                        <div class="content-wrapper max-width-800 mx-auto">
                                            <div class="content-text tinymce-content <?= $is_long_content ? 'content-collapsed' : '' ?>" id="<?= $content_id ?>">
                                                <?= $safe_content ?>
                                            </div>
                                            
                                            <?php if ($is_long_content): ?>
                                            <button class="read-more-btn" onclick="toggleContent('<?= $content_id ?>', this)" aria-expanded="false">
                                                <span>Lire la suite</span>
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                            <a href="<?= base_url(ltrim($section['bouton_lien'], '/')) ?>" class="btn btn-primary-custom mt-4">
                                                <?= htmlspecialchars($section['bouton_texte']) ?> 
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <!-- Type: Liste -->
                        <?php elseif ($section['type_section'] == 'liste'): ?>
                            <div class="row">
                                <div class="col-12 text-center mb-5">
                                    <?php if (!empty($section['titre_section'])): ?>
                                        <span class="section-tag"><?= htmlspecialchars($section['titre_section']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($section['sous_titre'])): ?>
                                        <h2 class="section-title"><?= htmlspecialchars($section['sous_titre']) ?></h2>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($raw_content)): ?>
                                    <?php
                                    $list_items = [];
                                    if (strpos($raw_content, '<li>') !== false) {
                                        preg_match_all('/<li>(.*?)<\/li>/', $raw_content, $matches);
                                        $list_items = $matches[1] ?? [];
                                    } else {
                                        $lines = array_filter(array_map('trim', explode("\n", strip_tags($raw_content))));
                                        $list_items = $lines;
                                    }
                                    ?>
                                    
                                    <div class="row g-4">
                                        <?php foreach (array_slice($list_items, 0, 6) as $i => $item): 
                                            $item = trim(strip_tags($item));
                                            if (empty($item)) continue;
                                            
                                            $parts = explode(':', $item, 2);
                                            $title = trim($parts[0]);
                                            $desc = trim($parts[1] ?? '');
                                        ?>
                                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                                            <div class="feature-card h-100">
                                                <div class="feature-icon">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                </div>
                                                <div class="feature-content">
                                                    <h5><?= htmlspecialchars($title) ?></h5>
                                                    <?php if ($desc): ?>
                                                        <p><?= htmlspecialchars($desc) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>











        <!-- Pages Enfants -->
        <?php if (!empty($children)): ?>
        <div class="children-section">
            <div class="container py-5 py-lg-6">
                <div class="text-center mb-5" data-aos="fade-up">
                    <span class="section-tag light">Explorer</span>
                    <h2 class="section-title text-white">Pages Connexes</h2>
                </div>
                
                <div class="row g-4">
                    <?php foreach ($children as $i => $child): ?>
                    <div class="col-6 col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                        <a href="<?= base_url('Home/PageDetail/' . $child['id_page'] . '_' . $child['slug']) ?>" class="child-card">
                            <div class="child-card-inner">
                                <?php if (!empty($child['icone_menu'])): ?>
                                <div class="child-icon">
                                    <i class="bi bi-<?= htmlspecialchars($child['icone_menu']) ?>"></i>
                                </div>
                                <?php endif; ?>
                                <h5><?= htmlspecialchars($child['titre_page']) ?></h5>
                                <?php if (!empty($child['meta_description'])): ?>
                                <p><?= htmlspecialchars(substr($child['meta_description'], 0, 80)) ?>...</p>
                                <?php endif; ?>
                                <span class="child-link">Découvrir <i class="bi bi-arrow-right"></i></span>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($sections) && empty($children)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Aucun contenu disponible</p>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<script>
function toggleContent(contentId, btn) {
    const content = document.getElementById(contentId);
    const span = btn.querySelector('span');
    const icon = btn.querySelector('i');
    const isExpanded = content.classList.contains('content-expanded');
    
    if (!isExpanded) {
        content.classList.remove('content-collapsed');
        content.classList.add('content-expanded');
        span.textContent = 'Réduire';
        icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
        btn.setAttribute('aria-expanded', 'true');
        btn.classList.add('active');
    } else {
        content.classList.add('content-collapsed');
        content.classList.remove('content-expanded');
        span.textContent = 'Lire la suite';
        icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
        btn.setAttribute('aria-expanded', 'false');
        btn.classList.remove('active');
        content.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Intersection Observer pour animations au scroll
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    
    document.querySelectorAll('.content-block, .feature-card, .child-card').forEach(el => {
        observer.observe(el);
    });
});
</script>

<style>
/* Styles Sections Contenu */
.content-sections {
    overflow: hidden;
}

.content-block {
    position: relative;
    transition: var(--transition);
}

.content-block.bg-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.py-6 {
    padding-top: 5rem;
    padding-bottom: 5rem;
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: rgba(15, 76, 58, 0.1);
    color: var(--primary);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 50px;
    margin-bottom: 16px;
}

.section-tag.light {
    background: rgba(255,255,255,0.15);
    color: white;
}

.section-tag::before {
    content: '';
    width: 6px;
    height: 6px;
    background: var(--accent);
    border-radius: 50%;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 24px;
    line-height: 1.3;
}

.section-title.text-white {
    color: white;
}

/* Content Text */
.content-wrapper {
    position: relative;
}

.content-text {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #555;
    transition: max-height 0.4s ease;
}

.content-text.content-collapsed {
    max-height: 180px;
    overflow: hidden;
    position: relative;
}

.content-text.content-collapsed::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: linear-gradient(transparent, rgba(255,255,255,0.95));
    pointer-events: none;
}

.content-text.content-expanded {
    max-height: none;
}

.read-more-btn {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
    padding: 10px 24px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    transition: var(--transition);
}

.read-more-btn:hover,
.read-more-btn.active {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

.max-width-800 {
    max-width: 800px;
}

/* Image Wrapper */
.image-wrapper {
    position: relative;
    margin: 0;
}

.image-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    transform: perspective(1000px) rotateY(-3deg);
    transition: var(--transition);
}

.image-container:hover {
    transform: perspective(1000px) rotateY(0deg) scale(1.02);
}

.image-container img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.image-container:hover img {
    transform: scale(1.05);
}

.floating-badge {
    position: absolute;
    bottom: 24px;
    right: -16px;
    background: var(--primary);
    color: white;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: var(--shadow-xl);
    text-align: center;
    animation: float 3s ease-in-out infinite;
    z-index: 10;
    border: 2px solid var(--accent);
}

.floating-badge .number {
    display: block;
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    color: var(--accent);
}

.floating-badge .text {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

/* Buttons */
.btn-primary-custom {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    box-shadow: 0 4px 15px rgba(15, 76, 58, 0.3);
    text-decoration: none;
}

.btn-primary-custom:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(15, 76, 58, 0.4);
    color: white;
}

/* Feature Cards */
.feature-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 24px;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
    border: 1px solid rgba(0,0,0,0.05);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
    border-color: rgba(15, 76, 58, 0.1);
}

.feature-icon {
    width: 48px;
    height: 48px;
    background: rgba(15, 76, 58, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 22px;
    flex-shrink: 0;
    transition: var(--transition);
}

.feature-card:hover .feature-icon {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

.feature-content h5 {
    color: var(--primary);
    margin-bottom: 6px;
    font-weight: 700;
    font-size: 1.1rem;
}

.feature-content p {
    color: var(--gray);
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.5;
}

/* Children Section */
.children-section {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    position: relative;
    overflow: hidden;
}

.children-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    background: rgba(212, 175, 55, 0.1);
    border-radius: 50%;
    pointer-events: none;
}

.child-card {
    display: block;
    text-decoration: none;
    height: 100%;
}

.child-card-inner {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 24px;
    height: 100%;
    transition: var(--transition);
    border: 1px solid rgba(255,255,255,0.2);
}

.child-card:hover .child-card-inner {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    background: white;
}

.child-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    transition: var(--transition);
}

.child-icon i {
    font-size: 26px;
    color: var(--primary-dark);
}

.child-card:hover .child-icon {
    transform: scale(1.1) rotate(5deg);
}

.child-card h5 {
    color: var(--primary);
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.child-card p {
    color: var(--gray);
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.child-link {
    color: var(--primary);
    font-weight: 600;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: var(--transition);
}

.child-card:hover .child-link {
    color: var(--accent-hover);
    gap: 10px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: var(--gray);
}

.empty-state i {
    font-size: 48px;
    color: var(--gray-light);
    margin-bottom: 16px;
    display: block;
}

/* TinyMCE Content Styles */
.tinymce-content h1 {
    font-size: 1.8rem;
    color: var(--primary);
    margin: 24px 0 16px;
    font-weight: 700;
}

.tinymce-content h2 {
    font-size: 1.5rem;
    color: var(--primary);
    margin: 20px 0 12px;
    font-weight: 600;
}

.tinymce-content h3 {
    font-size: 1.25rem;
    color: var(--primary);
    margin: 16px 0 10px;
    font-weight: 600;
}

.tinymce-content p {
    margin-bottom: 16px;
}

.tinymce-content ul {
    padding-left: 20px;
    margin: 16px 0;
}

.tinymce-content ul li {
    margin-bottom: 8px;
    position: relative;
}

.tinymce-content blockquote {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid var(--primary);
    padding: 20px 24px;
    margin: 20px 0;
    border-radius: 0 12px 12px 0;
    font-style: italic;
    color: var(--primary-dark);
}

.tinymce-content img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 20px 0;
}

.tinymce-content table {
    width: 100%;
    margin: 20px 0;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.tinymce-content table th {
    background: var(--primary);
    color: white;
    padding: 14px;
    font-weight: 600;
    text-align: left;
}

.tinymce-content table td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--gray-light);
}

.tinymce-content table tr:hover {
    background: #f8f9fa;
}

/* Responsive */
@media (max-width: 992px) {
    .py-6 { padding-top: 4rem; padding-bottom: 4rem; }
    .image-container { transform: none; }
    .image-container:hover { transform: scale(1.02); }
    .floating-badge {
        right: 12px;
        bottom: 12px;
        padding: 12px 16px;
    }
    .floating-badge .number { font-size: 24px; }
}

@media (max-width: 768px) {
    .py-6 { padding-top: 3rem; padding-bottom: 3rem; }
    .content-text { font-size: 1rem; }
    .feature-card { 
        flex-direction: column; 
        text-align: center; 
        padding: 20px;
    }
    .feature-icon { margin: 0 auto 12px; }
    .child-card-inner { padding: 20px; }
    .child-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 12px;
    }
    .child-icon i { font-size: 22px; }
    .image-container img { height: 280px; }
}

@media (max-width: 576px) {
    .section-title { font-size: 1.5rem; }
    .read-more-btn { width: 100%; justify-content: center; }
    .btn-primary-custom { width: 100%; justify-content: center; }
    .feature-card { padding: 16px; }
    .child-card-inner { padding: 16px; }
    .child-card h5 { font-size: 1rem; }
    .child-card p { font-size: 0.85rem; -webkit-line-clamp: 2; }
}
</style>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- CHIFFRES CLÉS - STATS INTERACTIVES -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php if (!empty($chiffres)): ?>
<section class="stats-section" id="stats">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-tag light"><?= t('our_achievements') ?></span>
            <h2 class="section-title text-white"><?= t('key_figures') ?></h2>
        </div>
        
        <div class="row g-4">
            <?php foreach ($chiffres as $index => $chiffre): ?>
                <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-<?= htmlspecialchars($chiffre['icone']) ?>"></i>
                        </div>
                        
                        <div class="stat-number">
                            <?php 
                            $valeur_numerique = preg_replace('/[^0-9]/', '', $chiffre['valeur']);
                            $suffixe = preg_replace('/[0-9]/', '', $chiffre['valeur']);
                            ?>
                            <span class="counter" 
                                  data-target="<?= $valeur_numerique ?>" 
                                  data-suffix="<?= htmlspecialchars($suffixe) ?>">
                                0
                            </span>
                        </div>
                        
                        <h5 class="stat-label"><?= htmlspecialchars($chiffre['etiquette']) ?></h5>
                        
                        <?php if (!empty($chiffre['description'])): ?>
                            <p class="stat-desc"><?= htmlspecialchars($chiffre['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.counter');
    
    const animateCounter = (el) => {
        const target = parseInt(el.getAttribute('data-target'));
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 2000;
        let startTime = null;
        
        const animate = (currentTime) => {
            if (!startTime) startTime = currentTime;
            const progress = Math.min((currentTime - startTime) / duration, 1);
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(easeOutQuart * target);
            
            el.innerText = current.toLocaleString() + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                el.innerText = target.toLocaleString() + suffix;
            }
        };
        
        requestAnimationFrame(animate);
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target.querySelector('.counter');
                if (counter) animateCounter(counter);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    document.querySelectorAll('.stat-card').forEach(card => observer.observe(card));
});
</script>

<style>
.stats-section {
    padding: 80px 0;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    position: relative;
    overflow: hidden;
}

.stats-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}

.stat-card {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px;
    padding: 32px 24px;
    text-align: center;
    transition: var(--transition);
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), transparent);
    transform: scaleX(0);
    transition: transform 0.6s ease;
}

.stat-card:hover {
    transform: translateY(-8px);
    background: rgba(255,255,255,0.1);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-icon {
    width: 64px;
    height: 64px;
    background: rgba(212, 175, 55, 0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 28px;
    color: var(--accent);
    transition: var(--transition);
}

.stat-card:hover .stat-icon {
    background: var(--accent);
    color: var(--primary-dark);
    transform: rotateY(360deg);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    line-height: 1;
    margin-bottom: 12px;
}

.stat-label {
    color: rgba(255,255,255,0.9);
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-desc {
    color: rgba(255,255,255,0.6);
    font-size: 0.85rem;
    margin: 0;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .stats-section { padding: 60px 0; }
    .stat-card { padding: 24px 16px; }
    .stat-number { font-size: 2rem; }
    .stat-icon { width: 56px; height: 56px; font-size: 24px; }
    .stat-label { font-size: 0.9rem; }
}
</style>




<!-- ═══════════════════════════════════════════════════════ -->
<!-- APPELS À L'ACTION - GRILLE DYNAMIQUE -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php if (!empty($appels_action)): ?>
<section class="cta-section" id="cta">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-tag"><?= t('act_now') ?></span>
            <h2 class="section-title"><?= t('our_cta') ?></h2>
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
                
                // Nettoyer le lien : enlever le préfixe de langue s'il existe
                $lien = ltrim($cta['bouton_lien'], '/');
                // Enlever le préfixe de langue (fr/, en/, sw/) s'il est présent
                $lien = preg_replace('/^(fr|en|sw)\//', '', $lien);
            ?>
            
            <article class="cta-card" <?= $bg_style ?> data-aos="zoom-in">
                <div class="cta-overlay">
                    <?php if (!empty($cta['type_public']) && $cta['type_public'] !== 'all'): ?>
                        <span class="cta-badge"><?= htmlspecialchars($cta['type_public']) ?></span>
                    <?php endif; ?>
                    
                    <div class="cta-content">
                        <h3><?= htmlspecialchars($cta['titre']) ?></h3>
                        <p><?= htmlspecialchars($cta['sous_titre']) ?></p>
                    </div>
                    
                    <a href="<?= base_url($lien) ?>" class="cta-btn">
                        <?= htmlspecialchars($cta['bouton_texte']) ?>
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                
                <div class="cta-shine"></div>
            </article>
            
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.cta-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.cta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}

.cta-card {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    min-height: 380px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    display: flex;
    flex-direction: column;
    transition: var(--transition);
    cursor: pointer;
}

.cta-card[style*="background-image"] {
    background-size: cover;
    background-position: center;
}

.cta-overlay {
    flex: 1;
    padding: 32px;
    background: linear-gradient(to bottom, rgba(15, 76, 58, 0.85), rgba(10, 51, 38, 0.95));
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    z-index: 2;
    transition: var(--transition);
}

.cta-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
}

.cta-card:hover .cta-overlay {
    background: linear-gradient(to bottom, rgba(15, 76, 58, 0.9), rgba(10, 51, 38, 0.98));
}

.cta-badge {
    align-self: flex-start;
    background: var(--accent);
    color: var(--primary-dark);
    font-size: 11px;
    font-weight: 800;
    padding: 6px 14px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 16px;
}

.cta-content h3 {
    color: white;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 12px;
    line-height: 1.3;
}

.cta-content p {
    color: rgba(255,255,255,0.85);
    font-size: 0.95rem;
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
    padding: 12px 24px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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
}

.cta-btn:hover::before {
    left: 0;
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

.cta-card:hover .cta-shine {
    left: 150%;
}

@media (max-width: 768px) {
    .cta-section { padding: 60px 0; }
    .cta-grid { grid-template-columns: 1fr; gap: 20px; }
    .cta-card { min-height: 320px; }
    .cta-overlay { padding:
            24px; }
    .cta-content h3 { font-size: 1.2rem; }
}
</style>






<?php 
        // Vérifier si la variable $products existe et contient des données
        if (!isset($products)) {
            // Si les produits ne sont pas encore chargés, on les récupère
            $products = $this->Model->get_products_translated($this->current_lang);
        }
        ?>
<?php include VIEWPATH.'sections/Products_Section.php'; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- NEWSLETTER - FORMULAIRE INTERACTIF -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="newsletter-section" id="newsletter">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            
            <!-- Illustration -->
            <div class="col-lg-5 d-none d-lg-block" data-aos="fade-right">
                <div class="newsletter-illustration">
                    <div class="envelope-container">
                        <div class="envelope-flap"></div>
                        <div class="envelope-body"></div>
                        <div class="letter">
                            <div class="letter-line"></div>
                            <div class="letter-line short"></div>
                            <div class="letter-line shorter"></div>
                        </div>
                        <div class="floating-circle"></div>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="col-lg-7" data-aos="fade-left">
                <div class="newsletter-content">
                    <span class="section-tag"><?= t('stay_informed') ?></span>
                    <h2 class="section-title"><?= t('subscribe_newsletter') ?></h2>
                    <p class="newsletter-desc">
                        <?= t('newsletter_desc') ?>
                    </p>

                    <form id="subscribeForm" method="POST" action="<?= base_url('Home/Abonner'); ?>" class="newsletter-form" novalidate>
                        
                        <!-- Choix du type -->
                        <div class="subscription-toggle mb-4">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="sub_type" id="type_email" value="email" checked>
                                <label class="btn btn-toggle" for="type_email">
                                    <i class="bi bi-envelope"></i> <?= t('email') ?>
                                </label>

                                <input type="radio" class="btn-check" name="sub_type" id="type_phone" value="phone">
                                <label class="btn btn-toggle" for="type_phone">
                                    <i class="bi bi-phone"></i> <?= t('phone_number') ?>
                                </label>
                            </div>
                        </div>

                        <!-- Champ Email -->
                        <div class="form-group email-group" id="emailGroup">
                            <div class="input-wrapper">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" 
                                       name="email" 
                                       id="emailInput"
                                       class="form-control form-control-lg" 
                                       placeholder="<?= t('your_email') ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-circle"></i> <?= t('invalid_email') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Champ Téléphone (caché par défaut) -->
                        <div class="form-group phone-group d-none" id="phoneGroup">
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="input-wrapper">
                                        <input type="text" 
                                               id="paysSearch" 
                                               class="form-control form-control-lg" 
                                               placeholder="<?= t('country') ?>" 
                                               autocomplete="off"
                                               disabled>
                                        <input type="hidden" name="pays_code" id="paysCode">
                                        <input type="hidden" name="indicatif_complet" id="indicatifComplet">
                                        
                                        <!-- Dropdown pays -->
                                        <div id="paysDropdown" class="pays-dropdown">
                                            <?php if (!empty($pays)): ?>
                                                <?php foreach ($pays as $p): ?>
                                                    <?php 
                                                    $indicatif = str_replace('+', '', $p['ITU_T_Telephone_Code'] ?? '');
                                                    $searchTerms = strtolower(($p['pays'] ?? '') . ' ' . ($p['ISO_3166_1_2_Letter_Code'] ?? '') . ' ' . $indicatif);
                                                    ?>
                                                    <div class="pays-option" 
                                                         data-value="<?= htmlspecialchars($p['ISO_3166_1_2_Letter_Code'] ?? '') ?>" 
                                                         data-indicatif="<?= htmlspecialchars($indicatif) ?>"
                                                         data-pays="<?= htmlspecialchars($p['pays'] ?? '') ?>"
                                                         data-search="<?= htmlspecialchars($searchTerms) ?>">
                                                        <span class="pays-nom"><?= htmlspecialchars($p['pays'] ?? '') ?></span>
                                                        <span class="pays-indicatif">+<?= htmlspecialchars($indicatif) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="input-wrapper">
                                        <span class="input-indicatif" id="displayIndicatif">+</span>
                                        <input type="tel" 
                                               name="telephone" 
                                               id="phoneInput"
                                               class="form-control form-control-lg" 
                                               placeholder="<?= t('phone_number_placeholder') ?>"
                                               disabled>
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle"></i> <?= t('invalid_phone') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton Submit -->
                        <button type="submit" id="submitBtn" class="btn btn-submit btn-lg w-100 mt-4">
                            <span class="btn-text"><?= t('subscribe_now') ?></span>
                            <span class="btn-loading d-none">
                                <i class="bi bi-arrow-repeat spin"></i> <?= t('sending') ?>
                            </span>
                            <span class="btn-success d-none">
                                <i class="bi bi-check-lg"></i> <?= t('subscribed') ?>
                            </span>
                        </button>

                        <p class="privacy-note mt-3">
                            <i class="bi bi-shield-check"></i> <?= t('privacy_note') ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    'use strict';
    
    const form = document.getElementById('subscribeForm');
    const emailGroup = document.getElementById('emailGroup');
    const phoneGroup = document.getElementById('phoneGroup');
    const emailInput = document.getElementById('emailInput');
    const phoneInput = document.getElementById('phoneInput');
    const paysSearch = document.getElementById('paysSearch');
    const paysDropdown = document.getElementById('paysDropdown');
    const displayIndicatif = document.getElementById('displayIndicatif');
    const submitBtn = document.getElementById('submitBtn');
    
    // Toggle Email/Téléphone
    document.querySelectorAll('input[name="sub_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'email') {
                phoneGroup.classList.add('d-none');
                emailGroup.classList.remove('d-none');
                emailInput.disabled = false;
                phoneInput.disabled = true;
                paysSearch.disabled = true;
                emailInput.focus();
            } else {
                emailGroup.classList.add('d-none');
                phoneGroup.classList.remove('d-none');
                emailInput.disabled = true;
                phoneInput.disabled = false;
                paysSearch.disabled = false;
                paysSearch.focus();
            }
            
            // Reset validation
            [emailInput, phoneInput, paysSearch].forEach(input => {
                input?.classList.remove('is-invalid');
            });
        });
    });
    
    // Recherche pays
    let searchTimeout;
    paysSearch?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.toLowerCase().trim();
        
        searchTimeout = setTimeout(() => {
            const options = paysDropdown.querySelectorAll('.pays-option');
            let hasResults = false;
            
            options.forEach(option => {
                const searchTerms = option.getAttribute('data-search') || '';
                if (searchTerms.includes(query)) {
                    option.style.display = 'flex';
                    hasResults = true;
                } else {
                    option.style.display = 'none';
                }
            });
            
            paysDropdown.style.display = hasResults ? 'block' : 'none';
        }, 100);
    });
    
    // Focus sur pays montre tous
    paysSearch?.addEventListener('focus', function() {
        paysDropdown.querySelectorAll('.pays-option').forEach(opt => {
            opt.style.display = 'flex';
        });
        paysDropdown.style.display = 'block';
    });
    
    // Sélection pays
    window.selectPays = function(element) {
        const code = element.getAttribute('data-value');
        const indicatif = element.getAttribute('data-indicatif');
        const pays = element.getAttribute('data-pays');
        
        document.getElementById('paysCode').value = code;
        document.getElementById('indicatifComplet').value = indicatif;
        paysSearch.value = pays.substring(0, 20);
        displayIndicatif.textContent = '+' + indicatif;
        
        paysSearch.classList.remove('is-invalid');
        paysDropdown.style.display = 'none';
        phoneInput.focus();
    };
    
    // Clic extérieur ferme dropdown
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#paysSearch') && !e.target.closest('#paysDropdown')) {
            paysDropdown.style.display = 'none';
        }
    });
    
    // Clic sur option pays
    paysDropdown?.querySelectorAll('.pays-option').forEach(option => {
        option.addEventListener('click', function() {
            selectPays(this);
        });
    });
    
    // Validation et soumission
    form?.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        
        const isEmail = document.getElementById('type_email').checked;
        
        if (isEmail) {
            const email = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(email)) {
                emailInput.classList.add('is-invalid');
                shakeElement(emailInput);
                isValid = false;
            }
        } else {
            const paysCode = document.getElementById('paysCode').value;
            const phone = phoneInput.value.trim();
            
            if (!paysCode) {
                paysSearch.classList.add('is-invalid');
                shakeElement(paysSearch);
                isValid = false;
            }
            
            if (!phone || phone.length < 8) {
                phoneInput.classList.add('is-invalid');
                shakeElement(phoneInput);
                isValid = false;
            }
        }
        
        if (isValid) {
            // Animation loading
            submitBtn.querySelector('.btn-text').classList.add('d-none');
            submitBtn.querySelector('.btn-loading').classList.remove('d-none');
            submitBtn.disabled = true;
            
            // Simulation envoi (remplacer par vrai envoi)
            setTimeout(() => {
                submitBtn.querySelector('.btn-loading').classList.add('d-none');
                submitBtn.querySelector('.btn-success').classList.remove('d-none');
                submitBtn.style.background = 'linear-gradient(135deg, #00b894 0%, #00cec9 100%)';
                
                setTimeout(() => {
                    form.submit();
                }, 1000);
            }, 1500);
        }
    });
    
    // Retirer erreur à la saisie
    [emailInput, phoneInput, paysSearch].forEach(input => {
        input?.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
    
    function shakeElement(el) {
        el.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            el.style.animation = '';
        }, 500);
    }
})();
</script>

<style>
.newsletter-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #e2e8f0 100%);
    position: relative;
    overflow: hidden;
}

.newsletter-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(15, 76, 58, 0.05);
    border-radius: 50%;
}

.newsletter-illustration {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.envelope-container {
    position: relative;
    width: 280px;
    height: 220px;
    transform: perspective(1000px) rotateY(-5deg);
    animation: floatEnvelope 4s ease-in-out infinite;
}

@keyframes floatEnvelope {
    0%, 100% { transform: perspective(1000px) rotateY(-5deg) translateY(0); }
    50% { transform: perspective(1000px) rotateY(-5deg) translateY(-15px); }
}

.envelope-flap {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 110px;
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    border-radius: 16px 16px 0 0;
    z-index: 2;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.envelope-body {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 140px;
    background: linear-gradient(135deg, #2c5aa0 0%, #1e3d6f 100%);
    border-radius: 0 0 16px 16px;
    z-index: 1;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.letter {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    width: 75%;
    height: 160px;
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    border-radius: 8px;
    z-index: 3;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    animation: slideLetter 3s ease-in-out infinite;
}

@keyframes slideLetter {
    0%, 100% { transform: translateX(-50%) translateY(20px); }
    50% { transform: translateX(-50%) translateY(0); }
}

.letter-line {
    height: 6px;
    background: rgba(255,255,255,0.6);
    border-radius: 3px;
    margin-bottom: 10px;
    width: 80%;
}

.letter-line.short { width: 60%; }
.letter-line.shorter { width: 40%; }

.floating-circle {
    position: absolute;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    border-radius: 50%;
    top: -60px;
    right: -10px;
    z-index: 0;
    opacity: 0.9;
    animation: pulseCircle 2s ease-in-out infinite;
}

@keyframes pulseCircle {
    0%, 100% { transform: scale(1); opacity: 0.9; }
    50% { transform: scale(1.1); opacity: 0.7; }
}

.newsletter-content {
    position: relative;
    z-index: 1;
}

.newsletter-desc {
    font-size: 1.1rem;
    color: #5a6c7d;
    margin-bottom: 32px;
    line-height: 1.7;
}

.newsletter-desc strong {
    color: var(--primary);
}

/* Form Styles */
.subscription-toggle .btn-group {
    background: rgba(255,255,255,0.6);
    padding: 4px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.1);
}

.btn-toggle {
    border: none;
    background: transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 12px 24px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-toggle:hover {
    color: var(--primary);
}

.btn-check:checked + .btn-toggle {
    background: white;
    color: var(--primary);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
    font-size: 20px;
    z-index: 10;
}

.form-control-lg {
    height: 56px;
    padding: 12px 20px;
    font-size: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    transition: all 0.3s ease;
}

.form-control-lg:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(15, 76, 58, 0.1);
    outline: none;
}

.input-icon + .form-control-lg {
    padding-left: 48px;
}

.input-indicatif {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
    font-weight: 700;
    font-size: 16px;
    z-index: 10;
}

.input-indicatif + .form-control-lg {
    padding-left: 60px;
}

/* Pays Dropdown */
.pays-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    border: 1px solid #e2e8f0;
}

.pays-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f1f3f5;
}

.pays-option:hover {
    background: #f8f9fa;
}

.pays-option:last-child {
    border-bottom: none;
}

.pays-nom {
    font-weight: 500;
    color: #2d3748;
}

.pays-indicatif {
    color: var(--primary);
    font-weight: 600;
    font-size: 14px;
}

/* Validation */
.is-invalid {
    border-color: #e53e3e !important;
    background-color: #fff5f5 !important;
}

.is-invalid:focus {
    box-shadow: 0 0 0 4px rgba(229, 62, 62, 0.1) !important;
}

.invalid-feedback {
    display: none;
    color: #e53e3e;
    font-size: 13px;
    margin-top: 6px;
    font-weight: 500;
}

.is-invalid ~ .invalid-feedback {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Submit Button */
.btn-submit {
    height: 56px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(15, 76, 58, 0.3);
}

.btn-submit:disabled {
    opacity: 0.8;
    cursor: not-allowed;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Privacy Note */
.privacy-note {
    font-size: 13px;
    color: #718096;
    text-align: center;
    margin-bottom: 0;
}

.privacy-note i {
    color: var(--primary);
}

/* Shake Animation */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* Responsive */
@media (max-width: 992px) {
    .newsletter-section { padding: 60px 0; }
    .envelope-container { width: 240px; height: 190px; }
    .letter { height: 140px; }
}

@media (max-width: 576px) {
    .newsletter-section { padding: 40px 0; }
    .newsletter-desc { font-size: 1rem; }
    .btn-toggle { padding: 10px 16px; font-size: 14px; }
    .form-control-lg { height: 52px; font-size: 16px; }
    .btn-submit { height: 52px; }
}
</style>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>