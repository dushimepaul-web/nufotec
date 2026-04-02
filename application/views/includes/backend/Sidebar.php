<!-- Sidebar wrapper -->
<div class="sidebar-wrapper" data-simplebar="true">

    <!-- Sidebar header -->
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo-badge">
                <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>" 
                     class="logo-icon" alt="AGF Phytomed Logo">
            </div>
            <div class="brand-text">
                <h4 class="brand-name"><?= $this->Model->get_setting('site_name', 'NUFOTEC') ?></h4>
            </div>
        </div>
        <div class="mobile-toggle-icon d-lg-none">
            <i class='bx bx-x'></i>
        </div>
    </div>
    <!-- end header -->

    <!-- Navigation -->
    <ul class="metismenu" id="menu">

        <!-- SECTION: DASHBOARD -->
        <li class="menu-section">
            <span class="menu-label">Tableau de Bord</span>
        </li>
        
        <!-- Dashboard -->
        <li class="<?= $this->uri->segment(1) == 'Dashboard' ? 'mm-active' : '' ?>">
            <a href="<?= base_url('Dashboard') ?>" class="<?= $this->uri->segment(1) == 'Dashboard' ? 'active' : '' ?>">
                <div class="parent-icon">
                    <i class='bx bxs-dashboard'></i>
                </div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>
         <?php if (admin_view()): ?>
        <!-- Contenu du site -->
        <li class="<?= in_array($this->uri->segment(1), ['Pages','Sections','Appels_action','Chiffres_cles','Equipe','Etapes_projet','Galerie_medias','Ressources_telechargeables','Actualites','Evenements','Faq','Categories_produits','Licences_certifications','Partenaires','Risques_mitigations','Services','Statistiques_reseaux','Temoignages']) ? 'mm-active' : '' ?>">
            <a href="#" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bxs-layer'></i>
                </div>
                <div class="menu-title">Contenu du Site</div>
                <?php 
                $content_count = count($this->Model->read('pages')) + count($this->Model->read('temoignages'));
                if($content_count > 0): 
                ?>
                <span class="menu-badge"><?= $content_count > 99 ? '99+' : $content_count ?></span>
                <?php endif; ?>
            </a>
            <ul>
                <li><a href="<?= base_url('Homepage') ?>" class="<?= $this->uri->segment(1) == 'Pages' ? 'active' : '' ?>"><i class='bx bx-file'></i>Pages</a></li>
                <li><a href="<?= base_url('Sections') ?>" class="<?= $this->uri->segment(1) == 'Sections' ? 'active' : '' ?>"><i class='bx bx-layout'></i>Sections</a></li>
                <li><a href="<?= base_url('Actualites') ?>" class="<?= $this->uri->segment(1) == 'Actualites' ? 'active' : '' ?>"><i class='bx bx-news'></i>Actualités</a></li>
                <li><a href="<?= base_url('Evenements') ?>" class="<?= $this->uri->segment(1) == 'Evenements' ? 'active' : '' ?>"><i class='bx bx-calendar-event'></i>Événements</a></li>
                <li class="submenu-divider"></li>
                <li><a href="<?= base_url('Equipe') ?>" class="<?= $this->uri->segment(1) == 'Equipe' ? 'active' : '' ?>"><i class='bx bx-group'></i>Équipe</a></li>
                <li><a href="<?= base_url('Partenaires') ?>" class="<?= $this->uri->segment(1) == 'Partenaires' ? 'active' : '' ?>"><i class='bx bx-network-chart'></i>Partenaires</a></li>
                <li><a href="<?= base_url('Temoignages') ?>" class="<?= $this->uri->segment(1) == 'Temoignages' ? 'active' : '' ?>"><i class='bx bx-message-square-detail'></i>Témoignages</a></li>
                <li class="submenu-divider"></li>
                
                <li><a href="<?= base_url('Services') ?>" class="<?= $this->uri->segment(1) == 'Services' ? 'active' : '' ?>"><i class='bx bx-briefcase'></i>Services</a></li>
                <li><a href="<?= base_url('Appels_action') ?>" class="<?= $this->uri->segment(1) == 'Appels_action' ? 'active' : '' ?>"><i class='bx bx-bullseye'></i>Calls to Action</a></li>
                <li><a href="<?= base_url('Chiffres_cles') ?>" class="<?= $this->uri->segment(1) == 'Chiffres_cles' ? 'active' : '' ?>"><i class='bx bx-trophy'></i>Chiffres Clés</a></li>
                <li><a href="<?= base_url('Statistiques_reseaux') ?>" class="<?= $this->uri->segment(1) == 'Statistiques_reseaux' ? 'active' : '' ?>"><i class='bx bx-line-chart'></i>Statistiques</a></li>
                <li class="submenu-divider"></li>
                <li><a href="<?= base_url('Galerie_medias') ?>" class="<?= $this->uri->segment(1) == 'Galerie_medias' ? 'active' : '' ?>"><i class='bx bx-images'></i>Médias</a></li>
                <li><a href="<?= base_url('Ressources_telechargeables') ?>" class="<?= $this->uri->segment(1) == 'Ressources_telechargeables' ? 'active' : '' ?>"><i class='bx bx-download'></i>Ressources</a></li>
                <li><a href="<?= base_url('Faq') ?>" class="<?= $this->uri->segment(1) == 'Faq' ? 'active' : '' ?>"><i class='bx bx-help-circle'></i>FAQ</a></li>
                <li><a href="<?= base_url('Licences_certifications') ?>" class="<?= $this->uri->segment(1) == 'Licences_certifications' ? 'active' : '' ?>"><i class='bx bx-certification'></i>Licences</a></li>
                <li><a href="<?= base_url('Visionmission') ?>" class="<?= $this->uri->segment(1) == 'Licences_certifications' ? 'active' : '' ?>"><i class='bx bx-certification'></i>Mission & Vision</a></li>
            </ul>
        </li>
