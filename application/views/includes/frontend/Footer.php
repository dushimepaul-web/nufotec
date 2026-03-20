<script>
    /**
     * LOADING SPINNER NUFOTEC
     * Affiche le spinner pendant 4 secondes puis le masque avec animation
     */
    (function() {
        'use strict';
        
        const loader = document.getElementById("loadingSpinner");
        
        if (!loader) return; // Sécurité : si le spinner n'existe pas, ne rien faire

        // Attendre que la page soit complètement chargée
        window.addEventListener("load", function() {
            
            // Délai de 4 secondes avant de masquer le spinner
            setTimeout(function() {
                
                // Ajouter la classe pour l'animation de disparition
                loader.classList.add("loader-hidden");
                
                // Supprimer complètement le spinner après l'animation (0.4s)
                setTimeout(function() {
                    loader.remove();
                    
                    // Libérer le scroll si bloqué
                    document.body.style.overflow = '';
                    
                }, 400); // Durée de la transition CSS
                
            }, 3000); // 4 secondes d'affichage
            
        });
        
        // Sécurité : si le load event ne se déclenche pas (déjà chargé)
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
                                 alt="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?>" 
                                 class="footer-logo">
                            <?php endif; ?>
                            <div class="brand-info">
                                <h3><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?></h3>
                                <span><?= htmlspecialchars($this->Model->get_setting('agf_slogan', 'Excellence Agro-Industrielle et Phytomédicinale')) ?></span>
                            </div>
                        </div>

                        <p class="footer-desc">
                            <?= htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi')) ?>
                        </p>

                        <!-- Quick Contact -->
                        <div class="footer-quick-contact">
                            <a href="tel:<?= htmlspecialchars($this->Model->get_setting('site_phone', '+257 79 666 439')) ?>" class="quick-contact-item">
                                <i class="bi bi-telephone-fill"></i>
                                <span><?= htmlspecialchars($this->Model->get_setting('site_phone', '+257 79 666 439')) ?></span>
                            </a>
                            <a href="mailto:<?= htmlspecialchars($this->Model->get_setting('contact_email_invest', 'nufotecburundi@gmail.com')) ?>" class="quick-contact-item">
                                <i class="bi bi-envelope-fill"></i>
                                <span><?= htmlspecialchars($this->Model->get_setting('contact_email_invest', 'nufotecburundi@gmail.com')) ?></span>
                            </a>
                            <a href="#" class="quick-contact-item" onclick="openMap(); return false;">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span><?= htmlspecialchars($this->Model->get_setting('adresse_siege', 'Bujumbura, République du Burundi')) ?></span>
                            </a>
                        </div>

                        <!-- Social Links -->
                        <div class="footer-social">
                            <a href="#" class="social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="social-link" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                            <a href="#" class="social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
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
                                <li><a href="<?= base_url() ?>">Home</a></li>
                                <li><a href="<?= base_url('a-propos') ?>">About Us</a></li>
                                <li><a href="<?= base_url('boutique') ?>">Shop</a></li>
                                <li><a href="<?= base_url('Medicins') ?>">Teleconsultation</a></li>
                                <li><a href="<?= base_url('investissement') ?>">Investment</a></li>
                                <li><a href="<?= base_url('Home/Media') ?>">News</a></li>
                                <li><a href="<?= base_url('Home/Contact') ?>">Contact</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 3: Our Services -->
                    <div class="footer-col footer-col-services">
                        <button class="footer-accordion-toggle d-lg-none" aria-expanded="false" aria-controls="footerNav2">
                            <h4>Our Services</h4>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <h4 class="d-none d-lg-block">Our Services</h4>
                        
                        <div class="footer-accordion-content" id="footerNav2">
                            <ul class="footer-links">
                                <li>
                                    <a href="<?= base_url('Medicins') ?>">
                                        <i class="bi bi-heart-pulse"></i>
                                        Medical Consultation
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('boutique') ?>">
                                        <i class="bi bi-shop"></i>
                                        Product Sales
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('investissement') ?>">
                                        <i class="bi bi-graph-up-arrow"></i>
                                        Investment Research
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
                        <p>&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($this->Model->get_setting('site_name', 'AGF Phytomed')) ?></strong>. All rights reserved.</p>
                    </div>

                    <!-- Legal Links -->
                    <div class="footer-legal">
                        <p>designed by Dushime Paul : dushimeyesupaulin@gmail.com</p>
                    </div>

                    <!-- Back to Top -->
                    <button class="back-to-top" onclick="scrollToTop()" aria-label="Back to top">
                        <i class="bi bi-arrow-up"></i>
                    </button>

                </div>
            </div>
        </div>

    </div>
</footer>

<!-- Mobile Sticky Footer (App Style) -->
<div class="mobile-sticky-footer d-lg-none">
    <a href="<?= base_url() ?>" class="sticky-nav-item <?= current_url() == base_url() ? 'active' : '' ?>">
        <i class="bi bi-house-door-fill"></i>
        <span>Home</span>
    </a>
    <a href="<?= base_url('Home/Boutique') ?>" class="sticky-nav-item">
        <i class="bi bi-shop"></i>
        <span>Shop</span>
    </a>
    <a href="<?= base_url('Medicins') ?>" class="sticky-nav-item">
        <i class="bi bi-camera-video"></i>
        <span>Teleconsult</span>
    </a>
    <a href="<?= base_url('Investors-form') ?>" class="sticky-nav-item">
        <i class="bi bi-graph-up-arrow"></i>
        <span>Invest</span>
    </a>
    <a href="<?= base_url('panier') ?>" class="sticky-nav-item cart-item">
        <i class="bi bi-cart3"></i>
        <span>Cart</span>
        <span class="sticky-badge" id="cart">0</span>
    </a>
    <a href="<?= base_url('Home/Contact') ?>" class="sticky-nav-item">
        <i class="bi bi-headset"></i>
        <span>Contact</span>
    </a>
</div>

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
    background: linear-gradient(180deg, var(--footer-bg-light) 0%, var(--footer-bg) 100%);
    color: var(--footer-text);
    font-family: 'Inter', sans-serif;
    position: relative;
    margin-top: auto;
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
    font-family: 'Playfair Display', serif;
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

.footer-legal a {
    color: var(--footer-text-muted);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    position: relative;
}

.footer-legal a:hover {
    color: var(--footer-accent);
}

.footer-legal .separator {
    color: var(--footer-text-muted);
    font-size: 8px;
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

.sticky-badge {
    position: absolute;
    top: 6px;
    right: calc(50% - 20px);
    background: #dc3545;
    color: white;
    font-size: 10px;
    font-weight: 700;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
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
    
    /* Accordions for tablet and mobile */
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
    
    .footer-legal {
        gap: 12px;
    }
    
    .footer-legal a {
        font-size: 0.8rem;
    }
    
    .footer-legal .separator {
        display: none;
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

/* Landscape mobile */
@media (max-height: 500px) and (max-width: 992px) {
    .mobile-sticky-footer {
        display: none;
    }
    body {
        padding-bottom: 0;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .mobile-sticky-footer {
        background: #1a1a1a;
        border-top-color: #333;
    }
    
    .sticky-nav-item {
        color: #a0a0a0;
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
// ============================================
// FOOTER INTERACTIONS
// ============================================

(function() {
    'use strict';
    
    // Mobile Accordions
    document.querySelectorAll('.footer-accordion-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            const contentId = this.getAttribute('aria-controls');
            const content = document.getElementById(contentId);
            
            // Close all others
            document.querySelectorAll('.footer-accordion-toggle').forEach(function(other) {
                if (other !== toggle) {
                    other.setAttribute('aria-expanded', 'false');
                    const otherContent = document.getElementById(other.getAttribute('aria-controls'));
                    if (otherContent) otherContent.classList.remove('open');
                }
            });
            
            // Toggle this one
            this.setAttribute('aria-expanded', !expanded);
            if (content) content.classList.toggle('open');
        });
    });

    // Scroll to Top
    window.scrollToTop = function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    // Open Map
    window.openMap = function() {
        const address = "<?= addslashes($this->Model->get_setting('adresse_siege', 'Bujumbura, Burundi')) ?>";
        window.open('https://maps.google.com/?q=' + encodeURIComponent(address), '_blank');
    };

    // ============================================
    // SCRIPT PANIER - BADGE MISE À JOUR
    // ============================================
    
    // Définir BASE_URL si ce n'est pas déjà fait
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?php echo rtrim(base_url(), '/'); ?>/';
    }

    // Fonction unique pour mettre à jour les badges
    function updateCartBadges() {
        const cartBadge = document.getElementById('cart');
        const cartBadgeFloating = document.getElementById('cartBadge');
        
        // Si aucun badge n'existe, ne rien faire
        if (!cartBadge && !cartBadgeFloating) return;

        fetch(BASE_URL + 'panier/get_cart', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        })
        .then(function(text) {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                // Réponse n'est pas du JSON, ignorer silencieusement
                return;
            }
            
            const count = data.nb_articles || 0;
            
            if (cartBadge) cartBadge.textContent = count;
            if (cartBadgeFloating) cartBadgeFloating.textContent = count;
        })
        .catch(function(error) {
            // Erreur silencieuse en production
            console.warn('Cart update failed:', error.message);
        });
    }

    // Exposer globalement si nécessaire
    window.updateCartBadge = updateCartBadges;
    window.updateCartBadgecart = updateCartBadges;

    // Première mise à jour au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateCartBadges);
    } else {
        updateCartBadges();
    }

    // Mise à jour périodique (toutes les 5 secondes pour réduire la charge)
    setInterval(updateCartBadges, 5000);

})();
</script>