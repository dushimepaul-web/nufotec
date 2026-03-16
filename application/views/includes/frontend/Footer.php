<script>
    window.addEventListener("load", function() {
        const loader = document.getElementById("loadingSpinner");
        if (loader) {
            setTimeout(() => {
                loader.classList.add("loader-hidden");
                setTimeout(() => {
                    loader.remove();
                }, 400); // après la transition de 0.4s, on supprime
            }, 3000); // 
        }
    });
</script>









<!-- ===== BOUTON PANIER FLOTTANT ===== -->
<a href="<?php echo base_url('panier'); ?>" class="cart-link">
    <i class="bi bi-cart-fill"></i>
    <span class="cart-badge" id="cartBadge">0</span>
</a>

<script>
// ============================================
// SCRIPT GLOBAL DE MISE À JOUR DU BADGE PANIER
// ============================================
(function() {
    // Définir BASE_URL si ce n'est pas déjà fait
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?php echo base_url(); ?>';
    }

    // Fonction de mise à jour du badge (rendue globale pour être appelée depuis les autres pages)
    window.updateCartBadge = function() {
        fetch(BASE_URL + 'panier/get_cart')
            .then(response => response.json())
            .then(data => {
                var badge = document.getElementById('cartBadge');
                if (badge) {
                    badge.textContent = data.nb_articles || 0;
                }
            })
            .catch(error => console.error('Erreur mise à jour badge:', error));
    };

    // Première mise à jour immédiate
    window.updateCartBadge();

    // Mise à jour toutes les 2 secondes
    setInterval(window.updateCartBadge, 2000);
})();
</script>
<style>
    /* Bouton panier flottant */
.cart-link {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #0f4c3a 0%, #1a6b52 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    box-shadow: 0 10px 15px rgba(0,0,0,0.1);
    cursor: pointer;
    z-index: 999;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border: none;
    text-decoration: none;
}

.cart-link:hover {
    transform: scale(1.1) rotate(5deg);
    background: linear-gradient(135deg, #1a6b52 0%, #0f4c3a 100%);
    color: white;
}

.cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #d4af37;
    color: #0a3326;
    font-size: 12px;
    font-weight: 700;
    min-width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 0 6px;
}

/* Adaptation mobile */
@media (max-width: 768px) {
    .cart-link {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
}
</style>













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
    <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'site_logo_20260309231724_69af38e43ca74.jpeg')) ?>" 
         alt="<?= $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI') ?>" 
         class="footer-logo"
         onerror="this.src='<?= base_url('assets/images/placeholder.png') ?>'">
    <div class="brand-info">
        <h3><?= $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI') ?></h3>
        <span><?= $this->Model->get_setting('agf_slogan', 'Excellence Agro-Industrielle et Phytomédicinale') ?></span>
    </div>
</div>

<p class="footer-desc">
    <?= $this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi') ?>
</p>

<!-- Quick Contact -->
<div class="footer-quick-contact">
    <a href="tel:<?= $this->Model->get_setting('site_phone', '+257 79 666 439') ?>" class="quick-contact-item">
        <i class="bi bi-telephone-fill"></i>
        <span><?= $this->Model->get_setting('site_phone', '+257 79 666 439') ?></span>
    </a>
    <a href="mailto:<?= $this->Model->get_setting('contact_email_invest', 'nufotecburundi@gmail.com') ?>" class="quick-contact-item">
        <i class="bi bi-envelope-fill"></i>
        <span><?= $this->Model->get_setting('contact_email_invest', 'nufotecburundi@gmail.com') ?></span>
    </a>
    <a href="#" class="quick-contact-item" onclick="openMap()">
        <i class="bi bi-geo-alt-fill"></i>
        <span><?= $this->Model->get_setting('adresse_siege', 'Bujumbura, République du Burundi') ?></span>
    </a>
</div>


                        <!-- Social Links -->
                        <div class="footer-social">
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="YouTube">
                                <i class="bi bi-youtube"></i>
                            </a>
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
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        Medical Consultation
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('boutique') ?>">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        Product Sales
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('investissement') ?>">
                                        <i class="bi bi-file-earmark-pdf"></i>
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
                        <p>&copy; <?= date('Y') ?> <strong><?= $this->Model->get_setting('site_name', 'AGF Phytomed') ?></strong>. All rights reserved.</p>
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
    display: flex;
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

