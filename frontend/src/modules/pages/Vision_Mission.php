<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    :root {
        --primary: #0f4c3a;
        --primary-light: #1a6b52;
        --primary-dark: #0a3326;
        --accent: #d4af37;
        --accent-hover: #b8962e;
        --accent-light: #f4d03f;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --gray-light: #dee2e6;
        --shadow: 0 4px 6px rgba(0,0,0,0.1);
        --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
        --shadow-glow: 0 0 30px rgba(212, 175, 55, 0.3);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);

        /* Valeurs RGB pour les transparences */
        --primary-rgb: 15, 76, 58;
        --primary-light-rgb: 26, 107, 82;
        --primary-dark-rgb: 10, 51, 38;
        --accent-rgb: 212, 175, 55;
        --accent-hover-rgb: 184, 150, 46;
        --accent-light-rgb: 244, 208, 63;
        --gray-rgb: 108, 117, 125;
        --gray-light-rgb: 222, 226, 230;
    }



<style>


.cta-section {
    padding: 100px 0;
    background: white;
}

.cta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.cta-item {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    min-height: 400px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    display: flex;
    flex-direction: column;
    transition: var(--transition);
    cursor: pointer;
}

.cta-item[style*="background-image"] {
    background-size: cover;
    background-position: center;
}

.cta-overlay {
    flex: 1;
    padding: 40px 30px;
    background: linear-gradient(to bottom, rgba(15, 76, 58, 0.8), rgba(10, 51, 38, 0.95));
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    z-index: 2;
    transition: var(--transition);
}

.cta-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 60px rgba(0,0,0,0.2);
}

.cta-item:hover .cta-overlay {
    background: linear-gradient(to bottom, rgba(15, 76, 58, 0.9), rgba(10, 51, 38, 0.98));
}

.cta-badge {
    align-self: flex-start;
    background: var(--accent);
    color: var(--primary-dark);
    font-size: 0.75rem;
    font-weight: 800;
    padding: 6px 16px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
}

.cta-content h3 {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    line-height: 1.3;
}

.cta-content p {
    color: rgba(255,255,255,0.8);
    font-size: 1rem;
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
    padding: 14px 28px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
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
    transform: translateX(5px);
}

.cta-btn:hover::before {
    left: 0;
}

.cta-btn i {
    transition: transform 0.3s ease;
}

.cta-btn:hover i {
    transform: translateX(5px);
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

.cta-item:hover .cta-shine {
    left: 150%;
}

@media (max-width: 768px) {
    .cta-section { padding: 60px 0; }
    .cta-grid { grid-template-columns: 1fr; gap: 20px; }
    .cta-item { min-height: 350px; }
    .cta-overlay { padding: 30px 25px; }
}

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

    .hero-btn {
        display: inline-flex;
        align-items: center;
        background: var(--accent);
        color: var(--primary-dark);
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .hero-btn:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
        color: var(--primary-dark);
    }

    @media (max-width: 991px) {
        .hero-section {
            height: 280px;
        }
        .hero-title {
            font-size: 1.8rem;
        }
        .hero-subtitle {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 768px) {
        .hero-section {
            height: 250px;
            min-height: 220px;
        }
        .hero-title {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        .hero-subtitle {
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .hero-text {
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .hero-btn {
            padding: 10px 25px;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .hero-section {
            height: 220px;
            min-height: 200px;
        }
        .hero-title {
            font-size: 1.3rem;
        }
        .hero-subtitle {
            font-size: 0.95rem;
        }
        .hero-text {
            font-size: 0.85rem;
        }
    }

    /* ================= CARDS ================= */
    .mission-card {
        border-radius: 15px;
        background: #fff;
        transition: 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .mission-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .icon-box {
        width: 55px;
        height: 55px;
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 15px;
    }

    /* Couleurs d'icône par type (basées sur la palette AGF) */
    .icon-box.vision { background: rgba(var(--primary-rgb), 0.1); color: var(--primary); }
    .icon-box.mission { background: rgba(var(--primary-light-rgb), 0.1); color: var(--primary-light); }
    .icon-box.value { background: rgba(var(--accent-rgb), 0.1); color: var(--accent-hover); }
    .icon-box.objective { background: rgba(var(--accent-light-rgb), 0.1); color: var(--accent); }
    .icon-box.slogan { background: rgba(var(--gray-rgb), 0.1); color: var(--gray); }
    .icon-box.other { background: rgba(var(--gray-light-rgb), 0.1); color: var(--gray); }

    /* Badge de type */
    .type-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
    }

    .badge-vision { background: rgba(var(--primary-rgb), 0.15); color: var(--primary); }
    .badge-mission { background: rgba(var(--primary-light-rgb), 0.15); color: var(--primary-light); }
    .badge-value { background: rgba(var(--accent-rgb), 0.15); color: var(--accent-hover); }
    .badge-objective { background: rgba(var(--accent-light-rgb), 0.15); color: var(--accent); }
    .badge-slogan { background: rgba(var(--gray-rgb), 0.15); color: var(--gray); }
    .badge-other { background: rgba(var(--gray-light-rgb), 0.15); color: var(--gray); }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--dark);
    }

    .card-description {
        color: var(--gray);
        line-height: 1.6;
        font-size: 0.9rem;
        flex-grow: 1;
    }
</style>

<!-- ===== HERO SECTION ===== -->
<?php if (isset($hero_section) && !empty($hero_section)): ?>
<div class="hero-section position-relative overflow-hidden">
    <?php if (!empty($hero_section['image_url'])): ?>
    <div class="hero-bg-image">
        <img src="<?php echo base_url($hero_section['image_url']); ?>" 
             alt="<?php echo isset($hero_section['titre_section']) ? $hero_section['titre_section'] : 'Hero background'; ?>"
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
                    <?php endif; ?>
                    <?php if (!empty($hero_section['sous_titre'])): ?>
                        <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                            <?php echo $hero_section['sous_titre']; ?>
                        </h2>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['contenu_texte'])): ?>
                        <p class="hero-text animate__animated animate__fadeInUp animate__delay-2s">
                            <?php echo $hero_section['contenu_texte']; ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['bouton_texte']) && !empty($hero_section['bouton_lien'])): ?>
                        <a href="<?php echo base_url($hero_section['bouton_lien']); ?>" 
                           class="hero-btn animate__animated animate__fadeInUp animate__delay-3s">
                            <?php echo $hero_section['bouton_texte']; ?>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php endif; ?>

