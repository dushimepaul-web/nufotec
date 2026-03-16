<?php
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
if (!function_exists('fix_content_images')) {
    function fix_content_images($content) {
        if (empty($content)) return $content;
        return preg_replace_callback(
            '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i',
            function($matches) {
                $old_src = $matches[1];
                $new_src = fix_image_path($old_src);
                return str_replace($old_src, $new_src, $matches[0]);
            },
            $content
        );
    }
}
?>

<?php include VIEWPATH . 'includes/frontend/Header.php'; ?>

<style>
    .article-detail {
        padding: 4rem 0;
        background: #fff;
    }
    .article-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    .article-category {
        display: inline-block;
        background: #e8f5e9;
        color: #0B4F2E;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 0.4rem 1.5rem;
        border-radius: 50px;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .article-title {
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 700;
        color: #0f4c3a;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }
    .article-meta {
        font-size: 0.95rem;
        color: #64748b;
        display: flex;
        justify-content: center;
        gap: 2rem;
        flex-wrap: wrap;
    }
    .article-meta i {
        margin-right: 0.4rem;
        color: #94a3b8;
    }
    .article-image {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        border-radius: 24px;
        margin-bottom: 2.5rem;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .article-content {
        max-width: 800px;
        margin: 0 auto;
        font-size: 1.1rem;
        line-height: 1.8;
        color: #334155;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 16px;
        margin: 2rem 0;
    }
    .article-content h2, .article-content h3, .article-content h4 {
        color: #0f4c3a;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    .article-content a {
        color: #0B4F2E;
        text-decoration: underline;
    }
    .article-content a:hover {
        color: #d4af37;
    }
    .article-footer {
        margin-top: 4rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .article-tags {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .tag {
        background: #f1f5f9;
        color: #334155;
        padding: 0.3rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
    }
    .tag:hover {
        background: #e8f5e9;
        color: #0B4F2E;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #0B4F2E;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 2rem;
    }
    .back-link:hover {
        color: #d4af37;
    }
    .article-share {
        display: flex;
        gap: 0.75rem;
    }
    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #334155;
        text-decoration: none;
        transition: all 0.3s;
    }
    .share-btn:hover {
        background: #0B4F2E;
        color: white;
    }
</style>

<section class="article-detail">
    <div class="container">
        <a href="<?= base_url('actualites') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour aux actualités
        </a>

        <article>
            <div class="article-header">
                <?php if (!empty($actualite['categorie'])): ?>
                    <div class="article-category"><?= htmlspecialchars($actualite['categorie']) ?></div>
                <?php endif; ?>
                <h1 class="article-title"><?= htmlspecialchars($actualite['titre']) ?></h1>
                <div class="article-meta">
                    <span><i class="far fa-calendar-alt"></i> <?= date('d F Y', strtotime($actualite['date_publication'])) ?></span>
                    <?php if (!empty($actualite['auteur'])): ?>
                        <span><i class="far fa-user"></i> <?= htmlspecialchars($actualite['auteur']) ?></span>
                    <?php endif; ?>
                    <span><i class="far fa-eye"></i> <?= (int)$actualite['vues'] ?> vues</span>
                </div>
            </div>

            <?php if (!empty($actualite['image_principale'])): ?>
                <img src="<?= fix_image_path($actualite['image_principale']) ?>" 
                     alt="<?= htmlspecialchars($actualite['titre']) ?>" 
                     class="article-image">
            <?php endif; ?>

            <div class="article-content">
                <?= fix_content_images($actualite['contenu']) ?>
            </div>

            <div class="article-footer">
                <?php 
                $tags = !empty($actualite['tags']) ? json_decode($actualite['tags'], true) : [];
                if (!empty($tags) && is_array($tags)): 
                ?>
                <div class="article-tags">
                    <i class="fas fa-tags me-2" style="color: #94a3b8;"></i>
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?= base_url('actualites?tag=' . urlencode($tag)) ?>" class="tag">#<?= htmlspecialchars($tag) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="article-share">
                    <span class="me-2" style="color: #64748b;">Partager :</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>" target="_blank" class="share-btn"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($actualite['titre']) ?>" target="_blank" class="share-btn"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(current_url()) ?>" target="_blank" class="share-btn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </article>
    </div>
</section>

<?php include VIEWPATH . 'includes/frontend/Footer.php'; ?>