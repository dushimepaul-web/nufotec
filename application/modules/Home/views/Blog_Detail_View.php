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

/* ================= ARTICLE HEADER ================= */
.article-header {
    padding: 100px 0 60px;
    background: linear-gradient(rgba(0,0,0,.7), rgba(0,0,0,.7)),
                var(--article-bg, url('https://images.unsplash.com/photo-1499750310107-5fef28a66643')) center/cover no-repeat;
    background-attachment: fixed;
    text-align: center;
    color: white;
    position: relative;
}

.article-meta-header {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
    margin-top: 20px;
    font-size: 0.95rem;
}

.article-meta-header span {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ================= ARTICLE CONTENT ================= */
.article-content-wrapper {
    background: white;
    border-radius: 20px;
    margin-top: -50px;
    position: relative;
    z-index: 10;
    box-shadow: 0 10px 40px rgba(0,0,0,.1);
}

.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #444;
}

.article-content p {
    margin-bottom: 1.5rem;
}

.article-content h2, 
.article-content h3 {
    color: var(--primary-teal);
    margin: 2rem 0 1rem;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    margin: 2rem 0;
}

.article-content blockquote {
    border-left: 4px solid var(--primary-teal);
    padding-left: 20px;
    margin: 2rem 0;
    font-style: italic;
    color: #666;
}

/* ================= TAGS ================= */
.article-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}

.tag {
    background: var(--light-bg);
    color: var(--primary-teal);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s;
}

.tag:hover {
    background: var(--primary-teal);
    color: white;
}

/* ================= AUTHOR BOX ================= */
.author-box {
    display: flex;
    gap: 20px;
    align-items: center;
    background: var(--light-bg);
    padding: 30px;
    border-radius: 15px;
    margin-top: 40px;
}

.author-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 3px 10px rgba(0,0,0,.1);
}

/* ================= NAVIGATION ================= */
.article-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding-top: 40px;
    border-top: 1px solid #eee;
}

.nav-article {
    flex: 1;
    max-width: 45%;
}

.nav-article.prev {
    text-align: left;
}

.nav-article.next {
    text-align: right;
}

.nav-label {
    display: block;
    color: var(--primary-teal);
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.nav-title {
    color: #333;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
}

.nav-title:hover {
    color: var(--primary-teal);
}

/* ================= RELATED ARTICLES ================= */
.related-section {
    margin-top: 60px;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.related-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,.08);
    transition: transform 0.3s;
    background: white;
}

.related-card:hover {
    transform: translateY(-5px);
}

.related-img {
    height: 180px;
    object-fit: cover;
    width: 100%;
}

.related-content {
    padding: 20px;
}

/* ================= SIDEBAR ================= */
.sidebar-sticky {
    position: sticky;
    top: 100px;
}

.share-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.share-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: transform 0.3s;
}

.share-btn:hover {
    transform: translateY(-3px);
    color: white;
}