// Mobile Accordions
document.querySelectorAll('.footer-accordion-toggle').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const expanded = this.getAttribute('aria-expanded') === 'true';
        const content = document.getElementById(this.getAttribute('aria-controls'));
        
        // Close all others
        document.querySelectorAll('.footer-accordion-toggle').forEach(other => {
            if (other !== this) {
                other.setAttribute('aria-expanded', 'false');
                document.getElementById(other.getAttribute('aria-controls'))?.classList.remove('open');
            }
        });
        
        // Toggle this one
        this.setAttribute('aria-expanded', !expanded);
        content?.classList.toggle('open');
    });
});

// Scroll to Top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Open Map
function openMap() {
    const address = "<?= $this->Model->get_setting('adresse_siege', 'Bujumbura, Burundi') ?>";
    window.open(`https://maps.google.com/?q=${encodeURIComponent(address)}`, '_blank');
}




// ============================================
// SCRIPT footer DE MISE À JOUR DU BADGE PANIER
// ============================================
(function() {
    // Définir BASE_URL si ce n'est pas déjà fait
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?php echo base_url(); ?>';
    }

    // Fonction de mise à jour du badge (rendue globale pour être appelée depuis les autres pages)
    window.updateCartBadgecart = function() {
        fetch(BASE_URL + 'panier/get_cart')
            .then(response => response.json())
            .then(data => {
                var badge = document.getElementById('cart');
                if (badge) {
                    badge.textContent = data.nb_articles || 0;
                }
            })
            .catch(error => console.error('Erreur mise à jour badge:', error));
    };

    // Première mise à jour immédiate
    window.updateCartBadgecart();

    // Mise à jour toutes les 2 secondes
    setInterval(window.updateCartBadgecart, 2000);
})();



// Intersection Observer for scroll animations
const footerObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('footer-visible');
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.footer-col').forEach(col => {
    footerObserver.observe(col);
});
</script>






<!-- ═══════════════════════════════════════════════════════ -->
<!-- SCRIPTS - ORDRE CORRIGÉ -->
<!-- ═══════════════════════════════════════════════════════ -->

<!-- 1. Bootstrap JS D'ABORD -->
<script src="<?= base_url() ?>assets/backend/js/bootstrap.bundle.min.js"></script>



<!-- 4. Autres scripts -->
<script src="<?= base_url() ?>assets/backend/js/swiper-bundle.min.js"></script>




    <script>
        // Page Loader
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('pageLoader').classList.add('hidden');
            }, 1000);
        });

        // Initialize Swiper
        const heroSwiper = new Swiper('.heroSwiper', {
            spaceBetween: 0,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            speed: 1000,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            loop: true,
            grabCursor: true,
            keyboard: {
                enabled: true,
            },
            on: {
                slideChange: function () {
                    // Reset animations
                    const activeSlide = this.slides[this.activeIndex];
                    const content = activeSlide.querySelector('.slide-inner');
                    content.style.animation = 'none';
                    setTimeout(() => {
                        content.style.animation = 'slideInUp 1s ease';
                    }, 10);
                }
            }
        });

        // Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.main-navbar');
            const header = document.querySelector('.main-header');
            const scrollIndicator = document.querySelector('.scroll-indicator');
            
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
                header.style.transform = 'translateY(-100%)';
                header.style.transition = 'transform 0.3s ease';
                if (scrollIndicator) scrollIndicator.style.opacity = '0';
            } else {
                navbar.classList.remove('scrolled');
                header.style.transform = 'translateY(0)';
                if (scrollIndicator) scrollIndicator.style.opacity = '0.7';
            }
        });

        // Mobile Navigation Toggle
        function toggleMobileNav() {
            const nav = document.getElementById('mainNav');
            const overlay = document.getElementById('mobileOverlay');
            
            nav.classList.toggle('active');
            overlay.style.display = nav.classList.contains('active') ? 'block' : 'none';
        }

        // Mobile Dropdown Toggle
        document.querySelectorAll('.nav-item.dropdown').forEach(item => {
            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 991) {
                    e.preventDefault();
                    this.classList.toggle('active');
                }
            });
        });

        // Smooth Scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Parallax effect for hero slides
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.slide-bg');
            parallaxElements.forEach(el => {
                const speed = 0.5;
                el.style.transform = `translateY(${scrolled * speed}px) scale(1.1)`;
            });

        });
    </script>