<?php endif; ?>

<?php if (medecin_view() || admin_view()): ?>
        <!-- Digital Health -->
        <li class="<?= in_array($this->uri->segment(1), ['Consultations','Health']) ? 'mm-active' : '' ?>">
            <a href="#" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bxs-heart'></i>
                </div>
                <div class="menu-title">Digital Health</div>
                <span class="menu-badge pulse">Live</span>
            </a>
            <ul>
                <li><a href="<?= base_url('Consultations/Entente') ?>" class="<?= $this->uri->segment(2) == 'Entente' && !$this->uri->segment(3) ? 'active' : '' ?>"><i class='bx bx-plus-medical'></i>Nouvelle Demande</a></li>
                <li><a href="<?= base_url('Consultations/Entente/confirme') ?>" class="<?= $this->uri->segment(3) == 'confirme' ? 'active' : '' ?>"><i class='bx bx-video'></i>Live Video</a></li>
                <li><a href="<?= base_url('Consultations') ?>" class="<?= $this->uri->segment(1) == 'Consultations' && !$this->uri->segment(2) ? 'active' : '' ?>"><i class='bx bx-history'></i>Historique</a></li>
                <li><a href="<?= base_url('Consultations/Consultation_chats') ?>" class="<?= $this->uri->segment(2) == 'Consultation_chats' ? 'active' : '' ?>"><i class='bx bx-chat'></i>Messages</a></li>
                <li><a href="<?= base_url('Documents') ?>" class="<?= $this->uri->segment(1) == 'Health' ? 'active' : '' ?>"><i class='bx bx-book-heart'></i>Ordonance</a></li>
                 <li><a href="<?= base_url('Prescriptions') ?>" class="<?= $this->uri->segment(1) == 'Health' ? 'active' : '' ?>"><i class='bx bx-book-heart'></i>Prescriptions</a></li>
            </ul>
        </li>
