<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<!-- Custom CSS -->
<style>
    .article-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0;
        margin-bottom: 40px;
        border-radius: 0 0 20px 20px;
    }
    .article-meta {
        color: rgba(255,255,255,0.9);
        font-size: 0.9rem;
    }
    .article-meta i {
        margin-right: 5px;
    }
    .article-image {
        border-radius: 15px;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        margin-bottom: 30px;
        max-height: 500px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .article-image:hover {
        transform: scale(1.02);
    }
    .article-content {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #333;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    .article-content p {
        margin-bottom: 1.5rem;
    }
    .article-content h2, .article-content h3 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
        color: #2c3e50;
    }
    .article-tags {
        margin: 30px 0;
    }
    .article-tags .badge {
        font-size: 0.9rem;
        padding: 8px 15px;
        margin-right: 8px;
        background: #f8f9fa;
        color: #333;
        border: 1px solid #dee2e6;
        border-radius: 30px;
        transition: all 0.3s ease;
    }
    .article-tags .badge:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }
    .sidebar-widget {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .sidebar-widget:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .sidebar-widget h4 {
        font-size: 1.2rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
        color: #2c3e50;
        font-weight: 600;
    }
    .recent-article-item {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    .recent-article-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .recent-article-item:hover {
        transform: translateX(5px);
    }
    .recent-article-item img {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .recent-article-item h5 {
        font-size: 1rem;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .recent-article-item h5 a {
        color: #333;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .recent-article-item h5 a:hover {
        color: #dc3545;
    }
    .recent-article-item .date {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .btn-back {
        margin-bottom: 20px;
    }
    .btn-back .btn {
        border-radius: 30px;
        padding: 10px 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn-back .btn:hover {
        transform: translateX(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .share-buttons {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 2px solid #dee2e6;
    }
    .share-buttons h5 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .share-buttons .btn {
        margin-right: 10px;
        padding: 12px 25px;
        border-radius: 30px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
    }
    .share-buttons .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .btn-facebook { background: #3b5998; color: white; }
    .btn-twitter { background: #1da1f2; color: white; }
    .btn-linkedin { background: #0077b5; color: white; }
    .btn-whatsapp { background: #25d366; color: white; }
    .subscriber-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-block;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(245, 87, 108, 0.3);
    }
    .category-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 500;
        display: inline-block;
    }
    .lead.fst-italic {
        color: #6c757d;
        border-left: 4px solid #dc3545;
        padding-left: 20px;
        margin: 20px 0;
        font-size: 1.1rem;
    }
</style>

<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Blog</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('Actualites') ?>">Actualités</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($article['titre'] ?? 'Article') ?></li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="<?= base_url('Actualites') ?>" class="btn btn-outline-danger btn-sm">
                    <i class="bx bx-arrow-back me-1"></i>Retour à la liste
                </a>
            </div>
        </div>

        <!-- Article Header -->
        <header class="article-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto text-center">
                        <h1 class="display-4 fw-bold mb-4"><?= htmlspecialchars($article['titre']) ?></h1>
                        
                        <?php if (!empty($article['for_subscriber']) && $article['for_subscriber'] == 1): ?>
                            <div class="subscriber-badge">
                                <i class='bx bx-crown me-1'></i>Réservé aux abonnés
                            </div>
                        <?php endif; ?>
                        
                        <div class="article-meta">
                            <span><i class='bx bx-user'></i><?= htmlspecialchars($article['auteur'] ?? 'Admin') ?></span>
                            <span class="mx-3">|</span>
                            <span><i class='bx bx-calendar'></i><?= date('d/m/Y H:i', strtotime($article['date_publication'])) ?></span>
                            <span class="mx-3">|</span>
                            <span><i class='bx bx-show'></i><?= number_format($article['vues'] ?? 0) ?> vues</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Article Content -->
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Bouton retour -->
                    <div class="btn-back">
                        <a href="<?= base_url('Actualites') ?>" class="btn btn-outline-secondary">
                            <i class='bx bx-arrow-back me-1'></i>Retour aux articles
                        </a>
                    </div>
                    
                    <!-- Image principale -->
                    <?php if (!empty($article['image_principale'])): ?>
                        <img src="<?= base_url($article['image_principale']) ?>" 
                             alt="<?= htmlspecialchars($article['titre']) ?>"
                             class="article-image img-fluid">
                    <?php endif; ?>
                    
                    <!-- Contenu -->
                    <div class="article-content">
                        <?php if (!empty($article['resume'])): ?>
                            <div class="lead fst-italic">
                                <?= nl2br(htmlspecialchars($article['resume'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="article-full-content mt-4">
                            <?= $article['contenu'] ?>
                        </div>
                    </div>
                    
                    <!-- Tags -->
                    <?php if (!empty($article['tags_array'])): ?>
                        <div class="article-tags">
                            <h5 class="mb-3"><i class='bx bx-tag me-2'></i>Tags :</h5>
                            <?php foreach ($article['tags_array'] as $tag): ?>
                                <span class="badge"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Boutons de partage -->
                    <?php if (!empty($article['in_socialmedia']) && $article['in_socialmedia'] == 1): ?>
                        <div class="share-buttons">
                            <h5 class="mb-3">Partager cet article :</h5>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>" 
                               target="_blank" class="btn btn-facebook">
                                <i class='bx bxl-facebook me-1'></i>Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($article['titre']) ?>" 
                               target="_blank" class="btn btn-twitter">
                                <i class='bx bxl-twitter me-1'></i>Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(current_url()) ?>" 
                               target="_blank" class="btn btn-linkedin">
                                <i class='bx bxl-linkedin me-1'></i>LinkedIn
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['titre'] . ' ' . current_url()) ?>" 
                               target="_blank" class="btn btn-whatsapp">
                                <i class='bx bxl-whatsapp me-1'></i>WhatsApp
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Articles récents -->
                    <?php if (!empty($recent_articles)): ?>
                        <div class="sidebar-widget">
                            <h4><i class='bx bx-time me-2'></i>Articles récents</h4>
                            <?php foreach ($recent_articles as $recent): ?>
                                <div class="recent-article-item">
                                    <img src="<?= !empty($recent['image_principale']) ? base_url($recent['image_principale']) : base_url('assets/images/news-placeholder.jpg') ?>" 
                                         alt="<?= htmlspecialchars($recent['titre']) ?>">
                                    <div>
                                        <h5>
                                            <a href="<?= base_url('actualite/' . $recent['slug']) ?>">
                                                <?= htmlspecialchars($recent['titre']) ?>
                                            </a>
                                        </h5>
                                        <div class="date">
                                            <i class='bx bx-calendar me-1'></i><?= date('d/m/Y', strtotime($recent['date_publication'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Catégorie -->
                    <?php if (!empty($article['categorie'])): ?>
                        <div class="sidebar-widget">
                            <h4><i class='bx bx-folder me-2'></i>Catégorie</h4>
                            <span class="category-badge"><?= htmlspecialchars($article['categorie']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Statistiques -->
                    <div class="sidebar-widget">
                        <h4><i class='bx bx-stats me-2'></i>Statistiques</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class='bx bx-show text-primary me-2'></i>
                                <strong>Vues :</strong> <?= number_format($article['vues'] ?? 0) ?>
                            </li>
                            <li class="mb-2">
                                <i class='bx bx-calendar text-success me-2'></i>
                                <strong>Publié le :</strong> <?= date('d/m/Y', strtotime($article['date_publication'])) ?>
                            </li>
                            <?php if (!empty($article['created_at'])): ?>
                                <li class="mb-2">
                                    <i class='bx bx-time text-info me-2'></i>
                                    <strong>Créé le :</strong> <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Script pour les partages sociaux -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ouvrir les partages dans une nouvelle fenêtre
    document.querySelectorAll('.share-buttons a').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            window.open(this.href, 'share', 'width=600,height=400,scrollbars=yes,resizable=yes');
        });
    });
    
    // Animation smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>