.share-btn.facebook { background: #3b5998; }
.share-btn.twitter { background: #1da1f2; }
.share-btn.linkedin { background: #0077b5; }
.share-btn.whatsapp { background: #25d366; }

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
    .article-header { padding: 80px 0 40px; }
    .article-meta-header { flex-direction: column; gap: 10px; }
    .article-navigation { flex-direction: column; gap: 20px; }
    .nav-article { max-width: 100%; text-align: center !important; }
    .author-box { flex-direction: column; text-align: center; }
}
</style>

<!-- ================= ARTICLE HEADER ================= -->
<section class="article-header" style="--article-bg: url('<?= $article['image'] ?>')">
    <div class="container">
        <span class="badge bg-white text-dark mb-3 px-3 py-2">
            <?= htmlspecialchars($article['category']) ?>
        </span>
        <h1 class="display-5 fw-bold mb-4"><?= htmlspecialchars($article['title']) ?></h1>
        
        <div class="article-meta-header">
            <span><i class="far fa-user"></i> <?= htmlspecialchars($article['author']) ?></span>
            <span><i class="far fa-calendar"></i> <?= $article['date_formatted'] ?></span>
            <span><i class="far fa-clock"></i> <?= $article['read_time'] ?> min de lecture</span>
            <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?> vues</span>
        </div>
    </div>
</section>

<!-- ================= ARTICLE CONTENT ================= -->
<section class="pb-5">
    <div class="container">
        <div class="row">
            <!-- MAIN CONTENT -->
            <div class="col-lg-8">
                <div class="article-content-wrapper p-4 p-md-5">
                    
                    <!-- Featured Image -->
                    <img src="<?= $article['image'] ?>" 
                         alt="<?= htmlspecialchars($article['title']) ?>"
                         class="img-fluid rounded-3 mb-4 w-100"
                         style="max-height: 400px; object-fit: cover;"
                         onerror="this.src='<?= base_url('assets/backend/images/defaut-logo.jpeg') ?>'">
                    
                    <!-- Content -->
                    <div class="article-content">
                        <?php if (!empty($article['resume'])): ?>
                            <p class="lead text-muted fst-italic border-start border-4 border-teal ps-4 mb-4">
                                <?= htmlspecialchars($article['resume']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?= $article['content'] ?>
                    </div>
                    
                    <!-- Tags -->
                    <?php if (!empty($article['tags'])): ?>
                    <div class="article-tags">
                        <i class="fas fa-tags text-teal me-2"></i>
                        <?php foreach ($article['tags'] as $tag): ?>
                            <a href="<?= base_url('blog/recherche?q=' . urlencode($tag)) ?>" class="tag">
                                #<?= htmlspecialchars($tag) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Author Box -->
                    <div class="author-box">
                        <img src="<?= base_url('assets/backend/images/default-avatar.jpg') ?>" 
                             alt="<?= htmlspecialchars($article['author']) ?>"
                             class="author-avatar"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($article['author']) ?>&background=1a8c78&color=fff'">
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($article['author']) ?></h5>
                            <p class="text-muted mb-0">Auteur et contributeur sur notre blog</p>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <?php if (!empty($navigation['prev']) || !empty($navigation['next'])): ?>
                    <div class="article-navigation">
                        <?php if (!empty($navigation['prev'])): ?>
                        <div class="nav-article prev">
                            <span class="nav-label"><i class="fas fa-arrow-left me-2"></i>Article précédent</span>
                            <a href="<?= $navigation['prev']['url'] ?>" class="nav-title">
                                <?= htmlspecialchars($navigation['prev']['title']) ?>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="nav-article prev"></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($navigation['next'])): ?>
                        <div class="nav-article next">
                            <span class="nav-label">Article suivant<i class="fas fa-arrow-right ms-2"></i></span>
                            <a href="<?= $navigation['next']['url'] ?>" class="nav-title">
                                <?= htmlspecialchars($navigation['next']['title']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Related Articles -->
                <?php if (!empty($related_articles)): ?>
                <div class="related-section">
                    <h3 class="fw-bold text-teal mb-4"><i class="fas fa-th-large me-2"></i>Articles similaires</h3>
                    <div class="related-grid">
                        <?php foreach ($related_articles as $related): ?>
                        <div class="related-card">
                            <img src="<?= $related['image'] ?>" class="related-img" alt=""
                                 onerror="this.src='<?= base_url('assets/backend/images/defaut-logo.jpeg') ?>'">
                            <div class="related-content">
                                <small class="text-teal fw-bold"><?= htmlspecialchars($related['category']) ?></small>
                                <h5 class="mt-2 mb-3">
                                    <a href="<?= $related['url'] ?>" class="text-dark text-decoration-none stretched-link">
                                        <?= htmlspecialchars(strlen($related['title']) > 60 ? substr($related['title'], 0, 60) . '...' : $related['title']) ?>
                                    </a>
                                </h5>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span><i class="far fa-calendar me-1"></i> <?= $related['date_formatted'] ?></span>
                                    <span><i class="far fa-eye me-1"></i> <?= number_format($related['views']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <div class="sidebar-sticky">
                    
                    <!-- Share -->
                    <div class="sidebar-widget text-center">
                        <h5 class="widget-title"><i class="fas fa-share-alt me-2"></i>Partager</h5>
                        <div class="share-buttons justify-content-center">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>" 
                               target="_blank" class="share-btn facebook" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($article['title']) ?>" 
                               target="_blank" class="share-btn twitter" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(current_url()) ?>" 
                               target="_blank" class="share-btn linkedin" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' ' . current_url()) ?>" 
                               target="_blank" class="share-btn whatsapp" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Suivez-nous -->
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
                    
                    <!-- Popular -->
                    <?php if (!empty($popular_articles)): ?>
                    <div class="sidebar-widget">
                        <h5 class="widget-title"><i class="fas fa-fire me-2"></i>Les plus lus</h5>
                        <?php foreach($popular_articles as $pop): ?>
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <img src="<?= $pop['image'] ?>" width="80" height="60" class="rounded object-fit-cover" alt=""
                                 onerror="this.src='<?= base_url('assets/backend/images/defaut-logo.jpeg') ?>'">
                            <div>
                                <h6 class="mb-1" style="font-size: 0.95rem;">
                                    <a href="<?= $pop['url'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars(strlen($pop['title']) > 50 ? substr($pop['title'], 0, 50) . '...' : $pop['title']) ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="far fa-eye me-1"></i><?= number_format($pop['views']) ?> vues
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Back to Blog -->
                    <div class="sidebar-widget text-center bg-light">
                        <a href="<?= base_url('blog') ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Tous les articles
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>