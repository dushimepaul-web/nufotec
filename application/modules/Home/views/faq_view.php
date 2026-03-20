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
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);

        /* Valeurs RGB pour les transparences */
        --primary-rgb: 15, 76, 58;
        --primary-light-rgb: 26, 107, 82;
        --primary-dark-rgb: 10, 51, 38;
        --accent-rgb: 212, 175, 55;
        --accent-hover-rgb: 184, 150, 46;
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

    /* ===== FAQ SECTION ===== */
    .faq-section {
        padding: 80px 0;
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
    }

    .section-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .section-tag {
        display: inline-block;
        background: rgba(var(--accent-rgb), 0.15);
        color: var(--accent-hover);
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 25px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: var(--gray);
        max-width: 600px;
        margin: 0 auto;
    }

    /* ===== FAQ ACCORDION ===== */
    .faq-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .faq-category {
        margin-bottom: 40px;
    }

    .category-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--accent);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .category-title i {
        color: var(--accent);
        font-size: 1.5rem;
    }

    .faq-item {
        background: white;
        border-radius: 15px;
        margin-bottom: 15px;
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid transparent;
    }

    .faq-item:hover {
        box-shadow: var(--shadow-lg);
        border-color: rgba(var(--accent-rgb), 0.3);
    }

    .faq-item.active {
        border-color: var(--accent);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.15);
    }

    .faq-question {
        width: 100%;
        padding: 20px 25px;
        background: none;
        border: none;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--dark);
        transition: var(--transition);
    }

    .faq-question:hover {
        color: var(--primary);
    }

    .faq-question-text {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .faq-number {
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .faq-item.active .faq-number {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
    }

    .faq-icon {
        width: 30px;
        height: 30px;
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-bounce);
        flex-shrink: 0;
    }

    .faq-item.active .faq-icon {
        background: var(--accent);
        color: var(--primary-dark);
        transform: rotate(180deg);
    }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out, padding 0.4s ease;
    }

    .faq-answer-content {
        padding: 0 25px 25px 72px;
        color: var(--gray);
        line-height: 1.8;
        font-size: 0.95rem;
    }

    .faq-item.active .faq-answer {
        max-height: 500px;
    }

    /* ===== NO FAQ MESSAGE ===== */
    .no-faq {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .no-faq-icon {
        width: 80px;
        height: 80px;
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 20px;
    }

    .no-faq h3 {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 10px;
    }

    .no-faq p {
        color: var(--gray);
    }

    /* ===== CTA BOX ===== */
    .faq-cta {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        margin-top: 50px;
        position: relative;
        overflow: hidden;
    }

    .faq-cta::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
        pointer-events: none;
    }

    .faq-cta h3 {
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .faq-cta p {
        color: rgba(255,255,255,0.8);
        margin-bottom: 25px;
        position: relative;
        z-index: 1;
    }

    .cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: var(--accent);
        color: var(--primary-dark);
        font-weight: 700;
        padding: 14px 30px;
        border-radius: 50px;
        text-decoration: none;
        transition: var(--transition);
        position: relative;
        z-index: 1;
        overflow: hidden;
    }

    .cta-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--accent-hover);
        transition: left 0.3s ease;
        z-index: -1;
    }

    .cta-btn:hover {
        color: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
    }

    .cta-btn:hover::before {
        left: 0;
    }

    @media (max-width: 768px) {
        .faq-section { padding: 50px 0; }
        .section-title { font-size: 2rem; }
        .faq-question { padding: 18px 20px; font-size: 0.95rem; }
        .faq-answer-content { padding: 0 20px 20px 67px; }
        .faq-number { width: 30px; height: 30px; font-size: 0.8rem; }
        .faq-cta { padding: 30px 20px; }
        .faq-cta h3 { font-size: 1.3rem; }
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
                    <h1 class="hero-title animate__animated animate__fadeInUp">FAQ</h1>
                    <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                        Questions Fréquemment Posées
                    </h2>
                    <p class="hero-text animate__animated animate__fadeInUp animate__delay-2s">
                        Trouvez rapidement les réponses à vos questions sur Nufotec et nos produits.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php endif; ?>

<!-- ===== FAQ SECTION ===== -->
<section class="faq-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Besoin d'aide ?</span>
            <h2 class="section-title">Foire Aux Questions</h2>
            <p class="section-subtitle">
                Retrouvez ici les réponses aux questions les plus fréquemment posées. 
                Si vous ne trouvez pas votre réponse, n'hésitez pas à nous contacter.
            </p>
        </div>

        <?php if (!empty($faq)): ?>
            <?php 
            // Grouper les FAQ par catégorie
            $faq_by_category = [];
            foreach ($faq as $item) {
                $cat = !empty($item['categorie']) ? $item['categorie'] : 'general';
                if (!isset($faq_by_category[$cat])) {
                    $faq_by_category[$cat] = [];
                }
                $faq_by_category[$cat][] = $item;
            }
            
            // Traduction des catégories et icônes
            $category_info = [
                'general' => ['title' => 'Général', 'icon' => 'bx-info-circle'],
                'produits' => ['title' => 'Produits', 'icon' => 'bx-package'],
                'qualite' => ['title' => 'Qualité & Certifications', 'icon' => 'bx-badge-check'],
                'investissement' => ['title' => 'Investissement', 'icon' => 'bx-trending-up'],
                'social' => ['title' => 'Impact Social', 'icon' => 'bx-group'],
                'technique' => ['title' => 'Technique', 'icon' => 'bx-cog'],
            ];
            ?>

            <div class="faq-container">
                <?php 
                $global_index = 1;
                foreach ($faq_by_category as $category => $items): 
                    $cat_info = isset($category_info[$category]) ? $category_info[$category] : ['title' => ucfirst($category), 'icon' => 'bx-help-circle'];
                ?>
                    <div class="faq-category" data-aos="fade-up">
                        <h3 class="category-title">
                            <i class="bx <?= $cat_info['icon'] ?>"></i>
                            <?= $cat_info['title'] ?>
                        </h3>
                        
                        <?php foreach ($items as $item): ?>
                            <div class="faq-item" data-aos="fade-up" data-aos-delay="<?= ($global_index % 5) * 100 ?>">
                                <button class="faq-question" onclick="toggleFaq(this)">
                                    <span class="faq-question-text">
                                        <span class="faq-number"><?= $global_index ?></span>
                                        <?= htmlspecialchars($item['question']) ?>
                                    </span>
                                    <span class="faq-icon">
                                        <i class="bx bx-chevron-down"></i>
                                    </span>
                                </button>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <?= nl2br(htmlspecialchars($item['reponse'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            $global_index++;
                            endforeach; 
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- CTA Box -->
            <div class="faq-cta" data-aos="zoom-in">
                <h3>Vous avez encore des questions ?</h3>
                <p>Notre équipe est là pour vous aider et répondre à toutes vos interrogations.</p>
                <a href="<?php echo base_url('contact'); ?>" class="cta-btn">
                    <i class="bx bx-envelope"></i>
                    Contactez-nous
                </a>
            </div>

        <?php else: ?>
            <div class="no-faq" data-aos="fade-up">
                <div class="no-faq-icon">
                    <i class="bx bx-help-circle"></i>
                </div>
                <h3>Aucune question disponible</h3>
                <p>Les FAQ seront bientôt disponibles. Revenez nous voir prochainement !</p>
            </div>
        <?php endif; ?>
    </div>
</section>

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

    // FAQ Accordion Functionality
    function toggleFaq(button) {
        const faqItem = button.parentElement;
        const isActive = faqItem.classList.contains('active');
        
        // Fermer tous les autres items actifs
        document.querySelectorAll('.faq-item.active').forEach(item => {
            if (item !== faqItem) {
                item.classList.remove('active');
            }
        });
        
        // Toggle l'item actuel
        faqItem.classList.toggle('active');
    }

    // Fermer en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.faq-item')) {
            document.querySelectorAll('.faq-item.active').forEach(item => {
                item.classList.remove('active');
            });
        }
    });
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>

