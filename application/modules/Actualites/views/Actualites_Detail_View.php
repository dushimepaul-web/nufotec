<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>


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
        <header class="bg-primary bg-gradient text-white py-4 mb-4">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto text-center">
                        <h1 class="display-4 fw-bold mb-4"><?= htmlspecialchars($article['titre']) ?></h1>
                        
                        <?php if (!empty($article['for_subscriber']) && $article['for_subscriber'] == 1): ?>
                            <div class="badge text-bg-danger rounded-pill shadow mb-3">
                                <i class='bx bx-crown me-1'></i>Réservé aux abonnés
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                            <span><i class='bx bx-user me-1'></i><?= htmlspecialchars($article['auteur'] ?? 'Admin') ?></span>
                            <span class="mx-2">|</span>
                            <span><i class='bx bx-calendar me-1'></i><?= date('d/m/Y H:i', strtotime($article['date_publication'])) ?></span>
                            <span class="mx-2">|</span>
                            <span><i class='bx bx-show me-1'></i><?= number_format($article['vues'] ?? 0) ?> vues</span>
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
                    <div class="mb-3">
                        <a href="<?= base_url('Actualites') ?>" class="btn btn-outline-secondary">
                            <i class='bx bx-arrow-back me-1'></i>Retour aux articles
                        </a>
                    </div>
                    
                    <!-- Image principale -->
                    <?php if (!empty($article['image_principale'])): ?>
                        <img src="<?= base_url($article['image_principale']) ?>" 
                             alt="<?= htmlspecialchars($article['titre']) ?>"
                             class="img-fluid rounded-4 shadow mb-4 w-100" style="object-fit: cover; max-height: 500px;">
                    <?php endif; ?>
                    
                    <!-- Contenu -->
                    <div class="card card-outline-secondary mb-4">
                        <div class="card-body">
                            <?php if (!empty($article['resume'])): ?>
                                <div class="lead fst-italic border-start border-4 border-danger ps-3 mb-4">
                                    <?= nl2br(htmlspecialchars($article['resume'])) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <?= $article['contenu'] ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tags -->
                    <?php if (!empty($article['tags_array'])): ?>
                        <div class="d-flex flex-wrap align-items-center gap-2 my-4">
                            <h5 class="mb-0 me-3"><i class='bx bx-tag me-2'></i>Tags :</h5>
                            <?php foreach ($article['tags_array'] as $tag): ?>
                                <span class="badge text-bg-light border rounded-pill"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Boutons de partage -->
                    <?php if (!empty($article['in_socialmedia']) && $article['in_socialmedia'] == 1): ?>
                        <div class="share-buttons border-top pt-4 mt-5">
                            <h5 class="mb-3">Partager cet article :</h5>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>" 
                               target="_blank" class="btn btn-primary">
                                <i class='bx bxl-facebook me-1'></i>Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($article['titre']) ?>" 
                               target="_blank" class="btn btn-info">
                                <i class='bx bxl-twitter me-1'></i>Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(current_url()) ?>" 
                               target="_blank" class="btn btn-secondary">
                                <i class='bx bxl-linkedin me-1'></i>LinkedIn
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['titre'] . ' ' . current_url()) ?>" 
                               target="_blank" class="btn btn-success">
                                <i class='bx bxl-whatsapp me-1'></i>WhatsApp
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Articles récents -->
                    <?php if (!empty($recent_articles)): ?>
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class='bx bx-time me-2'></i>Articles récents</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($recent_articles as $recent): ?>
                                    <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                                        <img src="<?= !empty($recent['image_principale']) ? base_url($recent['image_principale']) : base_url('assets/images/news-placeholder.jpg') ?>" 
                                             alt="<?= htmlspecialchars($recent['titre']) ?>"
                                             class="rounded-3 shadow-sm flex-shrink-0" style="width: 80px; height: 60px; object-fit: cover;">
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="<?= base_url('actualite/' . $recent['slug']) ?>" class="text-decoration-none text-reset">
                                                    <?= htmlspecialchars($recent['titre']) ?>
                                                </a>
                                            </h5>
                                            <div class="small text-muted">
                                                <i class='bx bx-calendar me-1'></i><?= date('d/m/Y', strtotime($recent['date_publication'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Catégorie -->
                    <?php if (!empty($article['categorie'])): ?>
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class='bx bx-folder me-2'></i>Catégorie</h5>
                            </div>
                            <div class="card-body">
                                <span class="badge text-bg-primary rounded-pill"><?= htmlspecialchars($article['categorie']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Statistiques -->
                    <div class="card card-outline card-primary mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class='bx bx-stats me-2'></i>Statistiques</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
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