<?php endif; ?>
 <?php if (admin_view()): ?>
        <!-- E-Commerce -->
        <li class="<?= in_array($this->uri->segment(1), ['Categories','Produits','Produit_images','Adresses','Commandes','Paniers']) ? 'mm-active' : '' ?>">
            <a href="#" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bxs-store'></i>
                </div>
                <div class="menu-title">E-Commerce</div>
            </a>
            <ul>
                <li><a href="<?= base_url('Produits') ?>" class="<?= $this->uri->segment(1) == 'Produits' ? 'active' : '' ?>"><i class='bx bx-package'></i>Produits</a></li>
                <li><a href="<?= base_url('Categories') ?>" class="<?= $this->uri->segment(1) == 'Categories' ? 'active' : '' ?>"><i class='bx bx-category-alt'></i>Catégories</a></li>
                <li><a href="<?= base_url('Produit_images') ?>" class="<?= $this->uri->segment(1) == 'Produit_images' ? 'active' : '' ?>"><i class='bx bx-photo-album'></i>Images</a></li>
                <li><a href="<?= base_url('Produits/Workflow_categories') ?>" class="<?= $this->uri->segment(2) == 'Workflow_categories' ? 'active' : '' ?>"><i class='bx bx-git-branch'></i>Workflow categories</a></li>
                <li class="submenu-divider"></li>
                <li><a href="<?= base_url('Commandes') ?>" class="<?= $this->uri->segment(1) == 'Commandes' ? 'active' : '' ?>"><i class='bx bx-receipt'></i>Commandes</a></li>
                <li><a href="<?= base_url('Paniers') ?>" class="<?= $this->uri->segment(1) == 'Paniers' ? 'active' : '' ?>"><i class='bx bx-cart'></i>Paniers</a></li>
                <li><a href="<?= base_url('Adresses') ?>" class="<?= $this->uri->segment(1) == 'Adresses' ? 'active' : '' ?>"><i class='bx bx-map'></i>Adresses</a></li>
                <li><a href="<?= base_url('advertise-product') ?>" class="<?= $this->uri->segment(1) == 'advertise-product' ? 'active' : '' ?>"><i class='bx bx-map'></i>advertise product</a></li> 
                <li><a href="<?= base_url('product_categories') ?>" class="<?= $this->uri->segment(1) == 'product_categories' ? 'active' : '' ?>"><i class='bx bx-map'></i>product categories</a></li>
            </ul>
        </li>
<?php endif; ?>

 <?php if (admin_view()): ?>
        <!-- Investment -->
        <li class="<?= in_array($this->uri->segment(1), ['Investment']) ? 'mm-active' : '' ?>">
            <a href="#" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bxs-coin-stack'></i>
                </div>
                <div class="menu-title">Investissement</div>
            </a>
            <ul>
                <li><a href="<?= base_url('Investment/Requirements') ?>" class="<?= $this->uri->segment(2) == 'Requirements' ? 'active' : '' ?>"><i class='bx bx-list-check'></i>Requirements</a></li>
                <li><a href="<?= base_url('Investment/FundingStructure') ?>" class="<?= $this->uri->segment(2) == 'FundingStructure' ? 'active' : '' ?>"><i class='bx bx-money-withdraw'></i>Funding</a></li>
                <li><a href="<?= base_url('Investment/FinancialProjections') ?>" class="<?= $this->uri->segment(2) == 'FinancialProjections' ? 'active' : '' ?>"><i class='bx bx-trending-up'></i>Projections</a></li>
                <li><a href="<?= base_url('Investment/RiskMitigation') ?>" class="<?= $this->uri->segment(2) == 'RiskMitigation' ? 'active' : '' ?>"><i class='bx bx-shield-quarter'></i>Risques</a></li>
                <li><a href="<?= base_url('Brokers') ?>" class="<?= $this->uri->segment(2) == 'ExitStrategy' ? 'active' : '' ?>"><i class='bx bx-exit'></i>Brokers</a></li>
                <li><a href="<?= base_url('Investors') ?>" class="<?= $this->uri->segment(2) == 'ExitStrategy' ? 'active' : '' ?>"><i class='bx bx-exit'></i>Investisseurs</a></li>
            </ul>
        </li>

<?php endif; ?>

<?php if (admin_view()): ?>
        <!-- E-Commerce -->
        <li class="<?= in_array($this->uri->segment(1), ['Categories','Produits','Produit_images','Adresses','Commandes','Paniers']) ? 'mm-active' : '' ?>">
            <a href="#" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bxs-store'></i>
                </div>
                <div class="menu-title">Social Media</div>
            </a>
            <ul>
                <li><a href="<?= base_url('media/Video') ?>" class="<?= $this->uri->segment(1) == 'Video' ? 'active' : '' ?>"><i class='bx bx-package'></i>Video</a></li>
                <li><a href="<?= base_url('media/Audio') ?>" class="<?= $this->uri->segment(1) == 'Audio' ? 'active' : '' ?>"><i class='bx bx-category-alt'></i>Audio</a></li>
                <li><a href="<?= base_url('media/Autre/admin_liste') ?>" class="<?= $this->uri->segment(1) == 'Autre' ? 'active' : '' ?>"><i class='bx bx-photo-album'></i>Autre</a></li>
                <li><a href="<?= base_url('social') ?>" class="<?= $this->uri->segment(1) == 'social' ? 'active' : '' ?>"><i class='bx bx-photo-album'></i>Reseaux social</a></li>
            </ul>
        </li>
