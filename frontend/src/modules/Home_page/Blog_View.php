<?php 
include VIEWPATH.'includes/frontend/Header.php'; 
include VIEWPATH.'includes/alerts.php';
?>

<!-- ================= STYLES ================= -->
<style>
:root {
    --primary-teal: #1a8c78;
    --dark-teal: #146c5c;
    --light-bg: #f8faf9;
    --primary: #0f4c3a;
    --primary-dark: #0a3328;
    --accent: #d4af37;
}

.text-teal { color: var(--primary-teal) !important; }
.bg-teal { background: var(--primary-teal) !important; }


 /* ===== HERO SECTION ===== */
    .hero-section {
        position: relative;
        height: 300px;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .hero-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .hero-bg-image img {
        object-fit: cover;
        object-position: center;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 76, 58, 0.85);
        z-index: 2;
    }

    .hero-content-wrapper {
        position: relative;
        z-index: 3;
        width: 100%;
        padding: 20px 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1.2;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.3rem;
        font-weight: 500;
        color: var(--accent);
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .hero-text {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 20px;
        opacity: 0.95;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    @media (max-width: 991px) {
        .hero-section { height: 280px; }
        .hero-title { font-size: 1.8rem; }
        .hero-subtitle { font-size: 1.1rem; }
    }

    @media (max-width: 768px) {
        .hero-section { height: 250px; min-height: 220px; }
        .hero-title { font-size: 1.5rem; }
        .hero-subtitle { font-size: 1rem; }
        .hero-text { font-size: 0.9rem; }
    }

    @media (max-width: 576px) {
        .hero-section { height: 220px; min-height: 200px; }
        .hero-title { font-size: 1.3rem; }
        .hero-subtitle { font-size: 0.95rem; }
    }


/* ================= ARTICLES ================= */
.article-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.4s ease;
    height: 100%;
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,.08);
    margin-bottom: 20px;
}
.article-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,.12);
}
.article-img {
    height: 220px;
    object-fit: cover;
    width: 100%;
    transition: transform 0.5s ease;
}
.article-card:hover .article-img {
    transform: scale(1.05);
}
.article-title-link {
    transition: color 0.3s ease;
}
.article-card:hover .article-title-link {
    color: var(--primary-teal) !important;
}
.article-category {
    background: var(--primary-teal);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 2;
}
.article-meta {
    font-size: 0.9rem;
    color: #666;
}
.article-meta i {
    color: var(--primary-teal);
    margin-right: 5px;
}
.article-read-time {
    background: var(--light-bg);
    color: var(--primary-teal);
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
}

/* Lien "Lire plus" dans la carte */
.article-readmore {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--primary-teal);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-top: 12px;
}
.article-readmore i {
    transition: transform 0.3s ease;
}
.article-readmore:hover {
    color: var(--dark-teal);
    gap: 12px;
}
.article-readmore:hover i {
    transform: translateX(4px);
}

/* ================= SIDEBAR ================= */
.sidebar-widget {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,.08);
}
.widget-title {
    color: var(--primary-teal);
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--light-bg);
}

/* ================= CATEGORIES ================= */
.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.category-list li {
    margin-bottom: 10px;
}
.category-list a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border-radius: 8px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
}
.category-list a:hover {
    background: var(--light-bg);
    color: var(--primary-teal);
}
.category-list .badge {
    background: var(--primary-teal);
    color: white;
}

/* ================= POPULAR ARTICLES ================= */
.popular-item {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}
.popular-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}
.popular-thumb {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}
.popular-content h6 {
    font-size: 0.95rem;
    margin-bottom: 5px;
    line-height: 1.4;
}
.popular-content a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}
.popular-content a:hover {
    color: var(--primary-teal);
}
.popular-meta {
    font-size: 0.8rem;
    color: #888;
}

/* ================= PAGINATION ================= */
.pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin-top: 40px;
    gap: 5px;
}
.pagination .page-item .page-link {
    border: 1px solid var(--primary-teal);
    color: var(--primary-teal);
    border-radius: 8px;
    padding: 8px 16px;
    text-decoration: none;
    transition: all 0.3s ease;
}
.pagination .page-item.active .page-link {
    background-color: var(--primary-teal);
    border-color: var(--primary-teal);
    color: white;
}
.pagination .page-item .page-link:hover {
    background-color: var(--primary-teal);
    color: white;
}

/* ================= SEARCH ================= */
.search-box {
    position: relative;
}
.search-box input {
    padding-left: 45px;
    border-radius: 25px;
    border: 2px solid #e0e0e0;
    transition: border-color 0.3s;
}
.search-box input:focus {
    border-color: var(--primary-teal);
    box-shadow: none;
}
.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
}

