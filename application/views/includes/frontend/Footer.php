<script>
    /**
     * LOADING SPINNER NUFOTEC
     * Affiche le spinner pendant 1 seconde puis le masque avec animation
     */
    (function() {
        'use strict';
        
        var loader = document.getElementById("loadingSpinner");
        
        if (!loader) return;

        window.addEventListener("load", function() {
            setTimeout(function() {
                loader.classList.add("loader-hidden");
                setTimeout(function() {
                    loader.remove();
                    document.body.style.overflow = '';
                }, 400);
            }, 1000);
        });
        
        if (document.readyState === 'complete') {
            window.dispatchEvent(new Event('load'));
        }
    })();
</script>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- MODERN FOOTER - 3 COLUMNS ONLY -->
<!-- ═══════════════════════════════════════════════════════ -->
<footer class="site-footer" id="footer">
    <div class="footer-container">
        
        <!-- Main Section -->
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    
                    <!-- Column 1: Brand & Quick Contact -->
                    <div class="footer-col footer-col-brand">
                        <div class="footer-brand">
                            <?php 
                            $site_logo = $this->Model->get_setting('site_logo');
                            if (!empty($site_logo)): 
                            ?>
                            <img src="<?= base_url('attachments/Configurations/' . $site_logo) ?>" 
                                 alt="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'), ENT_QUOTES, 'UTF-8') ?>" 
                                 class="footer-logo">
                            <?php endif; ?>
                            <div class="brand-info">
                                <h3><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'), ENT_QUOTES, 'UTF-8') ?></h3>
                                <span>Innovation & Technologie</span>
                            </div>
                        </div>

                        <p class="footer-desc">
                            <?= htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Plateforme de téléconsultation médicale et de produits phytomédicinaux'), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <!-- Quick Contact -->
                        <div class="footer-quick-contact">
                            <a href="tel:<?= htmlspecialchars($this->Model->get_setting('site_phone', '+257 79 666 439'), ENT_QUOTES, 'UTF-8') ?>" class="quick-contact-item">
                                <i class="bi bi-telephone-fill"></i>
                                <span><?= htmlspecialchars($this->Model->get_setting('site_phone', '+257 79 666 439'), ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                            <a href="mailto:<?= htmlspecialchars($this->Model->get_setting('contact_email_invest', 'nufotecburundi@gmail.com'), ENT_QUOTES, 'UTF-8') ?>" class="quick-contact-item">
                                <i class="bi bi-envelope-fill"></i>
                                <span><?= htmlspecialchars($this->Model->get_setting('contact_email_invest', 'nufotecburundi@gmail.com'), ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                            <a href="#" class="quick-contact-item" onclick="openMap(); return false;">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span><?= htmlspecialchars($this->Model->get_setting('adresse_siege', 'Bujumbura, Burundi'), ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                        </div>

                        <!-- Social Links -->
                        <div class="footer-social">
    <?php
    $CI =& get_instance();
    $social_links = $CI->db->query("
        SELECT * FROM social_links 
        WHERE is_active = 1 
        ORDER BY display_order ASC
    ")->result_array();
    ?>
    
    <?php foreach($social_links as $social): ?>
        <a href="<?= htmlspecialchars($social['url']) ?>" 
           class="social-link" 
           aria-label="<?= htmlspecialchars($social['label']) ?>"
           target="_blank" 
           rel="noopener noreferrer">
            <i class="bi bi-<?= $social['icon_name'] ?>"></i>
        </a>
    <?php endforeach; ?>
</div>
                    </div>

                    <!-- Column 2: Navigation -->
                    <div class="footer-col footer-col-nav">
                        <button class="footer-accordion-toggle d-lg-none" aria-expanded="false" aria-controls="footerNav1">
                            <h4>Navigation</h4>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <h4 class="d-none d-lg-block">Navigation</h4>
                        
                        <div class="footer-accordion-content" id="footerNav1">
                            <ul class="footer-links">
                                <li><a href="<?= base_url() ?>">Accueil</a></li>
                                <li><a href="<?= base_url('a-propos') ?>">À propos</a></li>
                                <li><a href="<?= base_url('investissement') ?>">Investissement</a></li>
                                <li><a href="<?= base_url('Home/Media') ?>">Médias</a></li>
                                <li><a href="<?= base_url('Home/Contact') ?>">Contact</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 3: Our Services -->
                    <div class="footer-col footer-col-services">
                        <button class="footer-accordion-toggle d-lg-none" aria-expanded="false" aria-controls="footerNav2">
                            <h4>Nos services</h4>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <h4 class="d-none d-lg-block">Nos services</h4>
                        
                        <div class="footer-accordion-content" id="footerNav2">
                            <ul class="footer-links">
                                <li>
                                    <a href="<?= base_url('Medicins') ?>">
                                        <i class="bi bi-heart-pulse"></i>
                                        Consultation médicale
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('Products') ?>">
                                        <i class="bi bi-shop"></i>
                                        Vente de produits
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('Investors-form') ?>">
                                        <i class="bi bi-graph-up-arrow"></i>
                                        Investir
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    
                    <!-- Copyright -->
                    <div class="footer-copyright">
                        <p>&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'), ENT_QUOTES, 'UTF-8') ?></strong>. Tous droits réservés.</p>
                    </div>

                    <!-- Legal Links -->
                    <div class="footer-legal">
                        <p>Conçu par Dushime Paul : dushimeyesupaulin@gmail.com</p>
                    </div>

                    <!-- Back to Top -->
                    <button class="back-to-top" onclick="scrollToTop()" aria-label="Retour en haut">
                        <i class="bi bi-arrow-up"></i>
                    </button>

                </div>
            </div>
        </div>

    </div>
</footer>


<!-- Footer Styles -->
<style>
/* ============================================
   MODERN FOOTER - 3 COLUMNS
   ============================================ */
:root {
    --footer-bg: #0a3326;
    --footer-bg-light: #0f4c3a;
    --footer-text: rgba(255, 255, 255, 0.8);
    --footer-text-muted: rgba(255, 255, 255, 0.6);
    --footer-border: rgba(255, 255, 255, 0.1);
    --footer-accent: #d4af37;
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
}

.site-footer {
    background: linear-gradient(135deg, var(--footer-bg-light) 0%, var(--footer-bg) 100%);
    color: var(--footer-text);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    position: relative;
    margin-top: auto;
}

.sticky-avatar-mini,
.avatar-placeholder-mini {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--primary);
    color: white;
    font-size: 12px;
    font-weight: bold;
}
.sticky-avatar-mini img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

/* Decorative top line */
.site-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--footer-accent), #f4d03f, var(--footer-accent));
    background-size: 200% 100%;
    animation: gradientMove 3s linear infinite;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

.footer-container {
    position: relative;
}

/* ============================================
   FOOTER MAIN - 3 Columns Grid
   ============================================ */
.footer-main {
    padding: 60px 0 40px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 60px;
    align-items: start;
}

/* ============================================
   COLUMN 1: BRAND
   ============================================ */
.footer-col-brand {
    max-width: 400px;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.footer-logo {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: white;
    padding: 4px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}

.footer-brand:hover .footer-logo {
    transform: scale(1.05) rotate(3deg);
}

.brand-info h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: white;
    margin: 0;
    line-height: 1.2;
}

.brand-info span {
    font-size: 11px;
    color: var(--footer-accent);
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 600;
}

.footer-desc {
    font-size: 0.95rem;
    line-height: 1.7;
    margin-bottom: 24px;
    color: var(--footer-text-muted);
}

/* Quick Contact */
.footer-quick-contact {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.quick-contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--footer-text);
    text-decoration: none;
    font-size: 0.9rem;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    border: 1px solid var(--footer-border);
    transition: all 0.3s ease;
}

.quick-contact-item:hover {
    background: rgba(212, 175, 55, 0.15);
    border-color: var(--footer-accent);
    color: white;
    transform: translateX(5px);
}

.quick-contact-item i {
    color: var(--footer-accent);
    font-size: 16px;
}

/* Social Links */
.footer-social {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.social-link {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    text-decoration: none;
    border: 1px solid var(--footer-border);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.social-link:hover {
    background: var(--footer-accent);
    color: var(--footer-bg);
    transform: translateY(-4px) scale(1.1);
    border-color: transparent;
    box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
}

/* ============================================
   COLUMNS 2 & 3: NAVIGATION & SERVICES
   ============================================ */
.footer-col h4 {
    font-size: 14px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 12px;
}

.footer-col h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 3px;
    background: var(--footer-accent);
    border-radius: 2px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: var(--footer-text);
    text-decoration: none;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    padding: 6px 0;
}

.footer-links a:hover {
    color: var(--footer-accent);
    transform: translateX(8px);
}

.footer-links a i {
    color: var(--footer-accent);
    font-size: 14px;
}

/* ============================================
   FOOTER BOTTOM
   ============================================ */
.footer-bottom {
    background: rgba(0, 0, 0, 0.2);
    border-top: 1px solid var(--footer-border);
    padding: 20px 0;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-copyright {
    font-size: 0.9rem;
    color: var(--footer-text-muted);
}

.footer-copyright strong {
    color: white;
    font-weight: 600;
}

.footer-legal {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.footer-legal p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--footer-text-muted);
}

.back-to-top {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--footer-border);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 18px;
}

.back-to-top:hover {
    background: var(--footer-accent);
    color: var(--footer-bg);
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
}

/* ============================================
   MOBILE STICKY FOOTER
   ============================================ */
.mobile-sticky-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 64px;
    background: white;
    border-top: 1px solid #e2e8f0;
    display: none;
    justify-content: space-around;
    align-items: center;
    z-index: 1030;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
    padding-bottom: env(safe-area-inset-bottom);
}

