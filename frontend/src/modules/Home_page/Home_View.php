<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<?php
    $site_logo = $this->Model->get_setting('site_logo');
    $site_name = $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
    $hero_bg = $this->Model->get_setting('image_hero_home', $site_logo);
    if (!empty($hero_bg) && !preg_match('/^https?:\/\//i', $hero_bg)) {
        $hero_bg = base_url((strpos($hero_bg, '/') === false && strpos($hero_bg, '\\') === false) ? 'attachments/Configurations/' . $hero_bg : $hero_bg);
    }
?>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- HERO SECTION PREMIUM (2026-2027) -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_hero-section-premium mb-4" style="--hero-home-bg: url('<?= htmlspecialchars($hero_bg) ?>')">
    <div class="home_hero-bg-overlay"></div>
    <div class="container home_hero-container">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-lg-10 col-xl-9">
                <?php if (!empty($site_logo)): ?>
                    <div class="home_hero-badge-logo mb-4 animate-fade-in">
                        <img src="<?= base_url('attachments/Configurations/' . $site_logo) ?>" 
                             alt="<?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?>" class="img-fluid">
                    </div>
                <?php endif; ?>
                
                <span class="home_badge-tag-pill mb-3 animate-fade-in">NUFOTEC BURUNDI</span>
                
                <h1 class="home_hero-main-title display-4 fw-bold text-white mb-4 animate-fade-up">
                    Bienvenue sur NUFOTEC BURUNDI
                </h1>
                
                <p class="home_hero-lead-text fs-5 text-light opacity-9 mb-5 mx-auto animate-fade-up" style="max-width: 800px;">
                    Votre plateforme de commerce en ligne rapide, fiable et sécurisée
                </p>
                
                <div class="home_hero-cta-group d-flex justify-content-center gap-3 flex-wrap animate-fade-up">
                    <a href="<?= base_url('doctor') ?>" class="btn home_btn-gold-premium px-4 py-3 rounded-pill fw-semibold shadow-lg">
                        <i class="bi bi-calendar-check me-2"></i>Être consulté
                    </a>
                    <a href="<?= base_url('Products') ?>" class="btn home_btn-outline-light-custom px-4 py-3 rounded-pill fw-semibold">
                        <i class="bi bi-box-seam me-2"></i>Acheter les produits
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 1 : PRÉSENTATION DE NUFOTEC-PHYTOMED INDUSTRIES -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_section-presentation home_py-100 bg-white">
    <div class="container home_container-1320">
        <div class="row home_presentation-row g-5">
            <!-- Colonne image à gauche (40%) -->
            <div class="col-lg-5 home_presentation-col" data-aos="fade-right">
                <div class="home_presentation-image-card">
                    <?php
                    $img_vision = $this->Model->get_setting('image_vision_2026', 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1000&q=80');
                    if (!empty($img_vision) && !preg_match('/^https?:\/\//i', $img_vision)) {
                        $img_vision = base_url((strpos($img_vision, '/') === false && strpos($img_vision, '\\') === false) ? 'attachments/Configurations/' . $img_vision : $img_vision);
                    }
                    ?>
                    <div class="home_presentation-image-inner">
                        <img src="<?= htmlspecialchars($img_vision) ?>" 
                             alt="NUFOTEC-PHYTOMED Industries - Laboratoire & Usine Moderne" 
                             onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1000&q=80'">
                    </div>
                    <!-- Espace sous l'image avant le badge Normes ISO & GMP -->
                    <div class="home_presentation-iso-badge d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: #EAF6EF; color: #0B5D3B; flex-shrink: 0;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark fs-6">Normes ISO & GMP</span>
                            <span class="text-muted small">Recherche & Innovation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne texte à droite (60%) -->
            <div class="col-lg-7 home_presentation-col" data-aos="fade-left">
                <div class="home_presentation-content ps-lg-4">
                    <div class="home_badge-pill-green home_presentation-badge">
                        <span>Vision 2026-2031</span>
                    </div>

                    <h2 class="home_presentation-title">
                        NUFOTEC-PHYTOMED INDUSTRIES – Industrialisation de l'économie des plantes médicinales en Afrique
                    </h2>

                    <p class="home_presentation-subtitle">INVESTISSEZ EN NOUS OU DEVENEZ NOTRE COURTIER CONTRACTUEL DÈS AUJOURD'HUI !</p>

                    <!-- Texte clampé à hauteur de l'image, fondu en bas, le texte ne grandit jamais la section -->
                    <div class="home_presentation-text-clamp position-relative overflow-hidden w-100">
                        <div class="text-secondary fs-6 home_presentation-paragraphs" style="line-height: 1.7;">
                            <p>Avec plus de 40 millions USD de capital d'amorçage dans le cadre de la vision quinquennale (Vision 2026-2031), NUFOTEC Limited est une entreprise de biotechnologie agro-industrielle privée, intégrée verticalement, basée au Burundi, qui s'emploie régulièrement à transformer l'agriculture biologique commerciale de plantes médicinales ciblées et sélectionnées, de cultures fonctionnelles, de fruits et de ressources agricoles riches en nutriments en MTCAs standardisées à base de plantes (Médecines Traditionnelles, Complémentaires et Alternatives), en Nutraceutiques/Compléments Alimentaires, en produits alimentaires et boissons santé fortifiés clean-label totalement exempts de sucres et de produits chimiques nocifs ajoutés, en engrais organiques haute-nutrition ainsi qu'en formulations phyto-médicinales et phyto-pharmaceutiques scientifiquement avancées soumises à des essais précliniques et cliniques via notre laboratoire de recherche scientifique et notre installation d'élevage d'animaux de laboratoire.</p>
                            <p>En intégrant l'agriculture biologique commerciale climato-intelligente, des réseaux structurés d'agriculteurs sous contrat, le profilage de produits piloté par le laboratoire, la standardisation, les essais précliniques et cliniques, et une infrastructure de transformation scalable, NUFOTEC fait passer l'agriculture d'une production de matières premières à faible marge à une agriculture industrielle et une fabrication à haute valeur ajoutée.</p>
                            <p>L'entreprise construit une plateforme de croissance conçue pour étendre la superficie cultivée, augmenter la capacité de transformation, renforcer la pénétration des exportations régionales et générer un emploi durable, en particulier pour les jeunes et les femmes, tout en contribuant à la transformation économique du Burundi et au développement de la chaîne de valeur de la santé naturelle.</p>
                            <p class="fw-medium home_text-green home_presentation-closing">Nous visons un chiffre d'affaires annuel de plusieurs millions de dollars, une performance EBITDA durable et une expansion des exportations scalable d'ici 2031 (Vision 2026-2031).</p>
                        </div>
                        <!-- Dégradé de fondu vers le blanc dans la partie basse du texte -->
                        <div class="home_presentation-fade-overlay position-absolute bottom-0 start-0 w-100" style="height: 90px; background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.95) 70%, #FFFFFF 100%); pointer-events: none;"></div>
                    </div>

                    <!-- Bouton Voir plus sous le texte -->
                    <div class="home_presentation-actions">
                        <a href="<?= base_url('About/presentation') ?>" class="btn home_btn-green-premium px-5 py-3 rounded-pill fw-semibold text-white shadow-sm d-inline-flex align-items-center gap-2" style="background: #0f4c3a; transition: all 0.3s ease;">
                            <span>Voir plus</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 2 : NOTRE VALEUR AJOUTÉE -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_section-value-addition home_py-100 bg-gray-light" style="background-color: #F8F9FA;">
    <div class="container home_container-1320">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;" data-aos="fade-up">
            <span class="home_badge-pill-green mb-3">Excellence & Distinction</span>
            <h2 class="home_section-heading-main fw-bold mb-3" style="color: #083D2A; font-family: 'Poppins', sans-serif; font-size: clamp(2rem, 3.5vw, 2.5rem);">
                Notre valeur ajoutée
            </h2>
            <p class="text-secondary fs-6">
                Pourquoi choisir notre plateforme de téléconsultation ?
            </p>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- Carte 1 -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="home_value-card-item bg-white p-4 home_rounded-25 home_shadow-hover h-100 d-flex flex-column home_border-custom">
                    <div class="home_value-icon-wrapper mb-4">
                        <div class="home_icon-circle-green d-flex align-items-center justify-content-center rounded-circle mx-auto" style="width: 72px; height: 72px; background: #EAF6EF; color: #0B5D3B;">
                            <i class="bi bi-clock-history fs-2"></i>
                        </div>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Consultations rapides</h4>
                        <p class="text-secondary small mb-0" style="line-height: 1.7;">
                            Consultations rapides disponibles 7 jours sur 7.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Carte 2 -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="home_value-card-item bg-white p-4 home_rounded-25 home_shadow-hover h-100 d-flex flex-column home_border-custom">
                    <div class="home_value-icon-wrapper mb-4">
                        <div class="home_icon-circle-green d-flex align-items-center justify-content-center rounded-circle mx-auto" style="width: 72px; height: 72px; background: #EAF6EF; color: #0B5D3B;">
                            <i class="bi bi-award fs-2"></i>
                        </div>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Professionnels de santé</h4>
                        <p class="text-secondary small mb-0" style="line-height: 1.7;">
                            Professionnels de santé qualifiés et certifiés.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Carte 3 -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="home_value-card-item bg-white p-4 home_rounded-25 home_shadow-hover h-100 d-flex flex-column home_border-custom">
                    <div class="home_value-icon-wrapper mb-4">
                        <div class="home_icon-circle-green d-flex align-items-center justify-content-center rounded-circle mx-auto" style="width: 72px; height: 72px; background: #EAF6EF; color: #0B5D3B;">
                            <i class="bi bi-shield-lock fs-2"></i>
                        </div>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Sécurité & Confidentialité</h4>
                        <p class="text-secondary small mb-0" style="line-height: 1.7;">
                            Plateforme 100 % sécurisée et confidentielle.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Carte 4 -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="home_value-card-item bg-white p-4 home_rounded-25 home_shadow-hover h-100 d-flex flex-column home_border-custom">
                    <div class="home_value-icon-wrapper mb-4">
                        <div class="home_icon-circle-green d-flex align-items-center justify-content-center rounded-circle mx-auto" style="width: 72px; height: 72px; background: #EAF6EF; color: #0B5D3B;">
                            <i class="bi bi-calendar-check fs-2"></i>
                        </div>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Prise de rendez-vous</h4>
                        <p class="text-secondary small mb-0" style="line-height: 1.7;">
                            Prise de rendez-vous facile en quelques clics.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 3 : TÉLÉCONSULTATION -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_section-teleconsultation home_py-100 bg-white">
    <div class="container home_container-1320">
        <div class="row align-items-center g-5">
            <!-- Texte à gauche -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="home_teleconsultation-content pe-lg-4">
                    <div class="home_badge-pill-green mb-3">
                        <span>Santé Connectée</span>
                    </div>
                    
                    <h2 class="home_section-heading-main fw-bold mb-3" style="color: #083D2A; font-family: 'Poppins', sans-serif; font-size: clamp(1.8rem, 3vw, 2.4rem); line-height: 1.25;">
                        Consultez un professionnel de santé, où que vous soyez.
                    </h2>
                    
                    <p class="home_text-green fw-semibold fs-6 mb-3">
                        Téléconsultation simple, rapide et sécurisée avec des agents qualifiés à votre service.
                    </p>

                    <p class="text-secondary fs-6 mb-4" style="line-height: 1.8;">
                        Accédez à des services de téléconsultation fiables et sécurisés en quelques clics. Notre plateforme vous met en relation avec des agents qualifiés et des professionnels de santé disponibles pour répondre rapidement et efficacement à vos besoins. Que vous ayez besoin d'un avis médical, d'un suivi ou de conseils, nous vous accompagnons à distance avec confidentialité et professionnalisme. Évitez les déplacements inutiles et les longues attentes : réservez votre rendez-vous en ligne et consultez depuis chez vous, à tout moment. Notre priorité est de vous offrir un service accessible, humain et sécurisé.
                    </p>

                    <div>
                        <a href="<?= base_url('doctor') ?>" class="btn home_btn-white-border-green px-5 py-3 rounded-pill fw-semibold shadow-sm text-white">
                            <i class="bi bi-calendar-plus home_text-green me-2" style="color:#fff"></i>Prendre rendez-vous
                        </a>
                    </div>
                </div>
            </div>

            <!-- Grande image à droite -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="home_teleconsultation-image-wrapper position-relative">
                    <?php
                    $img_sante = $this->Model->get_setting('image_sante_connectee', 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1000&q=80');
                    if (!empty($img_sante) && !preg_match('/^https?:\/\//i', $img_sante)) {
                        $img_sante = base_url((strpos($img_sante, '/') === false && strpos($img_sante, '\\') === false) ? 'attachments/Configurations/' . $img_sante : $img_sante);
                    }
                    ?>
                    <img src="<?= htmlspecialchars($img_sante) ?>" 
                         alt="Téléconsultation NUFOTEC" 
                         class="img-fluid home_rounded-24 home_shadow-xl w-100 object-fit-cover home_teleconsultation-image"
                         style="min-height: 320px; max-height: 550px;"
                         onerror="this.src='https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=1000&q=80'">
                    
                    <!-- Floating stat card -->
                    <div class="home_teleconsultation-floating-card bg-white p-4 home_rounded-20 shadow-lg">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-gold text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #0B5D3B;">
                                <i class="bi bi-star-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark fs-5">98.4%</span>
                                <span class="text-muted small">Satisfaction Patients</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>







<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 4 : CHIFFRES CLÉS -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_section-key-figures home_py-100 bg-green-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #083D2A 0%, #0B5D3B 100%);">
    <div class="container home_container-1320 position-relative home_z-index-2">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;" data-aos="fade-up">
            <span class="home_badge-tag-pill light mb-3">Impact & Envergure</span>
            <h2 class="home_section-heading-main fw-bold text-white mb-3" style="font-family: 'Poppins', sans-serif; font-size: clamp(2rem, 3.5vw, 2.5rem);">
                Chiffres Clés
            </h2>
            <p class="text-light opacity-8 fs-6">
                Notre croissance mesurée en chiffres témoigne de notre engagement indéfectible envers l'excellence et le développement.
            </p>
        </div>

        <div class="row g-4">
            <?php if (!empty($chiffres_cles)): ?>
                <?php foreach ($chiffres_cles as $i => $chiffre): ?>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= (($i % 4) + 1) * 100 ?>">
                        <div class="home_stat-card-premium home_bg-white-glass p-4 home_rounded-25 text-center h-100 d-flex flex-column justify-content-center home_border-glass" <?= !empty($chiffre['description']) ? 'title="' . htmlspecialchars($chiffre['description']) . '"' : '' ?>>
                            <div class="home_stat-icon-wrapper mb-3 home_text-gold" style="color: #2E9B6B;">
                                <i class="bi bi-<?= htmlspecialchars($chiffre['icone'] ?? 'star') ?> fs-1"></i>
                            </div>
                            <div class="home_stat-number display-4 fw-bold text-white mb-2" style="font-family: 'Poppins', sans-serif;">
                                <?= htmlspecialchars($chiffre['valeur'] ?? '') ?>
                            </div>
                            <?php if (!empty($chiffre['unite'])): ?>
                                <p class="home_stat-unit text-light opacity-8 small mb-1"><?= htmlspecialchars($chiffre['unite']) ?></p>
                            <?php endif; ?>
                            <p class="home_stat-unit text-light opacity-8 small mb-1">&nbsp;</p>
                            <p class="home_stat-label text-light opacity-9 small text-uppercase home_tracking-wider fw-semibold mb-0"><?= htmlspecialchars($chiffre['etiquette']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>






<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 5 : NOS APPELS À L'ACTION -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_section-cta-cards home_py-100 bg-white">
    <div class="container home_container-1320">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;" data-aos="fade-up">
            <span class="home_badge-pill-green mb-3">Rejoignez-nous</span>
            <h2 class="home_section-heading-main fw-bold mb-3" style="color: #083D2A; font-family: 'Poppins', sans-serif; font-size: clamp(2rem, 3.5vw, 2.5rem);">
                Nos Appels à l'Action
            </h2>
            <p class="text-secondary fs-6">
                Que vous soyez investisseur, courtier, acheteur ou patient, découvrez des opportunités et des services sur mesure adaptés à vos besoins.
            </p>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- 1. Investisseurs (Vert clair) -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="home_cta-card-premium p-4 home_rounded-25 h-100 d-flex flex-column home_border-custom home_shadow-hover" style="background-color: #EAF6EF;">
                    <div class="home_cta-icon-circle rounded-circle d-flex align-items-center justify-content-center mb-4 mx-auto shadow-xs" style="width: 72px; height: 72px; background: #FFFFFF; color: #0B5D3B;">
                        <i class="bi bi-briefcase fs-2"></i>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Investisseurs</h4>
                        <p class="text-secondary small mb-4" style="line-height: 1.7;">
                            Participez à un projet biotechnologique d'envergure internationale à fort rendement et impact durable.
                        </p>
                        <a href="<?= base_url('investor') ?>" class="btn home_btn-custom-green w-100 py-3 rounded-pill fw-semibold shadow-sm text-white" style="background-color: #0f4c3a;">
                            Investir <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Courtiers (Jaune clair) -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="home_cta-card-premium p-4 home_rounded-25 h-100 d-flex flex-column home_border-custom home_shadow-hover" style="background-color: #FDF8EC;">
                    <div class="home_cta-icon-circle rounded-circle d-flex align-items-center justify-content-center mb-4 mx-auto shadow-xs" style="width: 72px; height: 72px; background: #FFFFFF; color: #0B5D3B;">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Courtiers</h4>
                        <p class="text-secondary small mb-4" style="line-height: 1.7;">
                            Rejoignez notre réseau de partenaires et facilitez la mise en relation avec des investisseurs qualifiés.
                        </p>
                        <a href="<?= base_url('broker') ?>" class="btn home_btn-custom-yellow w-100 py-3 rounded-pill fw-semibold shadow-sm text-dark" style="background-color: #d4af37;">
                            Devenir courtier <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Acheteurs (Bleu clair) -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="home_cta-card-premium p-4 home_rounded-25 h-100 d-flex flex-column home_border-custom home_shadow-hover" style="background-color: #EBF3FA;">
                    <div class="home_cta-icon-circle rounded-circle d-flex align-items-center justify-content-center mb-4 mx-auto shadow-xs" style="width: 72px; height: 72px; background: #FFFFFF; color: #1E6091;">
                        <i class="bi bi-cart3 fs-2"></i>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Acheteurs</h4>
                        <p class="text-secondary small mb-4" style="line-height: 1.7;">
                            Commandez nos produits nutraceutiques, alimentaires et cosmétiques d'une qualité et pureté exceptionnelles.
                        </p>
                        <a href="<?= base_url('Products') ?>" class="btn home_btn-custom-blue w-100 py-3 rounded-pill fw-semibold shadow-sm text-white" style="background-color: #1E6091;">
                            Acheter <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. Patients (Rouge clair) -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="home_cta-card-premium p-4 home_rounded-25 h-100 d-flex flex-column home_border-custom home_shadow-hover" style="background-color: #FDF2F2;">
                    <div class="home_cta-icon-circle rounded-circle d-flex align-items-center justify-content-center mb-4 mx-auto shadow-xs" style="width: 72px; height: 72px; background: #FFFFFF; color: #C92A2A;">
                        <i class="bi bi-heart-pulse fs-2"></i>
                    </div>
                    <div class="text-center mt-auto">
                        <h4 class="fw-bold text-dark fs-5 mb-3">Patients</h4>
                        <p class="text-secondary small mb-4" style="line-height: 1.7;">
                            Bénéficiez de téléconsultations médicales professionnelles et d'un suivi de santé personnalisé et sécurisé.
                        </p>
                        <a href="<?= base_url('doctor') ?>" class="btn home_btn-custom-red w-100 py-3 rounded-pill fw-semibold shadow-sm text-white" style="background-color: #C92A2A;">
                            Se faire soigner <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 6 : NOS PRODUITS PREMIUM -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_section-products-premium home_py-100 bg-white">
    <div class="container home_container-1320">
        <div class="text-center mx-auto mb-5" data-aos="fade-up">
            <span class="home_badge-pill-green mb-3">NOS PRODUITS</span>
            <h2 class="home_section-heading-main fw-bold mb-3" style="color: #083D2A; font-family: 'Poppins', sans-serif; font-size: clamp(2rem, 3.5vw, 3.2rem); line-height: 1.2;">
                Découvrez nos produits naturels
            </h2>
            <div class="home_section-title-underline mx-auto"></div>
            <p class="text-secondary fs-6 mt-4" style="max-width: 700px; margin-left: auto; margin-right: auto;">
                Découvrez notre gamme de produits naturels développés à partir de plantes médicinales sélectionnées avec soin, conçus pour contribuer au bien-être et à une meilleure qualité de vie.
            </p>
        </div>

        <div class="row g-4">
            <?php if (!empty($produits)): 
                foreach ($produits as $produit):
                    $image_path = !empty($produit['main_image']) 
                        ? base_url('attachments/Products/' . $produit['main_image']) 
                        : base_url('attachments/Products/default-product.png');
                    $is_new = (strtotime($produit['created_at'] ?? 'now') > strtotime('-30 days'));
            ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="home_product-card-premium bg-white home_rounded-20 h-100 home_border-custom home_shadow-product">
                    <div class="home_product-image-wrapper text-center mb-3 position-relative">
                        <?php if ($is_new): ?>
                        <span class="home_product-badge-new bg-danger text-white home_fs-12 px-3 py-1 rounded-pill fw-bold position-absolute z-2 top-0 start-0 m-3">NOUVEAU</span>
                        <?php endif; ?>
                        <img src="<?= $image_path ?>" 
                             alt="<?= htmlspecialchars($produit['title']) ?>" 
                             class="img-fluid home_rounded-18 mx-auto d-block"
                             style="max-height: 200px; object-fit: contain; width: 100%; background: #F8F9FA;"
                             onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'">
                    </div>
                    <div class="home_product-info p-3">
                        <h4 class="fw-bold text-dark fs-6 mb-2" style="max-height: 2.5rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            <?= htmlspecialchars($produit['title']) ?>
                        </h4>
                        <p class="text-muted small mb-3" style="line-height: 1.6;">
                            <?= htmlspecialchars(substr(strip_tags($produit['description'] ?? ''), 0, 100)) ?><?= strlen(strip_tags($produit['description'] ?? '')) > 100 ? '...' : '' ?>
                        </p>
                        <div class="home_product-price mb-3">
                            <span class="fw-bold home_text-green fs-4" style="color: #083D2A; font-family: 'Poppins', sans-serif;"><?= htmlspecialchars($produit['price']) ?></span>
                        </div>
                        <a href="<?= base_url('Products/detail/' . ($produit['slug'] ?? $produit['id'])) ?>" class="btn home_btn-green-premium w-100 py-3 rounded-pill fw-semibold text-white shadow-sm">
                            <i class="bi bi-eye me-2"></i>Voir les détails
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">Aucun produit disponible pour le moment</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 7 : NEWSLETTER PREMIUM -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="home_section-newsletter-premium home_py-100 bg-white">
    <div class="container home_container-1320">
        <div class="home_newsletter-card-premium bg-white home_rounded-25 shadow-lg home_border-custom mx-auto overflow-hidden">
            <div class="row g-0">
                <!-- Illustration à gauche (desktop) -->
                <div class="col-lg-6 d-none d-lg-block home_newsletter-illustration-col" style="background: linear-gradient(135deg, #EAF6EF 0%, #FFFFFF 100%);">
                    <div class="home_newsletter-illustration d-flex align-items-center justify-content-center h-100 p-5">
                        <div class="text-center">
                            <div class="home_newsletter-illustration-icon mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#0B5D3B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <div class="home_newsletter-envelope-overlay">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#0B5D3B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16v16H4z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="fw-bold home_text-green" style="color: #0B5D3B;">Recevez nos actualités</h3>
                            <p class="text-secondary small mt-2">Ne manquez aucune de nos innovations</p>
                        </div>
                    </div>
                </div>
                
                <!-- Contenu à droite (ou center mobile) -->
                <div class="col-lg-6 home_newsletter-content-col d-flex flex-column justify-content-center p-4 p-lg-5">
                    <div class="home_newsletter-badge mb-3 d-none d-lg-block">
                        <span class="home_badge-pill-green">RESTEZ INFORMÉ</span>
                    </div>
                    
                    <div class="home_newsletter-badge mb-3 d-block d-lg-none text-center">
                        <span class="home_badge-pill-green">RESTEZ INFORMÉ</span>
                    </div>
                    
                    <h2 class="home_section-heading-main fw-bold mb-4" style="color: #083D2A; font-family: 'Poppins', sans-serif; font-size: clamp(1.8rem, 3vw, 2.4rem);">
                        Abonnez-vous à notre newsletter
                    </h2>
                    
                    <p class="text-secondary fs-6 mb-4" style="line-height: 1.7;">
                        Recevez nos nouveautés, nos conseils santé, nos offres exclusives et les dernières actualités de NUFOTEC directement dans votre boîte mail.
                    </p>

                    <form action="<?= base_url('Home/Abonner') ?>" method="post" class="home_newsletter-form">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                        <input type="hidden" name="sub_type" value="email">
                        
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control py-3 px-4 home_rounded-18 home_border-custom fw-medium text-dark" 
                                   placeholder="Votre adresse email" required style="background: #F8F9FA;">
                        </div>
                        
                        <div class="mb-4">
                            <input type="tel" name="telephone" class="form-control py-3 px-4 home_rounded-18 home_border-custom fw-medium text-dark" 
                                   placeholder="Votre numéro de téléphone (optionnel)" style="background: #F8F9FA;">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn home_btn-green-premium w-100 py-3 home_rounded-18 fw-semibold text-white shadow-sm">
                                Je m'abonne
                            </button>
                        </div>
                    </form>

                    <div class="home_newsletter-security mt-4 text-center">
                        <p class="text-secondary small mb-0">
                            <i class="bi bi-shield-lock home_text-green me-1"></i>🔒 Vos informations restent confidentielles. Vous pourrez vous désabonner à tout moment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
/* ═════════════════════════════════════════════════════════════════
   HOME VIEW — DESIGN PREMIUM BRILLANT (mobile-first, spécifique home)
   Toutes les classes sont préfixées `home_` pour éviter tout conflit.
   ═════════════════════════════════════════════════════════════════ */

/* ---------- 1. TOKENS ---------- */
.home_page {
  --green: #0B5D3B;
  --green-dark: #083D2A;
  --green-soft: #EAF6EF;
  --gold: #0B5D3B;
  --gold-light: #2E9B6B;
  --text: #1F2A26;
  --text-soft: #55655F;
  --bg-soft: #F4F7F5;
  --blue: #1E6091;
  --red: #C92A2A;
  --white: #FFFFFF;

  --radius-sm: 14px;
  --radius-md: 20px;
  --radius-lg: 28px;

  --shadow-sm: 0 2px 10px rgba(8,61,42,.06);
  --shadow-md: 0 14px 34px rgba(8,61,42,.12);
  --shadow-lg: 0 30px 60px rgba(8,61,42,.18);
  --glow-green: 0 0 0 1px rgba(11,93,59,.08), 0 18px 50px rgba(11,93,59,.20);

  --ease: cubic-bezier(.22,1,.36,1);
  --tap: 48px;
}

/* ---------- 2. LAYOUT UTILITAIRES ---------- */
.home_container-1320 { max-width: 1320px; }

.home_py-100 { padding-block: 28px !important; }
@media (min-width: 768px) { .home_py-100 { padding-block: 40px !important; } }
@media (min-width: 992px) { .home_py-100 { padding-block: 52px !important; } }

/* Le hero colle au header : on supprime le padding-top global du body (header fixe)
   appliqué uniquement sur la page d'accueil. */
body { padding-top: 0 !important; }

/* ---------- TYPOGRAPHIE (police de caractères) ---------- */
body {
  font-family: "Inter", "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
}
/* Tout le texte en Inter (sans-serif moderne, cohérent) */
.home_page h1, .home_page h2, .home_page h3, .home_page h4,
.home_section-heading-main,
.home_presentation-title,
.home_hero-main-title,
.home_newsletter-content-col h2,
.home_page p, .home_page li, .home_page span, .home_page small,
.home_presentation-subtitle, .home_presentation-paragraphs,
.home_value-card-item p, .home_cta-card-premium p,
.home_product-info p, .home_newsletter-content-col > p {
  font-family: "Inter", "Segoe UI", system-ui, sans-serif;
  font-weight: 600;
  letter-spacing: -0.01em;
  line-height: 1.5;
}

.home_rounded-18 { border-radius: var(--radius-sm) !important; }
.home_rounded-20 { border-radius: var(--radius-md) !important; }
.home_rounded-24 { border-radius: var(--radius-lg) !important; }
.home_rounded-25 { border-radius: 26px !important; }

.home_shadow-xl { box-shadow: var(--shadow-lg) !important; }
.home_shadow-hover { transition: transform .4s var(--ease), box-shadow .4s var(--ease); }
.home_shadow-hover:hover { transform: translateY(-8px); box-shadow: var(--glow-green) !important; }
.home_shadow-product { box-shadow: var(--shadow-sm); }

.home_border-custom { border: 1px solid rgba(11,93,59,.10) !important; }
.home_border-glass { border: 1px solid rgba(255,255,255,.22) !important; }
.home_z-index-2 { position: relative; z-index: 2; }
.home_fs-12 { font-size: .75rem; }
.home_tracking-wider { letter-spacing: .08em; }
.home_text-green { color: var(--green) !important; }
.home_text-gold { color: var(--gold) !important; }

/* ---------- 3. TYPOGRAPHIE ---------- */
.home_page h1, .home_page h2, .home_page h3, .home_page h4 {
  font-family: "Inter", "Segoe UI", system-ui, sans-serif;
  letter-spacing: -0.025em;
}
.home_section-heading-main {
  text-align: center;
  font-weight: 800;
  font-size: clamp(1.6rem, 4.5vw, 2.6rem);
  color: var(--green-dark) !important;
  line-height: 1.12;
  letter-spacing: -0.03em;
  margin-bottom: 12px;
}
.home_section-title-underline {
  position: relative;
  width: 72px; height: 4px;
  border-radius: 50px;
  margin: 0 auto 0;
  background: linear-gradient(90deg, var(--green), var(--gold));
  box-shadow: 0 4px 14px rgba(11,93,59,.4);
}

.home_badge-pill-green,
.home_badge-tag-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 18px;
  border-radius: 100px;
  font-size: .72rem;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
  background: linear-gradient(135deg, var(--green-soft), #fff);
  color: var(--green);
  border: 1px solid rgba(11,93,59,.12);
  box-shadow: 0 6px 16px rgba(11,93,59,.08);
}
.home_badge-tag-pill { background: rgba(255,255,255,.16); color: #fff; border-color: rgba(255,255,255,.25); }

/* ---------- 4. BOUTONS ---------- */
.home_btn-green-premium,
.home_btn-gold-premium,
.home_btn-outline-light-custom,
.home_btn-white-border-green,
.home_btn-custom-green, .home_btn-custom-yellow, .home_btn-custom-blue, .home_btn-custom-red {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: var(--tap);
  padding: 13px 26px;
  border-radius: 100px;
  font-weight: 700;
  font-size: .92rem;
  border: none;
  overflow: hidden;
  isolation: isolate;
  transition: transform .3s var(--ease), box-shadow .3s var(--ease), filter .3s var(--ease);
  white-space: nowrap;
}
/* reflet brillant balayé au survol */
.home_btn-green-premium::after,
.home_btn-gold-premium::after,
.home_btn-custom-green::after, .home_btn-custom-yellow::after,
.home_btn-custom-blue::after, .home_btn-custom-red::after {
  content: "";
  position: absolute;
  top: 0; left: -75%;
  width: 55%; height: 100%;
  background: linear-gradient(105deg, transparent, rgba(255,255,255,.45), transparent);
  transform: skewX(-20deg);
  z-index: 1;
  transition: left .6s var(--ease);
}
.home_btn-green-premium:hover::after,
.home_btn-gold-premium:hover::after,
.home_btn-custom-green:hover::after, .home_btn-custom-yellow:hover::after,
.home_btn-custom-blue:hover::after, .home_btn-custom-red:hover::after { left: 125%; }

.home_btn-green-premium { background: linear-gradient(135deg, #0f4c3a, #1a5f4a); color: #fff; box-shadow: 0 0 22px rgba(15,76,58,.45), 0 10px 24px rgba(15,76,58,.32); }
.home_btn-green-premium:hover { transform: translateY(-3px); box-shadow: 0 0 30px rgba(15,76,58,.55), 0 16px 34px rgba(15,76,58,.4); }

.home_btn-gold-premium { background: #b8941f; color: #0a3326; box-shadow: 0 0 28px rgba(184,148,31,.85), 0 18px 45px rgba(184,148,31,.85), 0 8px 18px rgba(0,0,0,.25); }
.home_btn-gold-premium:hover { background: #d5c067; color: #0a3326; transform: translateY(-3px); box-shadow: 0 0 36px rgba(213,192,103,.95), 0 30px 65px rgba(213,192,103,.95), 0 12px 26px rgba(0,0,0,.3); }

.home_btn-outline-light-custom { background: #ffffff; color: #0a3326; box-shadow: 0 0 22px rgba(255,255,255,.35), 0 10px 24px rgba(0,0,0,.2); }
.home_btn-outline-light-custom:hover { background: #eaf6ef; color: #0a3326; transform: translateY(-3px); box-shadow: 0 0 30px rgba(255,255,255,.45), 0 16px 34px rgba(0,0,0,.25); }

.home_btn-white-border-green { background: linear-gradient(135deg, #0f4c3a, #1a5f4a); color: #fff; border: none; box-shadow: 0 0 22px rgba(15,76,58,.45), 0 10px 24px rgba(15,76,58,.35); }
.home_btn-white-border-green:hover { background: linear-gradient(135deg, #0a3326, #0f4c3a); color: #fff; transform: translateY(-3px); box-shadow: 0 0 30px rgba(15,76,58,.55), 0 14px 30px rgba(15,76,58,.4); }

.home_btn-custom-green { background: linear-gradient(135deg, #0f4c3a, #1a5f4a); color: #fff; box-shadow: 0 0 20px rgba(15,76,58,.4), 0 8px 20px rgba(15,76,58,.35); }
.home_btn-custom-yellow { background: linear-gradient(135deg, #d4af37, #b8941f); color: #0a3326; box-shadow: 0 0 20px rgba(212,175,55,.45), 0 8px 20px rgba(212,175,55,.4); }
.home_btn-custom-blue { background: linear-gradient(135deg, #1E6091, #2B7CB8); color: #fff; box-shadow: 0 0 20px rgba(30,96,145,.4), 0 8px 20px rgba(30,96,145,.35); }
.home_btn-custom-red { background: linear-gradient(135deg, #C92A2A, #E05252); color: #fff; box-shadow: 0 0 20px rgba(201,42,42,.4), 0 8px 20px rgba(201,42,42,.35); }
.home_btn-custom-green:hover, .home_btn-custom-yellow:hover,
.home_btn-custom-blue:hover, .home_btn-custom-red:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

/* ---------- 5. HERO ---------- */
.home_hero-section-premium {
  position: relative;
  min-height: 64vh;
  display: flex;
  align-items: center;
  padding-block: 60px 48px;
  margin-top: 0;
  background:
    linear-gradient(160deg, rgba(8,61,42,.94), rgba(11,93,59,.78) 55%, rgba(8,61,42,.96)),
    var(--hero-home-bg) center/cover no-repeat;
  color: #fff;
  overflow: hidden;
  isolation: isolate;
}
.home_hero-section-premium::before {
  content: "";
  position: absolute; inset: 0;
  background:
    radial-gradient(circle at 18% 20%, rgba(11,93,59,.22), transparent 42%),
    radial-gradient(circle at 85% 12%, rgba(15,76,58,.35), transparent 45%);
  z-index: 0;
  pointer-events: none;
}
.home_hero-bg-overlay { position: absolute; inset: 0; z-index: 0; pointer-events: none; }
.home_hero-container { position: relative; z-index: 1; }
.home_hero-section-premium h1, .home_hero-section-premium p, .home_hero-section-premium .home_hero-cta-group { position: relative; z-index: 1; }

.home_hero-badge-logo img { max-width: 128px; height: auto; margin: 0 auto; filter: drop-shadow(0 6px 16px rgba(0,0,0,.3)); }
.home_hero-main-title {
  font-weight: 800;
  font-size: clamp(2rem, 5.5vw, 3.6rem);
  line-height: 1.08;
  letter-spacing: -0.03em;
  max-width: 760px; margin-inline: auto;
  background: linear-gradient(180deg, #fff, #EAF6EF);
  -webkit-background-clip: text; background-clip: text;
  -webkit-text-fill-color: transparent;
}
.home_hero-lead-text { font-size: clamp(.98rem, 2vw, 1.2rem); color: rgba(255,255,255,.88); max-width: 640px; margin-inline: auto; }
.home_hero-cta-group { flex-wrap: wrap; }

@media (max-width: 575.98px) {
  .home_hero-section-premium { min-height: 56vh; padding-block: 48px 36px; }
  .home_hero-cta-group .home_btn-gold-premium,
  .home_hero-cta-group .home_btn-outline-light-custom { width: 100%; }
}

/* ---------- 6. SECTION 1 : PRÉSENTATION ---------- */
.home_section-presentation { background: var(--white); }
.home_presentation-row { align-items: center; }
.home_presentation-col { display: flex; }

.home_presentation-image-card {
  position: relative;
  width: 100%;
  border-radius: var(--radius-lg);
  box-shadow: var(--glow-green);
  overflow: hidden;
}
.home_presentation-image-inner { border-radius: 24px; overflow: hidden; aspect-ratio: 4/3.3; }
.home_presentation-image-inner img { width: 100%; height: 100%; object-fit: cover; }

.home_presentation-iso-badge {
  position: absolute;
  bottom: -18px; left: 18px;
  background: rgba(255,255,255,.92);
  backdrop-filter: blur(8px);
  border-radius: var(--radius-sm);
  padding: 12px 18px;
  box-shadow: var(--shadow-md);
  border: 1px solid rgba(11,93,59,.08);
}

.home_presentation-content { display: flex; flex-direction: column; align-items: flex-start; height: 100%; }
.home_presentation-badge { margin-bottom: 14px; }
.home_presentation-title {
  font-weight: 800;
  font-size: clamp(1.45rem, 3.6vw, 2.2rem);
  color: var(--green-dark);
  margin-bottom: 10px;
  line-height: 1.22;
}
.home_presentation-subtitle {
  color: var(--gold);
  font-weight: 700;
  font-size: .8rem;
  text-transform: uppercase;
  letter-spacing: .07em;
  margin-bottom: 16px;
}
.home_presentation-text-clamp {
  position: relative;
  color: var(--text-soft);
  font-size: .95rem;
  line-height: 1.72;
  max-height: 12.5em;
  overflow: hidden;
}
.home_presentation-paragraphs p { margin-bottom: 14px; }
.home_presentation-paragraphs .home_presentation-closing { margin-bottom: 0; color: var(--green); font-weight: 600; }
.home_presentation-fade-overlay {
  position: absolute; left: 0; right: 0; bottom: 0; height: 60px;
  background: linear-gradient(180deg, rgba(255,255,255,0), #fff);
  pointer-events: none;
}
.home_presentation-actions { margin-top: 22px; }

@media (max-width: 991.98px) { .home_presentation-image-card { margin-bottom: 42px; } }
@media (max-width: 575.98px) {
  .home_presentation-image-inner { aspect-ratio: 4/3; }
  .home_presentation-actions .home_btn-green-premium { width: 100%; }
}

/* ---------- 7. SECTION 2 : VALEUR AJOUTÉE ---------- */
.home_section-value-addition { background: var(--bg-soft); }
/* Espace de respiration entre la grille de cartes et la section suivante */
.home_section-value-addition .row { margin-bottom: 0 !important; }
.home_value-card-item { margin-bottom: 8px !important; }
.home_section-value-addition { padding-bottom: 44px !important; }
@media (max-width: 575.98px) { .home_section-value-addition { padding-bottom: 32px !important; } }
.home_value-card-item {
  position: relative;
  background: #fff;
  border-radius: var(--radius-md);
  padding: 30px 22px;
  height: 100%;
  border: 1px solid rgba(11,93,59,.06);
  box-shadow: var(--shadow-sm);
  transition: transform .4s var(--ease), box-shadow .4s var(--ease);
  overflow: hidden;
}
.home_value-card-item::before {
  content: "";
  position: absolute; top: 0; left: 0; right: 0; height: 4px;
  background: linear-gradient(90deg, var(--green), var(--gold));
  opacity: 0;
  transition: opacity .4s var(--ease);
}
.home_value-card-item:hover::before { opacity: 1; }
.home_value-card-item:hover { transform: translateY(-8px); box-shadow: var(--glow-green); }
.home_value-icon-wrapper { margin-bottom: 18px; }
.home_icon-circle-green {
  width: 64px; height: 64px;
  border-radius: 18px;
  background: linear-gradient(135deg, var(--green-soft), #fff);
  color: var(--green);
  border: 1px solid rgba(11,93,59,.10);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem;
  box-shadow: 0 8px 20px rgba(11,93,59,.10);
}
.home_value-card-item h4 { font-weight: 700; color: var(--green-dark); margin-bottom: 8px; font-size: 1.05rem; }
.home_value-card-item p { font-size: .88rem; color: var(--text-soft); line-height: 1.6; margin-bottom: 0; }

/* ---------- 8. SECTION 3 : TÉLÉCONSULTATION ---------- */
.home_section-teleconsultation { background: #fff; overflow: hidden; }
.home_teleconsultation-content h2 { margin-bottom: 12px; }
.home_teleconsultation-content p.text-green { margin-bottom: 12px; }
.home_teleconsultation-content p.text-secondary { margin-bottom: 20px; line-height: 1.75; }
.home_teleconsultation-image-wrapper { position: relative; }
.home_teleconsultation-image {
  width: 100%;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  object-fit: cover;
  aspect-ratio: 4/3.3;
}
.home_teleconsultation-floating-card {
  position: absolute;
  bottom: -20px; left: 20px;
  background: rgba(255,255,255,.95);
  backdrop-filter: blur(8px);
  border-radius: var(--radius-sm);
  padding: 14px 18px;
  box-shadow: var(--shadow-md);
  border: 1px solid rgba(11,93,59,.08);
  display: flex; gap: 12px; align-items: center;
}
.home_teleconsultation-floating-card .fw-bold { color: var(--green-dark); font-size: .95rem; }
.home_teleconsultation-floating-card small { color: var(--text-soft); }
@media (max-width: 575.98px) {
  .home_teleconsultation-image { aspect-ratio: 4/3; }
  .home_teleconsultation-floating-card { left: 14px; bottom: -14px; }
}

/* ---------- 9. SECTION 4 : CHIFFRES CLÉS ---------- */
.home_section-key-figures {
  background: linear-gradient(135deg, var(--green-dark), var(--green) 55%, #0F7A4E);
  position: relative;
  overflow: hidden;
}
.home_section-key-figures::before {
  content: "";
  position: absolute; inset: 0;
  background:
    radial-gradient(circle at 12% 88%, rgba(11,93,59,.22), transparent 45%),
    radial-gradient(circle at 88% 12%, rgba(247,201,72,.14), transparent 40%);
  pointer-events: none;
}
.home_section-key-figures .home_section-heading-main,
.home_section-key-figures .home_badge-tag-pill { color: #fff !important; -webkit-text-fill-color: #fff; }
.home_stat-card-premium,
.home_bg-white-glass {
  position: relative;
  z-index: 1;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.18);
  border-radius: var(--radius-md);
  padding: 30px 16px;
  text-align: center;
  height: 100%;
  backdrop-filter: blur(8px);
  transition: background .4s var(--ease), transform .4s var(--ease);
}
.home_stat-card-premium:hover, .home_bg-white-glass:hover { background: rgba(255,255,255,.16); transform: translateY(-6px); }
.home_stat-icon-wrapper { margin-bottom: 12px; color: var(--gold-light); font-size: 2rem; filter: drop-shadow(0 6px 12px rgba(11,93,59,.5)); }
.home_stat-number {
  font-weight: 800;
  font-size: clamp(2rem, 5vw, 2.8rem);
  color: #fff;
  line-height: 1;
  text-shadow: 0 6px 24px rgba(0,0,0,.25);
}
.home_stat-unit { font-size: 1.05rem; color: var(--gold-light); font-weight: 700; margin-bottom: 6px; }
.home_stat-label { margin-top: 8px; font-size: .8rem; color: rgba(255,255,255,.82); text-transform: uppercase; letter-spacing: .07em; }

/* ---------- 10. SECTION 5 : CTA ---------- */
.home_section-cta-cards { background: var(--white); }
.home_cta-card-premium {
  position: relative;
  border-radius: var(--radius-md);
  padding: 32px 22px;
  height: 100%;
  text-align: center;
  border: 1px solid rgba(11,93,59,.06);
  box-shadow: var(--shadow-sm);
  transition: transform .4s var(--ease), box-shadow .4s var(--ease);
  overflow: hidden;
}
.home_cta-card-premium::before {
  content: "";
  position: absolute; top: 0; left: 0; right: 0; height: 4px;
  background: linear-gradient(90deg, var(--green), var(--gold));
  opacity: 0;
  transition: opacity .4s var(--ease);
}
.home_cta-card-premium:hover::before { opacity: 1; }
.home_cta-card-premium:hover { transform: translateY(-8px); box-shadow: var(--glow-green); }
.home_cta-icon-circle {
  width: 68px; height: 68px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.8rem;
  margin: 0 auto 18px;
  color: #fff;
  box-shadow: 0 12px 26px rgba(0,0,0,.16);
}
.home_cta-card-premium:nth-child(1) .home_cta-icon-circle { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
.home_cta-card-premium:nth-child(2) .home_cta-icon-circle { background: linear-gradient(135deg, var(--green), var(--green-dark)); color: #fff; }
.home_cta-card-premium:nth-child(3) .home_cta-icon-circle { background: linear-gradient(135deg, var(--blue), #2B7CB8); }
.home_cta-card-premium:nth-child(4) .home_cta-icon-circle { background: linear-gradient(135deg, var(--red), #E05252); }
.home_cta-card-premium h4 { font-weight: 700; color: var(--green-dark); margin-bottom: 8px; font-size: 1.05rem; }
.home_cta-card-premium p { font-size: .88rem; color: var(--text-soft); line-height: 1.65; margin-bottom: 20px; }
.home_cta-card-premium .home_btn-custom-green, .home_cta-card-premium .home_btn-custom-yellow,
.home_cta-card-premium .home_btn-custom-blue, .home_cta-card-premium .home_btn-custom-red { width: 100%; }

/* ---------- 11. SECTION 6 : PRODUITS ---------- */
.home_section-products-premium { background: var(--bg-soft); }
.home_product-card-premium {
  background: #fff;
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  border: 1px solid rgba(11,93,59,.07);
  height: 100%;
  transition: transform .4s var(--ease), box-shadow .4s var(--ease);
}
.home_product-card-premium:hover { transform: translateY(-8px); box-shadow: var(--glow-green); }
.home_product-image-wrapper {
  position: relative;
  aspect-ratio: 1/0.85;
  overflow: hidden;
  background: var(--green-soft);
}
.home_product-image-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s var(--ease); }
.home_product-card-premium:hover .home_product-image-wrapper img { transform: scale(1.07); }
.home_product-badge-new {
  position: absolute; top: 12px; left: 12px;
  background: linear-gradient(135deg, var(--green), var(--green-dark));
  color: #fff;
  font-size: .68rem; font-weight: 800; letter-spacing: .04em;
  padding: 5px 10px; border-radius: 100px; text-transform: uppercase;
  box-shadow: 0 6px 16px rgba(11,93,59,.4);
  z-index: 2;
}
.home_product-info { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
.home_product-info h4 { font-size: .98rem; font-weight: 700; color: var(--green-dark); margin-bottom: 6px; }
.home_product-info p { font-size: .85rem; color: var(--text-soft); line-height: 1.6; margin-bottom: 10px; }
.home_product-price { font-weight: 800; font-size: 1.15rem; color: var(--green); margin-block: 8px 14px; }
.home_product-info .home_btn-green-premium { width: 100%; margin-top: auto; }

/* ---------- 12. SECTION 7 : NEWSLETTER ---------- */
.home_section-newsletter-premium { background: var(--bg-soft); }
.home_newsletter-card-premium {
  background: linear-gradient(135deg, var(--green-dark), var(--green) 60%, #0F7A4E);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-lg);
  display: flex;
  flex-direction: column;
}
.home_newsletter-content-col {
  padding: 36px 26px;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.home_newsletter-content-col h2 { color: #fff; font-weight: 800; margin-bottom: 12px; }
.home_newsletter-content-col > p { color: rgba(255,255,255,.84); font-size: .92rem; line-height: 1.7; margin-bottom: 18px; }
.home_newsletter-content-col .home_badge-pill-green { background: rgba(255,255,255,.14); color: var(--gold-light); border-color: rgba(255,255,255,.25); }

.home_newsletter-form {
  background: #fff;
  padding: 30px 26px;
  border-radius: var(--radius-md);
  margin: 0 18px 18px;
  box-shadow: var(--shadow-md);
}
.home_newsletter-form input {
  width: 100%;
  min-height: var(--tap);
  padding: 13px 18px;
  border-radius: 100px;
  border: 1.5px solid #E1E7E3;
  margin-bottom: 12px;
  font-size: .92rem;
  transition: border-color .2s var(--ease), box-shadow .2s var(--ease);
}
.home_newsletter-form input:focus { border-color: var(--green); box-shadow: 0 0 0 4px rgba(11,93,59,.10); outline: none; }
.home_newsletter-form .home_btn-green-premium { width: 100%; }

@media (min-width: 768px) {
  .home_newsletter-card-premium { flex-direction: row; align-items: stretch; }
  .home_newsletter-content-col { flex: 1 1 42%; padding: 52px; text-align: left; }
  .home_newsletter-form { flex: 1 1 58%; margin: 18px; align-self: center; }
}
@media (max-width: 575.98px) {
  .home_newsletter-content-col { padding: 26px 20px; }
  .home_newsletter-form { margin: 0 12px 12px; padding: 24px 18px; }
  .home_newsletter-form input { font-size: 16px; }
}

.home_section-value-addition .text-center.mb-5,
  .home_section-cta-cards .text-center.mb-5,
  .home_section-products-premium .text-center.mb-5,
  .home_section-key-figures .text-center.mb-5 { margin-bottom: 16px !important; }
@media (max-width: 575.98px) {
  .home_section-value-addition .text-center.mb-5,
  .home_section-cta-cards .text-center.mb-5,
  .home_section-products-premium .text-center.mb-5,
  .home_section-key-figures .text-center.mb-5 { margin-bottom: 14px !important; }
}
/* Grilles de cartes : espace garanti sous la dernière rangée (évite que les
   cartes touchent la section suivante) */
.home_section-value-addition .row.g-4,
.home_section-cta-cards .row.g-4,
.home_section-products-premium .row.g-4,
.home_section-key-figures .row.g-4 { margin-bottom: 0 !important; }
.home_value-card-item,
.home_cta-card-premium,
.home_product-card-premium,
.home_stat-card-premium,
.home_bg-white-glass { margin-bottom: 12px !important; }
@media (max-width: 991.98px) {
  .home_value-card-item, .home_cta-card-premium,
  .home_product-card-premium, .home_stat-card-premium, .home_bg-white-glass { margin-bottom: 16px !important; }
}
@media (max-width: 575.98px) {
  .home_container-1320 { padding-inline: 16px; }
  .home_stat-card-premium, .home_bg-white-glass, .home_cta-card-premium, .home_value-card-item { padding: 22px 16px; }
  .home_section-heading-main { font-size: 1.55rem; }
}
@media (min-width: 1200px) { .home_container-1320 { padding-inline: 0; } }
/* Desktop : le hero doit dégager le header fixe (hauteur totale 156px). Mobile inchangé. */
@media (min-width: 992px) {
  .home_hero-section-premium { margin-top: 156px; min-height: calc(64vh - 60px); }
}
</style>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
