<?php 
include VIEWPATH.'includes/frontend/Header.php'; 
include VIEWPATH.'includes/alerts.php';
?>

<style>
:root {
    --primary-teal: #1a8c78;
    --dark-teal: #146c5c;
    --light-bg: #f8faf9;
}
.text-teal { color: var(--primary-teal) !important; }

.search-hero {
    background: linear-gradient(135deg, #0f4c3a 0%, #146c5c 100%);
    padding: 60px 0 45px;
    color: white;
}
.search-hero h1 { font-size: 1.8rem; font-weight: 700; }
.search-hero form { max-width: 640px; margin: 0 auto; }

.search-group-title {
    color: var(--primary-teal);
    font-size: 1.15rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.search-card {
    display: flex;
    gap: 18px;
    background: #fff;
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,.07);
    transition: all .3s ease;
    text-decoration: none;
    color: #333;
}
.search-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,.12);
    text-decoration: none;
}
.search-card-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    background: var(--light-bg);
    color: var(--primary-teal);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.search-card h5 { font-size: 1.05rem; margin-bottom: 4px; color: #222; }
.search-card p { font-size: .88rem; color: #777; margin: 0; }
.search-empty {
    background: #fff;
    border-radius: 14px;
    padding: 50px 20px;
    text-align: center;
    box-shadow: 0 4px 14px rgba(0,0,0,.07);
}
.search-empty i { font-size: 3.5rem; color: #cfd8d4; margin-bottom: 16px; }
</style>

<section class="search-hero">
    <div class="container text-center">
        <h1 class="mb-2"><i class="bi bi-search"></i> Recherche</h1>
        <form action="<?= base_url('search/index') ?>" method="GET" class="position-relative">
            <input type="text" name="q" class="form-control form-control-lg rounded-pill ps-4 pe-5"
                   placeholder="Rechercher des articles, produits, pages..." 
                   value="<?= htmlspecialchars($term ?? '') ?>">
            <button type="submit" class="btn btn-warning btn-lg rounded-pill position-absolute px-4"
                    style="top:2px;right:2px;bottom:2px;">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (empty($term)): ?>
            <div class="search-empty">
                <i class="bi bi-search"></i>
                <h4>Saisissez un terme pour lancer la recherche.</h4>
            </div>
        <?php else: ?>
            <?php
                $labels = ['produits' => ['Produits', 'bi-box-seam', 'produit/'], 
                           'actualites' => ['Actualités', 'bi-newspaper', 'actualite/'], 
                           'pages' => ['Pages', 'bi-file-text', '']];
                $any = false;
            ?>
            <?php foreach ($results as $type => $items): ?>
                <?php if (!empty($items)): $any = true; ?>
                    <div class="mb-5">
                        <h4 class="search-group-title">
                            <i class="bi <?= isset($labels[$type]) ? $labels[$type][1] : 'bi-folder' ?>"></i>
                            <?= isset($labels[$type]) ? $labels[$type][0] : ucfirst($type) ?>
                        </h4>
                        <?php foreach ($items as $item): ?>
                            <?php
                                $url = isset($item['url']) && !empty($item['url'])
                                    ? $item['url']
                                    : base_url((isset($labels[$type]) ? $labels[$type][2] : '') . ($item['slug'] ?? $item['id'] ?? '#'));
                            ?>
                            <a href="<?= $url ?>" class="search-card">
                                <span class="search-card-icon">
                                    <i class="bi <?= isset($labels[$type]) ? $labels[$type][1] : 'bi-file-text' ?>"></i>
                                </span>
                                <div class="flex-grow-1 min-width-0">
                                    <h5><?= htmlspecialchars($item['titre'] ?? '') ?></h5>
                                    <?php if (!empty($item['extrait'])): ?>
                                        <p><?= htmlspecialchars(mb_strimwidth($item['extrait'], 0, 110, '...')) ?></p>
                                    <?php endif; ?>
                                </div>
                                <i class="bi bi-chevron-right align-self-center text-teal"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!$any): ?>
                <div class="search-empty">
                    <i class="bi bi-database-x"></i>
                    <h4>Aucun résultat pour « <?= htmlspecialchars($term) ?> »</h4>
                    <p class="text-muted">Essayez avec d'autres mots-clés ou parcourez les catégories du blog.</p>
                    <a href="<?= base_url('blog') ?>" class="btn btn-outline-primary rounded-pill">
                        <i class="fas fa-newspaper me-2"></i>Voir les articles
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>