.sticky-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #64748b;
    text-decoration: none;
    font-size: 11px;
    font-weight: 500;
    flex: 1;
    height: 100%;
    position: relative;
    transition: all 0.3s ease;
    padding: 8px;
}

.sticky-nav-item i {
    font-size: 22px;
    transition: transform 0.3s ease;
}

.sticky-nav-item.active,
.sticky-nav-item:hover {
    color: var(--primary);
}

.sticky-nav-item.active i {
    transform: scale(1.1);
}

/* ============================================
   MOBILE ACCORDION
   ============================================ */
.footer-accordion-toggle {
    width: 100%;
    background: none;
    border: none;
    color: white;
    display: none;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    cursor: pointer;
    border-bottom: 1px solid var(--footer-border);
}

.footer-accordion-toggle h4 {
    margin: 0;
    padding: 0;
    font-size: 14px;
}

.footer-accordion-toggle h4::after {
    display: none;
}

.footer-accordion-toggle i {
    transition: transform 0.3s ease;
    color: var(--footer-accent);
}

.footer-accordion-toggle[aria-expanded="true"] i {
    transform: rotate(180deg);
}

.footer-accordion-content {
    max-height: none;
    overflow: visible;
}

/* ============================================
   RESPONSIVE BREAKPOINTS
   ============================================ */