<?php endif; ?>
        <?php if (admin_view()): ?>
    <!-- Administration - Visible uniquement pour les admins -->
    <li class="<?= in_array($this->uri->segment(1), ['Users','Roles','Slides','Configurations','Newsletter']) ? 'mm-active' : '' ?>">
        <a href="#" class="has-arrow">
            <div class="parent-icon">
                <i class='bx bxs-cog'></i>
            </div>
            <div class="menu-title">Administration</div>
        </a>
        <ul>
            <li><a href="<?= base_url('users') ?>" class="<?= $this->uri->segment(1) == 'Users' ? 'active' : '' ?>"><i class='bx bx-user-circle'></i>Utilisateurs</a></li>
            <li><a href="<?= base_url('Roles') ?>" class="<?= $this->uri->segment(1) == 'Roles' ? 'active' : '' ?>"><i class='bx bx-lock-alt'></i>Rôles</a></li>
            <li><a href="<?= base_url('Users/Medecins') ?>" class="<?= $this->uri->segment(2) == 'Medecins' ? 'active' : '' ?>"><i class='bx bx-plus-medical'></i>Médecins</a></li>
            <li class="submenu-divider"></li>
            <li><a href="<?= base_url('Slides') ?>" class="<?= $this->uri->segment(1) == 'Slides' ? 'active' : '' ?>"><i class='bx bx-slideshow'></i>Slides</a></li>
            <li><a href="<?= base_url('Newsletter') ?>" class="<?= $this->uri->segment(1) == 'Newsletter' ? 'active' : '' ?>"><i class='bx bx-envelope'></i>Newsletter</a></li>
            <li><a href="<?= base_url('contact_us/Contact_Us') ?>" class="<?= $this->uri->segment(1) == 'Newsletter' ? 'active' : '' ?>"><i class='bx bx-envelope'></i>Contact_us</a></li>
            <li><a href="<?= base_url('Brokers') ?>" class="<?= $this->uri->segment(1) == 'Brokers' ? 'active' : '' ?>"><i class='bx bx-envelope'></i>Brokers</a></li>
            <li><a href="<?= base_url('Email_templates') ?>" class="<?= $this->uri->segment(1) == 'Email_templates' ? 'active' : '' ?>"><i class='bx bx-envelope'></i>Email_templates</a></li>
            <li><a href="<?= base_url('Publication') ?>" class="<?= $this->uri->segment(1) == 'Brokers' ? 'active' : '' ?>"><i class='bx bx-envelope'></i>ADVERTISE</a></li>
            <li><a href="<?= base_url('Investors') ?>" class="<?= $this->uri->segment(1) == 'Investors' ? 'active' : '' ?>"><i class='bx bx-envelope'></i>Investors</a></li>

           

            <li><a href="<?= base_url('Mode_payement') ?>" class="<?= $this->uri->segment(1) == 'Mode_payement' ? 'active' : '' ?>"><i class='bx bx-envelope'></i>Mode_payement</a></li>

            <li><a href="<?= base_url('Configurations') ?>" class="<?= $this->uri->segment(1) == 'Configurations' ? 'active' : '' ?>"><i class='bx bx-cog'></i>Paramètres</a></li>  
        </ul>
    </li>
<?php endif; ?>
    </ul>
    <!-- end navigation -->
    <?php if (admin_view()): ?>
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="footer-content">
            <div class="footer-stats">
                <div class="stat-item">
                    <span class="stat-value"><?= count($this->Model->read('pages', ['est_publiee' => 1])) ?></span>
                    <span class="stat-label">Pages</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($this->Model->read('produits')) ?></span>
                    <span class="stat-label">Produits</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($this->Model->read('users', ['is_active' => 1])) ?></span>
                    <span class="stat-label">Users</span>
                </div>
            </div>
            <div class="footer-version">
                <span>AGF CMS v2.0</span>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>