<!-- ================= SECTION DES CARTES ================= -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <?php if (!empty($statements)): ?>
                <?php foreach ($statements as $st): ?>
                    <?php
                    // Déterminer la classe CSS selon le type
                    $type = $st['type'];
                    $icon_class = '';
                    $badge_class = '';
                    $badge_text = '';
                    switch ($type) {
                        case 'vision':
                            $icon_class = 'vision';
                            $badge_class = 'badge-vision';
                            $badge_text = 'Vision';
                            break;
                        case 'mission':
                            $icon_class = 'mission';
                            $badge_class = 'badge-mission';
                            $badge_text = 'Mission';
                            break;
                        case 'value':
                            $icon_class = 'value';
                            $badge_class = 'badge-value';
                            $badge_text = 'Valeur';
                            break;
                        case 'objective':
                            $icon_class = 'objective';
                            $badge_class = 'badge-objective';
                            $badge_text = 'Objectif';
                            break;
                        case 'slogan':
                            $icon_class = 'slogan';
                            $badge_class = 'badge-slogan';
                            $badge_text = 'Slogan';
                            break;
                        default:
                            $icon_class = 'other';
                            $badge_class = 'badge-other';
                            $badge_text = 'Autre';
                    }
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="mission-card p-4 shadow-sm">
                            <div class="icon-box <?= $icon_class ?>">
                                <i class="bx <?= htmlspecialchars($st['icon'] ?? 'bx-bullseye') ?>"></i>
                            </div>
                            <span class="type-badge <?= $badge_class ?>"><?= $badge_text ?></span>
                            <?php if (!empty($st['title'])): ?>
                                <h3 class="card-title"><?= htmlspecialchars($st['title']) ?></h3>
                            <?php endif; ?>
                            <p class="card-description"><?= nl2br(htmlspecialchars($st['description'] ?? '')) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Aucune déclaration disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- APPELS À L'ACTION - GRILLE DYNAMIQUE -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php if (!empty($appels_action)): ?>
<section class="cta-section py-5" id="cta">
    <div class="container-fluid px-4">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <span class="section-tag" style="font-size: 20px; font-weight: 200">Take Action Now</span>
            <h2 class="section-title" style="font-size: 40px; font-weight: bold;">Our Calls to Action</h2>
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
            ?>
            
            <div class="cta-item" <?= $bg_style ?> data-aos="zoom-in">
                <div class="cta-overlay">
                    
                    <?php if (!empty($cta['type_public']) && $cta['type_public'] !== 'all'): ?>
                        <span class="cta-badge">
                            <?= htmlspecialchars($cta['type_public']) ?>
                        </span>
                    <?php endif; ?>
                    
                    <div class="cta-content">
                        <h3><?= htmlspecialchars($cta['titre']) ?></h3>
                        <p><?= htmlspecialchars($cta['sous_titre']) ?></p>
                    </div>
                    
                    <a href="<?= htmlspecialchars($cta['bouton_lien']) ?>" class="cta-btn">
                        <?= htmlspecialchars($cta['bouton_texte']) ?>
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                
                <div class="cta-shine"></div>
            </div>
            
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({
    duration: 800,
    easing: 'ease-out-cubic',
    once: true,
    offset: 50
});
</script>


<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>