@media (max-width: 1200px) {
    .footer-grid {
        grid-template-columns: 1.5fr 1fr 1fr;
        gap: 40px;
    }
}

@media (max-width: 992px) {
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .mobile-sticky-footer {
        display: flex;
    }
    
    body {
        padding-bottom: 64px;
    }
    
    .footer-col-brand {
        max-width: 100%;
        text-align: center;
        margin-bottom: 30px;
    }
    
    .footer-brand {
        justify-content: center;
    }
    
    .footer-quick-contact {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .footer-social {
        justify-content: center;
    }
    
    .footer-desc {
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .footer-col-nav,
    .footer-col-services {
        border-bottom: 1px solid var(--footer-border);
        padding: 0 16px;
    }
    
    .footer-col h4 {
        display: none;
    }
    
    .footer-accordion-toggle {
        display: flex !important;
    }
    
    .footer-accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    .footer-accordion-content.open {
        max-height: 300px;
        padding-top: 16px;
        padding-bottom: 16px;
    }
    
    .footer-links a {
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    
    .footer-links a:hover {
        transform: none;
        padding-left: 10px;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }
    
    .back-to-top {
        position: fixed;
        bottom: 80px;
        right: 20px;
        z-index: 1020;
        background: var(--footer-accent);
        color: var(--footer-bg);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
}

@media (max-width: 576px) {
    .footer-main {
        padding: 40px 0 20px;
    }
    
    .footer-brand {
        flex-direction: column;
        gap: 12px;
    }
    
    .footer-logo {
        width: 64px;
        height: 64px;
    }
    
    .brand-info h3 {
        font-size: 1.5rem;
    }
    
    .quick-contact-item {
        width: 100%;
        justify-content: center;
    }
    
    .footer-copyright {
        font-size: 0.85rem;
    }
    
    .footer-legal p {
        font-size: 0.8rem;
    }
}

/* Desktop - Navigation always visible */
@media (min-width: 993px) {
    .footer-accordion-toggle {
        display: none !important;
    }
    
    .footer-accordion-content {
        max-height: none !important;
        overflow: visible !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    
    .footer-col h4 {
        display: block !important;
    }
    
    .footer-col-nav,
    .footer-col-services {
        padding: 0;
    }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .site-footer::before {
        animation: none;
    }
    
    * {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}
</style>

<!-- Footer Scripts -->
<script>
(function() {
    'use strict';

    if (window.footerScriptInitialized) return;
    window.footerScriptInitialized = true;

    // FOOTER ACCORDIONS
    var accordionToggles = document.querySelectorAll('.footer-accordion-toggle');
    for (var i = 0; i < accordionToggles.length; i++) {
        accordionToggles[i].addEventListener('click', function() {
            var expanded = this.getAttribute('aria-expanded') === 'true';
            var contentId = this.getAttribute('aria-controls');
            var content = document.getElementById(contentId);

            var allToggles = document.querySelectorAll('.footer-accordion-toggle');
            for (var j = 0; j < allToggles.length; j++) {
                if (allToggles[j] !== this) {
                    allToggles[j].setAttribute('aria-expanded', 'false');
                    var otherContent = document.getElementById(allToggles[j].getAttribute('aria-controls'));
                    if (otherContent) otherContent.classList.remove('open');
                }
            }

            this.setAttribute('aria-expanded', !expanded);
            if (content) content.classList.toggle('open');
        });
    }

    // UTILITAIRES
    window.scrollToTop = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.openMap = function() {
        var address = "<?= addslashes($this->Model->get_setting('adresse_siege', 'Bujumbura, Burundi')) ?>";
        window.open('https://maps.google.com/?q=' + encodeURIComponent(address), '_blank');
    };

    // CONFIG BASE URL
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?php echo rtrim(base_url(), '/'); ?>/';
    }
})();
</script>