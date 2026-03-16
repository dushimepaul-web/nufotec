<?php
// Définition des fonctions si non existantes (à placer dans un helper global de préférence)
if (!function_exists('fix_image_path')) {
    function fix_image_path($path) {
        if (empty($path)) return '';
        if (preg_match('#^https?://#', $path)) {
            return $path;
        }
        $CI =& get_instance();
        return $CI->config->base_url($path);
    }
}
?>

<?php include VIEWPATH . 'includes/frontend/Header.php'; ?>

<style>
    .actualites-section {
        padding: 4rem 0;
        background: #f8fafc;
    }
    .actualite-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .actualite-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -12px rgba(11,79,46,0.25);
    }
    .actualite-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
    }
    .actualite-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .actualite-category {
        display: inline-block;
        background: #e8f5e9;
        color: #0B4F2E;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.25rem 1rem;
        border-radius: 50px;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .actualite-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #0f4c3a;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    .actualite-title a {
        color: inherit;
        text-decoration: none;
    }
    .actualite-title a:hover {
        color: #d4af37;
    }
    .actualite-meta {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 1rem;
        display: flex;
        gap: 1rem;
    }
    .actualite-meta i {
        margin-right: 0.25rem;
        color: #94a3b8;
    }
    .actualite-excerpt {
        color: #334155;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex: 1;
    }
    .btn-read-more {
        align-self: flex-start;
        background: transparent;
        color: #0B4F2E;
        font-weight: 600;
        padding: 0.5rem 0;
        border-bottom: 2px solid transparent;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-read-more:hover {
        color: #d4af37;
        border-bottom-color: #d4af37;
    }
    .no-actualites {
        text-align: center;
        padding: 4rem;
        background: white;
        border-radius: 24px;
        color: #64748b;
    }
    .page-title {
        color: #0f4c3a;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 2rem;
        position: relative;
        padding-bottom: 1rem;
    }
    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 80px;
        height: 4px;
        background: #d4af37;
        border-radius: 2px;
    }
</style>

<section class="actualites-section">
    <div class="container">
        <h1 class="page-title"><?= $title ?></h1>
        
        <?php if (empty($actualites)): ?>
            <div class="no-actualites">
                <i class="fas fa-newspaper fa-3x mb-3" style="color: #cbd5e1;"></i>
                <h3>Aucune actualité pour le moment</h3>
                <p>Revenez bientôt pour découvrir nos dernières nouvelles.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($actualites as $article): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="actualite-card">
                            <?php if (!empty($article['image_principale'])): ?>
                                <img src="<?= fix_image_path($article['image_principale']) ?>" 
                                     alt="<?= htmlspecialchars($article['titre']) ?>" 
                                     class="actualite-image">
                            <?php endif; ?>
                            <div class="actualite-content">
                                <?php if (!empty($article['categorie'])): ?>
                                    <span class="actualite-category"><?= htmlspecialchars($article['categorie']) ?></span>
                                <?php endif; ?>
                                <h2 class="actualite-title">
                                    <a href="<?= base_url('actualites/view/' . $article['slug']) ?>">
                                        <?= htmlspecialchars($article['titre']) ?>
                                    </a>
                                </h2>
                                <div class="actualite-meta">
                                    <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($article['date_publication'])) ?></span>
                                    <?php if (!empty($article['auteur'])): ?>
                                        <span><i class="far fa-user"></i> <?= htmlspecialchars($article['auteur']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="actualite-excerpt">
                                    <?= character_limiter(strip_tags($article['contenu']), 150) ?>
                                </div>
                                <a href="<?= base_url('actualites/view/' . $article['slug']) ?>" class="btn-read-more">
                                    Lire la suite <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include VIEWPATH . 'includes/frontend/Footer.php'; ?>