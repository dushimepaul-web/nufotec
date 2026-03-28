<script>
    /**
     * LOADING SPINNER NUFOTEC
     * Affiche le spinner pendant 1 seconde puis le masque avec animation
     */
    (function() {
        'use strict';
        
        var loader = document.getElementById("loadingSpinner");
        
        if (!loader) return; // Sécurité : si le spinner n'existe pas, ne rien faire

        // Attendre que la page soit complètement chargée
        window.addEventListener("load", function() {
            
            // Délai de 1 seconde avant de masquer le spinner
            setTimeout(function() {
                
                // Ajouter la classe pour l'animation de disparition
                loader.classList.add("loader-hidden");
                
                // Supprimer complètement le spinner après l'animation (0.4s)
                setTimeout(function() {
                    loader.remove();
                    
                    // Libérer le scroll si bloqué
                    document.body.style.overflow = '';
                    
                }, 400); // Durée de la transition CSS
                
            }, 1000); // 1 seconde d'affichage
            
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
                                 alt="<?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'), ENT_QUOTES, 'UTF-8') ?>" 
                                 class="footer-logo">
                            <?php endif; ?>
                            <div class="brand-info">
                                <h3><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI'), ENT_QUOTES, 'UTF-8') ?></h3>
                                <span><?= htmlspecialchars($this->Model->get_setting('agf_slogan', 'Excellence Agro-Industrielle et Phytomédicinale'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>

                        <p class="footer-desc">
                            <?= htmlspecialchars($this->Model->get_setting('agf_description_courte', 'Projet intégré de transformation agro-alimentaire et de production phytomédicinale au Burundi'), ENT_QUOTES, 'UTF-8') ?>
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
                                <span><?= htmlspecialchars($this->Model->get_setting('adresse_siege', 'Bujumbura, République du Burundi'), ENT_QUOTES, 'UTF-8') ?></span>
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
                                <li><a href="<?= base_url() ?>">Accueil</a></li>
                                <li><a href="<?= base_url('a-propos') ?>">À Propos</a></li>
                                <li><a href="<?= base_url('boutique') ?>">Boutique</a></li>
                                <li><a href="<?= base_url('Medicins') ?>">Téléconsultation</a></li>
                                <li><a href="<?= base_url('investissement') ?>">Investissement</a></li>
                                <li><a href="<?= base_url('Home/Media') ?>">Médias</a></li>
                                <li><a href="<?= base_url('Home/Contact') ?>">Contact</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 3: Our Services -->
                    <div class="footer-col footer-col-services">
                        <button class="footer-accordion-toggle d-lg-none" aria-expanded="false" aria-controls="footerNav2">
                            <h4>Nos Services</h4>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <h4 class="d-none d-lg-block">Nos Services</h4>
                        
                        <div class="footer-accordion-content" id="footerNav2">
                            <ul class="footer-links">
                                <li>
                                    <a href="<?= base_url('Medicins') ?>">
                                        <i class="bi bi-heart-pulse"></i>
                                        Consultation Médicale
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('boutique') ?>">
                                        <i class="bi bi-shop"></i>
                                        Vente de Produits
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('investissement') ?>">
                                        <i class="bi bi-graph-up-arrow"></i>
                                        Recherche d'Investissement
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

<!-- Floating Cart Button - Caché sur mobile -->
<a href="<?= base_url('panier') ?>" class="floating-cart d-none d-md-flex" id="floatingCart" title="Mon panier">
    <div class="cart-icon-wrapper">
        <i class="bi bi-cart-fill"></i>
        <span class="cart-badge" id="cartBadge">0</span>
    </div>
    <span class="cart-text">Panier</span>
</a>

<!-- Mobile Sticky Footer (App Style) -->
<div class="mobile-sticky-footer d-lg-none">
    <a href="<?= base_url() ?>" class="sticky-nav-item <?= (current_url() == base_url()) ? 'active' : '' ?>">
        <i class="bi bi-house-door-fill"></i>
        <span>Accueil</span>
    </a>
    <!-- <a href="" class="sticky-nav-item">
        <i class="bi bi-shop"></i>
        <span>Boutique</span>
    </a> -->
    <a href="<?= base_url('Products') ?>" class="sticky-nav-item">
        <i class="bi bi-shop"></i>
        <span>Boutique</span>
    </a>
    <a href="<?= base_url('Medicins') ?>" class="sticky-nav-item">
        <i class="bi bi-camera-video"></i>
        <span>Téléconsult</span>
    </a>
    <a href="<?= base_url('investissement') ?>" class="sticky-nav-item">
        <i class="bi bi-graph-up-arrow"></i>
        <span>Investir</span>
    </a>
    <!-- <a href="" class="sticky-nav-item cart-item">
        <i class="bi bi-cart3"></i>
        <span>Panier</span>
        <span class="sticky-badge" id="cart">0</span>
    </a>-->

    <a href="<?= base_url('media') ?>" class="sticky-nav-item">
        <i class="bi bi-collection"></i>
        <span>media</span>
    </a>
    
    <?php 
    // Récupérer les informations de l'utilisateur depuis la session (CodeIgniter 3)
    $user_id = $this->session->userdata('user_id');
    $user_photo = $this->session->userdata('photo');
    $user_fullname = $this->session->userdata('fullname');
    $user_type = $this->session->userdata('type_utilisateur');
    $is_logged_in = $this->session->userdata('logged_in');

    // Calculer les initiales pour le placeholder
    $initials = '';
    if ($is_logged_in && !empty($user_fullname)) {
        $name_parts = explode(' ', trim($user_fullname));
        if (count($name_parts) >= 2) {
            $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
        } else {
            $initials = strtoupper(substr($user_fullname, 0, 2));
        }
    }
    
    // Déterminer le lien de redirection selon le type d'utilisateur
    $account_link = base_url('Auth');
    if ($is_logged_in) {
        if ($user_type == 'medecin') {
            $account_link = base_url('Admin');
        } elseif ($user_type == 'admin') {
            $account_link = base_url('Admin');
        } else {
            $account_link = base_url('home-patient');
        }
    }
    ?>
    
    <a href="<?= $account_link ?>" class="sticky-nav-item sticky-account" title="<?= $is_logged_in ? 'Mon compte' : 'Se connecter' ?>">
        <div class="sticky-icon-wrapper">
            <?php if ($is_logged_in && !empty($user_photo) && file_exists(FCPATH . 'attachments/Users/' . $user_photo)): ?>
                <div class="sticky-avatar">
                    <img src="<?= base_url('attachments/Users/' . $user_photo) ?>" 
                         alt="<?= htmlspecialchars($user_fullname, ENT_QUOTES, 'UTF-8') ?>"
                         class="avatar-img">
                </div>
            <?php elseif ($is_logged_in): ?>
                <div class="avatar-placeholder"><?= $initials ?></div>
            <?php else: ?>
                <i class="bi bi-person-circle"></i>
            <?php endif; ?>
        </div>
        <span><?= $is_logged_in ? 'Compte' : 'Connexion' ?></span>
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
    background: linear-gradient(135deg, var(--footer-bg-light) 0%, var(--footer-bg) 100%);
    color: var(--footer-text);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
   FLOATING CART BUTTON
   ============================================ */
.floating-cart {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: linear-gradient(135deg, var(--footer-accent), #f4d03f);
    color: var(--footer-bg);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 1040;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    border: none;
    cursor: pointer;
}

.floating-cart:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
    color: var(--footer-bg);
}

.cart-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.floating-cart i {
    font-size: 24px;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -12px;
    background: #dc3545;
    color: white;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    border: 2px solid white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.cart-text {
    font-size: 14px;
    font-weight: 600;
}

/* Animation pour l'ajout au panier */
@keyframes cartBump {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.cart-badge.bump {
    animation: cartBump 0.3s ease-in-out;
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
   STICKY FOOTER ACCOUNT AVATAR
   ============================================ */
.sticky-nav-item.sticky-account {
    position: relative;
}

/* Conteneur unifié pour l'icône/avatar */
.sticky-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    position: relative;
}

/* Style pour l'avatar */
.sticky-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-light);
    border: 2px solid var(--footer-accent);
    transition: all 0.3s ease;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Style pour le placeholder d'avatar (initiales) */
.avatar-placeholder {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--footer-accent), #f4d03f);
    color: var(--footer-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    border: 2px solid var(--footer-accent);
    transition: all 0.3s ease;
}

/* Style pour les icônes */
.sticky-icon-wrapper i {
    font-size: 24px;
    transition: all 0.3s ease;
    line-height: 1;
}

/* Effets au survol */
.sticky-nav-item.active .sticky-avatar,
.sticky-nav-item:hover .sticky-avatar {
    transform: scale(1.1);
    border-color: var(--footer-accent);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.3);
}

.sticky-nav-item.active .avatar-placeholder,
.sticky-nav-item:hover .avatar-placeholder {
    transform: scale(1.1);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.3);
}

.sticky-nav-item.active .sticky-icon-wrapper i,
.sticky-nav-item:hover .sticky-icon-wrapper i {
    color: var(--footer-accent);
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
    
    .floating-cart {
        bottom: 80px;
        right: 20px;
        padding: 10px 16px;
    }
    
    .floating-cart i {
        font-size: 20px;
    }
    
    .cart-text {
        font-size: 12px;
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
    
    .sticky-icon-wrapper {
        width: 24px;
        height: 24px;
    }
    
    .sticky-icon-wrapper i {
        font-size: 20px;
    }
    
    .avatar-placeholder {
        width: 24px;
        height: 24px;
        font-size: 10px;
    }
    
    .floating-cart {
        bottom: 70px;
        right: 15px;
        padding: 8px 14px;
    }
    
    .floating-cart i {
        font-size: 18px;
    }
    
    .cart-text {
        display: none;
    }
    
    .cart-badge {
        top: -6px;
        right: -10px;
        font-size: 10px;
        min-width: 16px;
        height: 16px;
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
    
    .floating-cart {
        bottom: 20px;
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
(function() {
    'use strict';

    // Empêcher l'initialisation multiple
    if (window.footerScriptInitialized) return;
    window.footerScriptInitialized = true;

    // ============================================
    // FOOTER ACCORDIONS
    // ============================================
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

    // ============================================
    // UTILITAIRES
    // ============================================
    window.scrollToTop = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.openMap = function() {
        var address = "<?= addslashes($this->Model->get_setting('adresse_siege', 'Bujumbura, Burundi')) ?>";
        window.open('https://maps.google.com/?q=' + encodeURIComponent(address), '_blank');
    };

    // ============================================
    // CONFIG BASE URL
    // ============================================
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?php echo rtrim(base_url(), '/'); ?>/';
    }

    // ============================================
    // PANIER (ANTI SPAM + SAFE)
    // ============================================
    var isFetchingCart = false;
    var cartInterval = null;

    function updateCartBadges() {
        if (isFetchingCart) return;
        isFetchingCart = true;

        var cartBadge = document.getElementById('cart');
        var cartBadgeFloating = document.getElementById('cartBadge');

        if (!cartBadge && !cartBadgeFloating) {
            isFetchingCart = false;
            return;
        }

        fetch(BASE_URL + 'panier/get_cart', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function(data) {
            var count = data.nb_articles || 0;
            
            // Mettre à jour le badge du sticky footer
            if (cartBadge) {
                cartBadge.textContent = count;
            }
            
            // Mettre à jour le badge flottant avec animation
            if (cartBadgeFloating) {
                var oldCount = parseInt(cartBadgeFloating.textContent) || 0;
                cartBadgeFloating.textContent = count;
                
                if (count > oldCount) {
                    cartBadgeFloating.classList.add('bump');
                    setTimeout(function() {
                        cartBadgeFloating.classList.remove('bump');
                    }, 300);
                }
            }
        })
        .catch(function() {})
        .finally(function() {
            isFetchingCart = false;
        });
    }

    // Fonction pour ajouter au panier
    window.addToCart = function(productId, quantity) {
        quantity = quantity || 1;
        
        fetch(BASE_URL + 'panier/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'product_id=' + productId + '&quantity=' + quantity
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                updateCartBadges();
                showNotification('Produit ajouté au panier !', 'success');
            } else {
                showNotification(data.message || 'Erreur lors de l\'ajout', 'error');
            }
        })
        .catch(function() {
            showNotification('Erreur lors de l\'ajout au panier', 'error');
        });
    };
    
    // Fonction pour afficher une notification
    function showNotification(message, type) {
        var notification = document.getElementById('cart-notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'cart-notification';
            notification.className = 'cart-notification';
            document.body.appendChild(notification);
        }
        
        notification.textContent = message;
        notification.className = 'cart-notification ' + type + ' show';
        
        setTimeout(function() {
            notification.classList.remove('show');
        }, 3000);
    }

    // Exposer globalement
    window.updateCartBadge = updateCartBadges;

    // ============================================
    // INITIALISATION UNIQUE
    // ============================================
    function initCart() {
        updateCartBadges();

        // Empêcher plusieurs intervals
        if (!cartInterval) {
            cartInterval = setInterval(updateCartBadges, 5000);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCart);
    } else {
        initCart();
    }

})();
</script>

<!-- Cart Notification Styles -->
<style>
.cart-notification {
    position: fixed;
    bottom: 100px;
    right: 30px;
    background: var(--footer-bg);
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 1050;
    transform: translateX(400px);
    transition: transform 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    border-left: 4px solid var(--footer-accent);
}

.cart-notification.show {
    transform: translateX(0);
}

.cart-notification.success {
    border-left-color: #28a745;
    background: linear-gradient(135deg, var(--footer-bg), #0a3326);
}

.cart-notification.error {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, #2c1a1a, #1a0f0f);
}

@media (max-width: 992px) {
    .cart-notification {
        bottom: 90px;
        right: 20px;
        padding: 10px 16px;
        font-size: 12px;
    }
}

@media (max-width: 576px) {
    .cart-notification {
        bottom: 80px;
        right: 15px;
        left: 15px;
        transform: translateY(100px);
        text-align: center;
    }
    
    .cart-notification.show {
        transform: translateY(0);
    }
}
</style>