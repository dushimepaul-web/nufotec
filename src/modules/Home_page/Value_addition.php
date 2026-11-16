DETAILL PAGE 



<?php
// Helper pour les URLs d'images
if (!function_exists('get_image_url')) {
    function get_image_url($image_path) {
        if (empty($image_path)) return null;
        $image_path = trim($image_path);
        $image_path = ltrim($image_path, '/');
        $base_url = rtrim(base_url(), '/') . '/';
        return $base_url . $image_path;
    }
}

// Nettoyage HTML sécurisé
if (!function_exists('clean_html_full')) {
    function clean_html_full($content) {
        if (empty($content)) return '';
        $allowed = '<p><br><strong><b><em><i><u><s><strike><del><span><div><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img><table><thead><tbody><tr><td><th><pre><code><hr><sub><sup><figure><figcaption>';
        $content = strip_tags($content, $allowed);
        $content = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);
        $content = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $content);
        return $content;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Lightbox pour galerie -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0f4c3a;
            --primary-dark: #0a3328;
            --accent: #d4af37;
            --light: #f8f9fa;
            --gray: #6c757d;
        }

        /* Header de la page */
        .section-detail-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }

        .section-detail-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="2"/></svg>');
            background-size: 100px;
            opacity: 0.5;
        }

        .breadcrumb-custom {
            background: rgba(255,255,255,0.1);
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            backdrop-filter: blur(10px);
        }

        .breadcrumb-custom a {
            color: var(--accent);
            text-decoration: none;
        }

        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }

        .section-tag-detail {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: var(--accent);
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 50px;
            margin-bottom: 20px;
        }

        .section-title-detail {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        /* Contenu principal */
        .section-content-full {
            font-size: 1.1rem;
            line-height: 1.9;
            color: #444;
        }

        .section-content-full p {
            margin-bottom: 1.5rem;
        }

        .section-content-full strong,
        .section-content-full b {
            font-weight: 700 !important;
            color: var(--primary);
        }

        .section-content-full em,
        .section-content-full i {
            font-style: italic;
        }

        .section-content-full h2 {
            color: var(--primary);
            font-weight: 700;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--accent);
            display: inline-block;
        }

        .section-content-full h3 {
            color: var(--primary);
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .section-content-full ul,
        .section-content-full ol {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        .section-content-full li {
            margin-bottom: 0.75rem;
        }

        .section-content-full a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 2px solid var(--accent);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .section-content-full a:hover {
            background: var(--accent);
            color: var(--primary-dark);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .section-content-full blockquote {
            border-left: 5px solid var(--accent);
            background: var(--light);
            padding: 20px 25px;
            margin: 2rem 0;
            border-radius: 0 12px 12px 0;
            font-style: italic;
            color: var(--gray);
        }

        .section-content-full img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 1.5rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .section-content-full table {
            width: 100%;
            margin: 2rem 0;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .section-content-full th {
            background: var(--primary);
            color: white;
            padding: 15px;
            font-weight: 600;
            text-align: left;
        }

        .section-content-full td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .section-content-full tr:nth-child(even) {
            background: #f8f9fa;
        }

        /* Galerie d'images */
        .gallery-section {
            background: var(--light);
            padding: 40px;
            border-radius: 20px;
            margin-top: 3rem;
        }

        .gallery-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .gallery-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            aspect-ratio: 4/3;
        }

        .gallery-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(15,76,58,0.9) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-caption {
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .gallery-zoom {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 24px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .gallery-item:hover .gallery-zoom {
            opacity: 1;
        }

        /* Image principale hero */
        .hero-image-section {
            margin: -60px 0 40px;
            position: relative;
        }

        .hero-image-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.2);
            position: relative;
        }

        .hero-image-container img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .hero-image-badge {
            position: absolute;
            bottom: 30px;
            right: 30px;
            background: var(--accent);
            color: var(--primary-dark);
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Navigation retour */
        .back-navigation {
            position: sticky;
            top: 20px;
            z-index: 100;
            margin-bottom: 30px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-back:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
        }

        /* Métadonnées */
        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            opacity: 0.9;
        }

        .meta-item i {
            color: var(--accent);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .section-detail-header {
                padding: 40px 0;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .gallery-section {
                padding: 20px;
            }

            .hero-image-container img {
                height: 250px;
            }

            .section-content-full {
                font-size: 1rem;
            }
        }

        /* Animations */
        .fade-in-up {
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <!-- Header de la section -->
    <header class="section-detail-header">
        <div class="container position-relative">
            
            <!-- Fil d'Ariane -->
            <nav class="breadcrumb-custom mb-4 fade-in-up">
                <a href="<?= base_url() ?>"><i class="bi bi-house-fill"></i> Accueil</a>
                <i class="bi bi-chevron-right"></i>
                <?php if (!empty($page)): ?>
                    <a href="<?= base_url('pages/' . $page['slug']) ?>"><?= htmlspecialchars($page['titre_page']) ?></a>
                    <i class="bi bi-chevron-right"></i>
                <?php endif; ?>
                <span>Détail</span>
            </nav>

            <!-- Tag et titre -->
            <?php if (!empty($section['titre_section'])): ?>
                <span class="section-tag-detail fade-in-up">
                    <i class="bi bi-bookmark-fill"></i>
                    <?= htmlspecialchars($section['titre_section']) ?>
                </span>
            <?php endif; ?>

            <h1 class="section-title-detail fade-in-up">
                <?= htmlspecialchars($section['sous_titre'] ?? $section['titre_section'] ?? 'Détail de la section') ?>
            </h1>

            <!-- Métadonnées -->
            <div class="meta-info fade-in-up">
                <?php if (!empty($page)): ?>
                    <div class="meta-item">
                        <i class="bi bi-folder"></i>
                        <span><?= htmlspecialchars($page['titre_page']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="meta-item">
                    <i class="bi bi-images"></i>
                    <span><?= count($images) ?> image<?= count($images) > 1 ? 's' : '' ?></span>
                </div>
                <div class="meta-item">
                    <i class="bi bi-clock"></i>
                    <span><?= ceil(str_word_count(strip_tags($section['contenu_texte'] ?? '')) / 200) ?> min de lecture</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="container py-5">
        
        <!-- Bouton retour sticky -->
        <div class="back-navigation">
            <?php 
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $back_url = (!empty($page)) ? base_url('pages/' . $page['slug']) : (empty($referer) ? base_url() : $referer);
            ?>
            <a href="<?= $back_url ?>" class="btn-back" onclick="history.back(); return false;">
                <i class="bi bi-arrow-left"></i>
                Retour à la page
            </a>
        </div>

        <!-- Image principale (si existe) -->
        <?php 
        $main_image = null;
        foreach ($images as $img) {
            if ($img['type'] === 'principale') {
                $main_image = $img;
                break;
            }
        }
        ?>
        
        <?php if ($main_image): ?>
            <section class="hero-image-section fade-in-up">
                <div class="hero-image-container">
                    <img src="<?= get_image_url($main_image['url']) ?>" 
                         alt="<?= htmlspecialchars($main_image['alt']) ?>"
                         loading="eager">
                    <?php if (!empty($section['titre_section'])): ?>
                        <div class="hero-image-badge">
                            <i class="bi bi-image-fill me-2"></i>
                            <?= htmlspecialchars($section['titre_section']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Contenu texte complet -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <article class="section-content-full fade-in-up">
                    <?= clean_html_full($section['contenu_texte'] ?? '') ?>
                </article>
            </div>
        </div>

        <!-- Galerie d'images complète -->
        <?php if (count($images) > 0): ?>
            <section class="gallery-section fade-in-up">
                <h2 class="gallery-title">
                    <i class="bi bi-images"></i>
                    Galerie photos 
                    <span class="badge bg-primary ms-2"><?= count($images) ?></span>
                </h2>

                <div class="gallery-grid">
                    <?php foreach ($images as $index => $image): ?>
                        <a href="<?= get_image_url($image['url']) ?>" 
                           data-lightbox="section-gallery"
                           data-title="<?= htmlspecialchars($image['alt']) ?>"
                           class="gallery-item">
                            <img src="<?= get_image_url($image['url']) ?>" 
                                 alt="<?= htmlspecialchars($image['alt']) ?>"
                                 loading="lazy">
                            <div class="gallery-overlay">
                                <div class="gallery-zoom">
                                    <i class="bi bi-zoom-in"></i>
                                </div>
                                <?php if (!empty($image['legende'])): ?>
                                    <div class="gallery-caption">
                                        <?= htmlspecialchars($image['legende']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Navigation bas de page -->
        <div class="text-center mt-5 pt-5 border-top">
            <a href="<?= $back_url ?>" class="btn btn-primary btn-lg px-5 rounded-pill" onclick="history.back(); return false;">
                <i class="bi bi-arrow-left me-2"></i>
                Retour à la page précédente
            </a>
        </div>

    </main>

    <!-- Footer simple -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    
    <script>
        // Configuration Lightbox
        lightbox.option({
            'resizeDuration': 300,
            'wrapAround': true,
            'showImageNumberLabel': true,
            'albumLabel': 'Image %1 sur %2'
        });

        // Animation au scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in-up');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.gallery-item').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

</body>
</html>