/* ================= FEATURED BADGE ================= */
.featured-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #ffd700, #ffaa00);
    color: #333;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
}

/* ================= EMPTY STATE ================= */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

/* ================= NEWSLETTER ================= */
.newsletter-widget {
    background: linear-gradient(160deg, #0f4c3a 0%, #146c5c 100%);
    color: white;
    position: relative;
    overflow: hidden;
}
.newsletter-widget .widget-title {
    color: white;
    border-bottom-color: rgba(255,255,255,.2);
}
.newsletter-widget::before {
    content: '';
    position: absolute;
    width: 160px;
    height: 160px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
    top: -60px;
    right: -60px;
}
.newsletter-widget::after {
    content: '';
    position: absolute;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(212,175,55,.12);
    bottom: -40px;
    left: -40px;
}
.newsletter-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--accent);
    color: var(--primary-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 16px;
}
.newsletter-widget .text-muted { color: rgba(255,255,255,.85) !important; }
.newsletter-input {
    position: relative;
}
.newsletter-input i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
    z-index: 2;
}
.newsletter-input .form-control {
    padding-left: 40px;
    border-radius: 25px;
    border: none;
}
.newsletter-input .form-control:focus {
    box-shadow: 0 0 0 3px rgba(212,175,55,.35);
}
.newsletter-msg {
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.88rem;
    margin-bottom: 12px;
}
.newsletter-msg.success { background: rgba(39,174,96,.15); color: #eafff1; border: 1px solid rgba(39,174,96,.4); }
.newsletter-msg.error { background: rgba(231,76,60,.15); color: #ffe9e7; border: 1px solid rgba(231,76,60,.4); }
.newsletter-msg.warning { background: rgba(241,196,15,.15); color: #fff8e1; border: 1px solid rgba(241,196,15,.4); }
.btn-teal {
    background: var(--accent);
    color: var(--primary-dark) !important;
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 700;
    transition: all 0.3s ease;
}
.btn-teal:hover {
    background: #e8c84e;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
}

/* ================= SOCIAL FOLLOW ================= */
.social-follow-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.social-follow-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    background: var(--light-bg);
    text-decoration: none;
    color: #333;
    border: 1px solid #eef1f0;
    transition: all 0.3s ease;
}
.social-follow-item:hover {
    background: white;
    transform: translateX(4px);
    box-shadow: 0 5px 15px rgba(0,0,0,.1);
    border-color: var(--primary-teal);
}
.social-follow-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.social-follow-item.facebook .social-follow-icon { background: #3b5998; }
.social-follow-item.twitter .social-follow-icon { background: #1da1f2; }
.social-follow-item.linkedin .social-follow-icon { background: #0077b5; }
.social-follow-item.instagram .social-follow-icon { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
.social-follow-item.youtube .social-follow-icon { background: #ff0000; }
.social-follow-item.whatsapp .social-follow-icon { background: #25d366; }
.social-follow-item.tiktok .social-follow-icon { background: #010101; }
.social-follow-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}
.social-follow-name {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}
.social-follow-count {
    color: var(--primary-teal);
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 4px;
}
.social-follow-arrow {
    color: #bbb;
    font-size: 0.9rem;
    transition: transform 0.3s ease;
}
.social-follow-item:hover .social-follow-arrow {
    color: var(--primary-teal);
    transform: translate(2px, -2px);
}

/* ================= RESPONSIVE ================= */
@media (max-width: 768px) {
    .article-img { height: 180px; }
    .page-header { padding: 100px 0 60px; }
}
</style>


<!-- ===== HERO SECTION ===== -->
<?php if (isset($hero_section) && !empty($hero_section)): ?>
<div class="hero-section position-relative overflow-hidden">
    <?php if (!empty($hero_section['image_url'])): ?>
    <div class="hero-bg-image">
        <img src="<?php echo base_url($hero_section['image_url']); ?>" 
             alt="<?php echo isset($hero_section['titre_section']) ? $hero_section['titre_section'] : 'FAQ'; ?>"
             class="w-100 h-100 object-fit-cover">
    </div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <?php if (!empty($hero_section['titre_section'])): ?>
                        <h1 class="hero-title animate__animated animate__fadeInUp">
                            <?php echo $hero_section['titre_section']; ?>
                        </h1>
                    <?php else: ?>
                        <h1 class="hero-title animate__animated animate__fadeInUp">FAQ</h1>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['sous_titre'])): ?>
                        <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                            <?php echo $hero_section['sous_titre']; ?>
                        </h2>
                    <?php else: ?>
                        <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                            Questions Fréquemment Posées
                        </h2>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['contenu_texte'])): ?>
                        <p class="hero-text animate__animated animate__fadeInUp animate__delay-2s">
                            <?php echo $hero_section['contenu_texte']; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php else: ?>
<!-- Hero par défaut si pas de hero_section -->
<div class="hero-section position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="hero-content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <h1 class="hero-title animate__animated animate__fadeInUp">News & Actualités</h1>
                    <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                        L'actualité d'African Green Farmers
                    </h2>
                    <p class="hero-text animate__animated animate__fadeInUp animate__delay-2s">
                        Suivez nos dernières actualités, événements et innovations dans le secteur agro-industriel.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php endif; ?>

<!-- ================= CONTENU PRINCIPAL ================= -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- COLONNE PRINCIPALE: ARTICLES -->
            <div class="col-lg-8">
                
                <?php if (!empty($articles)): ?>
                    <div class="row g-4">
                        <?php foreach($articles as $article): ?>
                        <div class="col-md-6 article-item">
                            <div class="article-card position-relative">
                                <div class="position-relative overflow-hidden">
                                    <img src="<?= $article['image'] ?>" 
                                         class="article-img" 
                                         alt="<?= htmlspecialchars($article['title']) ?>"
                                         loading="lazy"
                                         onerror="this.src='<?= base_url('assets/backend/images/defaut-logo.jpeg') ?>'">
                                    
                                    <span class="article-category"><?= htmlspecialchars($article['category']) ?></span>
                                    
                                    <?php if ($article['featured']): ?>
                                        <span class="featured-badge"><i class="fas fa-star me-1"></i>À la une</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="article-meta">
                                            <i class="far fa-calendar-alt"></i> <?= $article['date_formatted'] ?>
                                        </div>
                                        <span class="article-read-time">
                                            <i class="far fa-clock me-1"></i><?= $article['read_time'] ?> min
                                        </span>
                                    </div>
                                    
                                    <h4 class="fw-bold mb-3 name" style="font-size: 1.15rem; line-height: 1.4;">
                                        <a href="<?= $article['url'] ?>" class="text-dark text-decoration-none article-title-link">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h4>
                                    
                                    <p class="text-muted details mb-3">
                                        <?= !empty($article['resume']) ? htmlspecialchars($article['resume']) : substr(strip_tags($article['content']), 0, 120) ?>...
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="article-meta">
                                            <i class="far fa-user"></i> <?= htmlspecialchars($article['author']) ?>
                                        </div>
                                        <div class="article-meta">
                                            <i class="far fa-eye"></i> <?= number_format($article['views']) ?> vues
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($article['tags'])): ?>
                                        <div class="mb-3">
                                            <?php foreach (array_slice($article['tags'], 0, 3) as $tag): ?>
                                                <span class="badge bg-light text-dark me-1">#<?= htmlspecialchars($tag) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="<?= $article['url'] ?>" class="article-readmore">
                                        Lire la suite <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (!empty($pagination)): ?>
                        <div class="mt-4">
                            <?= $pagination ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state bg-white rounded-3 shadow-sm">
                        <i class="far fa-newspaper"></i>
                        <h4>Aucun article disponible</h4>
                        <p class="text-muted">
                            <?php if (!empty($search_query)): ?>
                                Aucun résultat trouvé pour votre recherche. Essayez avec d'autres termes.
                            <?php else: ?>
                                Revenez bientôt pour découvrir nos nouveaux contenus.
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($search_query)): ?>
                            <a href="<?= base_url('blog') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Voir tous les articles
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- SIDEBAR -->
            <div class="col-lg-4">
                
                <!-- Widget: Recherche -->
                <div class="sidebar-widget">
                    <h5 class="widget-title"><i class="fas fa-search me-2"></i>Rechercher</h5>
                    <form action="<?= base_url('blog/recherche') ?>" method="GET">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="q" class="form-control" placeholder="Rechercher un article..." 
                                   value="<?= !empty($search_query) ? htmlspecialchars($search_query) : '' ?>">
                        </div>
                    </form>
                </div>
                
                <!-- Widget: Catégories -->
                <?php if (!empty($categories)): ?>
                <div class="sidebar-widget">
                    <h5 class="widget-title"><i class="fas fa-folder me-2"></i>Catégories</h5>
                    <ul class="category-list">
                        <?php foreach($categories as $cat): ?>
                        <li>
                            <a href="<?= base_url('blog/categorie/' . urlencode($cat['categorie'])) ?>">
                                <span><?= htmlspecialchars($cat['categorie']) ?></span>
                                <span class="badge"><?= $cat['count'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Widget: Articles Populaires -->
                <?php if (!empty($popular_articles)): ?>
                <div class="sidebar-widget">
                    <h5 class="widget-title"><i class="fas fa-fire me-2"></i>Populaires</h5>
                    <?php foreach($popular_articles as $pop): ?>
                    <div class="popular-item">
                        <img src="<?= $pop['image'] ?>" alt="" class="popular-thumb" loading="lazy"
                             onerror="this.src='<?= base_url('assets/backend/images/defaut-logo.jpeg') ?>'">
                        <div class="popular-content">
                            <h6>
                                <a href="<?= $pop['url'] ?>">
                                    <?= htmlspecialchars(strlen($pop['title']) > 50 ? substr($pop['title'], 0, 50) . '...' : $pop['title']) ?>
                                </a>
                            </h6>
                            <div class="popular-meta">
                                <i class="far fa-eye me-1"></i><?= number_format($pop['views']) ?> vues
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Widget: Newsletter (AJAX) -->
                <div class="sidebar-widget newsletter-widget">
                    <div class="newsletter-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <h5 class="widget-title"><i class="fas fa-paper-plane me-2"></i>Newsletter</h5>
                    <p class="text-muted mb-3">Restez informé de nos dernières actualités et innovations. Inscrivez-vous gratuitement !</p>
                    <form id="newsletterForm" action="<?= base_url('newsletter/subscribe') ?>" method="POST" novalidate>
                        <input type="hidden" name="sub_type" value="email">
                        <div class="mb-2">
                            <div class="newsletter-input">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" id="newsletterEmail" class="form-control" placeholder="Votre adresse email" required>
                            </div>
                        </div>
                        <div id="newsletterMsg" class="newsletter-msg d-none"></div>
                        <button type="submit" id="newsletterBtn" class="btn btn-teal w-100 fw-bold text-white">
                            <i class="fas fa-paper-plane me-2"></i>S'abonner
                        </button>
                    </form>
                </div>

                <!-- Widget: Suivez-nous -->
                <?php if (!empty($social_links)): ?>
                <div class="sidebar-widget">
                    <h5 class="widget-title"><i class="fas fa-users me-2"></i>Suivez-nous</h5>
                    <div class="social-follow-list">
                        <?php foreach ($social_links as $social): 
                            $icon = !empty($social['icon_class']) && $social['icon_class'] !== 'bi'
                                ? $social['icon_class'] . ' ' . $social['icon_name']
                                : 'bi bi-' . $social['icon_name'];
                            $brand = strtolower($social['platform']);
                        ?>
                        <a href="<?= htmlspecialchars($social['url']) ?>" 
                           class="social-follow-item <?= htmlspecialchars($brand) ?>"
                           target="_blank" rel="noopener noreferrer"
                           title="<?= htmlspecialchars($social['label']) ?>">
                            <span class="social-follow-icon"><i class="<?= $icon ?>"></i></span>
                            <span class="social-follow-info">
                                <span class="social-follow-name"><?= htmlspecialchars($social['label']) ?></span>
                                <?php if (!empty($social['followers'])): ?>
                                <span class="social-follow-count">
                                    <i class="bi bi-people-fill"></i> <?= htmlspecialchars($social['followers']) ?> abonnés
                                </span>
                                <?php endif; ?>
                            </span>
                            <i class="bi bi-arrow-up-right social-follow-arrow"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</section>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>


<script>
(function() {
    'use strict';
    var form = document.getElementById('newsletterForm');
    if (!form) return;

    var CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var email = document.getElementById('newsletterEmail').value.trim();
        var msg = document.getElementById('newsletterMsg');
        var btn = document.getElementById('newsletterBtn');

        if (!email) {
            showMsg(msg, 'Veuillez saisir votre adresse email.', 'error');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Inscription...';

        var body = new URLSearchParams();
        body.append('email', email);
        body.append('sub_type', 'email');
        body.append(CSRF_NAME, CSRF_HASH);

        fetch('<?= base_url('newsletter/subscribe') ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_HASH,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(function(resp) {
            if (!resp.ok) {
                // Le bootstrap CSRF peut renvoyer 403 (token expiré) : recharger la page
                window.location.reload();
                return null;
            }
            return resp.json();
        })
        .then(function(data) {
            if (!data) return;

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>S\'abonner';

            if (data.success) {
                showMsg(msg, data.message, 'success');
                document.getElementById('newsletterEmail').value = '';
            } else {
                showMsg(msg, data.message, data.status === 'warning' ? 'warning' : 'error');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>S\'abonner';
            showMsg(msg, 'Une erreur est survenue. Veuillez réessayer.', 'error');
        });
    });

    function showMsg(el, text, type) {
        el.className = 'newsletter-msg ' + type;
        el.innerHTML = text;
        el.classList.remove('d-none');
        setTimeout(function() { el.classList.add('d-none'); }, 6000);
    }
})();
</script>