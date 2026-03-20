<?php 
include VIEWPATH.'includes/frontend/Header.php'; 
?>

<!-- ================= STYLES ================= -->
<style>
:root {
    --primary-teal: #1a8c78;
    --dark-teal: #146c5c;
    --light-bg: #f8faf9;
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
                                         onerror="this.src='<?= base_url('assets/images/news-placeholder.jpg') ?>'">
                                    
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
                                    
                                    <h4 class="fw-bold mb-3 name">
                                        <a href="<?= $article['url'] ?>" class="text-dark text-decoration-none stretched-link">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h4>
                                    
                                    <p class="text-muted details mb-3">
                                        <?= !empty($article['resume']) ? htmlspecialchars($article['resume']) : substr(strip_tags($article['content']), 0, 120) ?>...
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="article-meta">
                                            <i class="far fa-user"></i> <?= htmlspecialchars($article['author']) ?>
                                        </div>
                                        <div class="article-meta">
                                            <i class="far fa-eye"></i> <?= number_format($article['views']) ?> vues
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($article['tags'])): ?>
                                        <div class="mt-3">
                                            <?php foreach (array_slice($article['tags'], 0, 3) as $tag): ?>
                                                <span class="badge bg-light text-dark me-1">#<?= htmlspecialchars($tag) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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
                             onerror="this.src='<?= base_url('assets/images/news-placeholder.jpg') ?>'">
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
                
                <!-- Widget: Newsletter (optionnel) -->
                <div class="sidebar-widget bg-teal text-white">
                    <h5 class="widget-title text-white border-white"><i class="fas fa-envelope me-2"></i>Newsletter</h5>
                    <p class="mb-3">Restez informé de nos dernières actualités.</p>
                    <form action="<?= base_url('newsletter/subscribe') ?>" method="POST">
                        <div class="mb-2">
                            <input type="email" name="email" class="form-control" placeholder="Votre email" required>
                        </div>
                        <button type="submit" class="btn btn-light w-100 text-teal fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>S'abonner
                        </button>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</section>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>