<!--end sidebar wrapper -->

<style type="text/css">
    /* ============================================
       AGF PHYTOMED SIDEBAR - Design System
       Couleurs: Vert forêt (#2d5a3d), Or (#d4af37), Blanc cassé (#f5f5f0)
       ============================================ */

    :root {
        --agf-primary: #2d5a3d;
        --agf-primary-dark: #1e3d2a;
        --agf-primary-light: #3d7a52;
        --agf-gold: #d4af37;
        --agf-gold-light: #e5c158;
        --agf-gold-dark: #b8960c;
        --agf-cream: #f5f5f0;
        --agf-dark: #1a1a1a;
        --agf-sidebar-bg: #1e2a1e;
        --agf-hover: rgba(212, 175, 55, 0.15);
    }

    /* ========== SIDEBAR CONTAINER ========== */
    .sidebar-wrapper {
        background: linear-gradient(180deg, var(--agf-sidebar-bg) 0%, #152015 100%);
        border-right: 1px solid rgba(212, 175, 55, 0.2);
        box-shadow: 4px 0 20px rgba(0,0,0,0.3);
    }

    /* ========== HEADER ========== */
    .sidebar-header {
        background: linear-gradient(135deg, var(--agf-primary-dark) 0%, var(--agf-primary) 100%);
        padding: 1.5rem 1.2rem;
        position: relative;
        border-bottom: 2px solid var(--agf-gold);
        overflow: hidden;
    }

    /* Texture feuillage subtile */
    .sidebar-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        
    }

    .logo-container {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 1;
    }

    .logo-badge {
        position: relative;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--agf-gold) 0%, var(--agf-gold-dark) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
        border: 2px solid rgba(255,255,255,0.3);
    }

    .logo-badge::before {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--agf-gold-light), transparent);
        z-index: -1;
        opacity: 0.6;
    }

    .logo-icon {
        width: 40px;
        height: 40px;
        object-fit: contain;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .brand-text {
        display: flex;
        flex-direction: column;
    }

    .brand-name {
        font-size: 1.25rem;
        font-weight: 800;
        color: #fff;
        margin: 0;
        line-height: 1.2;
        letter-spacing: 0.5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .brand-tagline {
        font-size: 0.75rem;
        color: var(--agf-gold);
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 600;
        margin-top: 2px;
    }

    /* Mobile toggle */
    .mobile-toggle-icon {
        width: 36px;
        height: 36px;
        background: rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid rgba(212, 175, 55, 0.3);
        color: var(--agf-gold);
        font-size: 1.5rem;
    }

    .mobile-toggle-icon:hover {
        background: var(--agf-gold);
        color: var(--agf-primary-dark);
        transform: rotate(90deg);
    }

    /* ========== MENU SECTIONS ========== */
    .metismenu {
        padding: 0.5rem 0;
    }

    .menu-section {
        padding: 1.5rem 1.5rem 0.5rem;
        margin-top: 0.5rem;
    }

    .menu-section:first-child {
        margin-top: 0;
    }

    .menu-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--agf-gold);
        opacity: 0.8;
        position: relative;
        padding-left: 12px;
    }

    .menu-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 4px;
        background: var(--agf-gold);
        border-radius: 50%;
    }

    /* ========== MENU ITEMS ========== */
    .metismenu li {
        margin: 2px 0;

    }

    .metismenu a {
        padding: 0.9rem 1.2rem;
        color: rgba(245, 245, 240, 0.85);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 0 8px 8px 0;
        margin-right: 12px;
        position: relative;
        overflow: hidden;

    }

    .metismenu a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--agf-gold);
        transform: scaleY(0);
        transition: transform 0.3s ease;

    }

    .metismenu a:hover,
    .metismenu a.active {
        background: var(--agf-hover);
        color: #fff;
        padding-left: 1.5rem;

    }

    .metismenu a:hover::before,
    .metismenu a.active::before {
        transform: scaleY(1);
        
    }

    .metismenu a.active {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.2) 0%, transparent 100%);
        color: var(--agf-gold-light);
        font-weight: 600;
    }

    .parent-icon {
        font-size: 1.3rem;
        min-width: 24px;
        text-align: center;
        color: var(--agf-gold);
        opacity: 0.9;
        transition: all 0.3s ease;
    }

    .metismenu a:hover .parent-icon,
    .metismenu a.active .parent-icon {
        opacity: 1;
        transform: scale(1.1);
        color: var(--agf-gold-light);
    }

    .menu-title {
        font-size: 0.9rem;
        font-weight: 500;
        flex: 1;
    }

    /* Badge de notification */
    .menu-badge {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4);
    }

    .menu-badge.pulse {
        animation: pulse-badge 2s infinite;
    }

    @keyframes pulse-badge {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }

    /* ========== SUBMENU ========== */
    .metismenu ul {
        background: rgba(0, 0, 0, 0.2);
        padding: 0.5rem 0;
        margin: 0 12px 0 0;
        border-radius: 0 0 8px 0;
    }

    .metismenu ul a {
        padding: 0.7rem 1.2rem 0.7rem 3rem;
        font-size: 0.85rem;
        color: rgba(245, 245, 240, 0.7);
    }

    .metismenu ul a i {
        font-size: 1rem;
        color: var(--agf-gold);
        opacity: 0.6;
        margin-right: 8px;
        transition: all 0.3s ease;
    }

    .metismenu ul a:hover,
    .metismenu ul a.active {
        color: #fff;
        background: rgba(212, 175, 55, 0.1);
        padding-left: 3.5rem;
    }

    .metismenu ul a:hover i,
    .metismenu ul a.active i {
        opacity: 1;
        color: var(--agf-gold-light);
    }

    .submenu-divider {
        height: 1px;
        background: rgba(212, 175, 55, 0.1);
        margin: 8px 1.2rem;
    }

    /* Arrow animation */
    .has-arrow::after {
        border-color: var(--agf-gold);
        opacity: 0.6;
        transition: all 0.3s ease;
    }

    .has-arrow[aria-expanded="true"]::after {
        transform: rotate(-135deg);
        opacity: 1;
    }

    /* ========== SIDEBAR FOOTER ========== */
    .sidebar-footer {
        margin-top: auto;
        padding: 1.2rem;
        border-top: 1px solid rgba(212, 175, 55, 0.2);
        background: rgba(0,0,0,0.2);
    }

    .footer-stats {
        display: flex;
        justify-content: space-around;
        margin-bottom: 1rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        display: block;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--agf-gold);
    }

    .stat-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        color: rgba(245, 245, 240, 0.6);
        letter-spacing: 0.5px;
    }

    .footer-version {
        text-align: center;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .footer-version span {
        font-size: 0.7rem;
        color: rgba(245, 245, 240, 0.4);
        letter-spacing: 1px;
    }

    /* ========== MINI SIDEBAR MODE ========== */
    .sidebar-mini .sidebar-header {
        padding: 1rem 0.5rem;
        justify-content: center;
    }

    .sidebar-mini .logo-container {
        flex-direction: column;
        gap: 8px;
    }

    .sidebar-mini .brand-text {
        display: none;
    }

    .sidebar-mini .logo-badge {
        width: 40px;
        height: 40px;
    }

    .sidebar-mini .logo-icon {
        width: 30px;
        height: 30px;
    }

    .sidebar-mini .menu-title,
    .sidebar-mini .menu-badge,
    .sidebar-mini .has-arrow::after {
        display: none;
    }

    .sidebar-mini .metismenu a {
        justify-content: center;
        padding: 1rem;
        margin: 0 4px;
    }

    .sidebar-mini .parent-icon {
        margin: 0;
        font-size: 1.4rem;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 991.98px) {
        .sidebar-header {
            padding: 1rem;
        }
        
        .brand-name {
            font-size: 1.1rem;
        }
        
        .logo-badge {
            width: 42px;
            height: 42px;
        }
    }

    /* Scrollbar personnalisée */
    .sidebar-wrapper .simplebar-scrollbar::before {
        background: var(--agf-gold);
        opacity: 0.5;
    }

    /* Effet de lueur dorée sur hover */
    .metismenu a:hover {
        text-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
    }
</style>