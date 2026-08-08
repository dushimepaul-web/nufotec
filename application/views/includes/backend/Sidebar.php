<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!--begin::Sidebar-->
<style>
    .app-sidebar { display: flex; flex-direction: column; }
    .app-sidebar .sidebar-brand { flex: 0 0 auto; }
    .app-sidebar .sidebar-wrapper { flex: 1 1 auto; min-height: 0; height: auto; overflow-y: auto; }
    .app-sidebar .sidebar-footer-stats { flex: 0 0 auto; border-top: 1px solid rgba(255, 255, 255, .08); }
</style>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <a href="<?= base_url('Dashboard') ?>" class="brand-link">
            <img src="<?= base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png')) ?>"
                 alt="AGF Phytomed Logo" class="brand-image opacity-75 shadow">
            <span class="brand-text fw-light"><?= $this->Model->get_setting('site_name', 'NUFOTEC') ?></span>
        </a>
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">

                <!-- SECTION: DASHBOARD -->
                <li class="nav-header">Tableau de Bord</li>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= base_url('Dashboard') ?>" class="nav-link <?= $this->uri->segment(1) == 'Dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php if (admin_view()): ?>
                <!-- SECTION: CONTENU DU SITE -->
                <li class="nav-header">Contenu du Site</li>
                <?php
                $content_count = static_pages_count() + count($this->Model->read('temoignages'));
                $content_active = in_array($this->uri->segment(1), ['Actualites','Partenaires','Temoignages','Chiffres_cles','Galerie_medias','Faq','Categories_produits']);
                ?>
                <li class="nav-item <?= $content_active ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $content_active ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-layers"></i>
                        <p>
                            Pages
                            <?php if($content_count > 0): ?><span class="nav-badge badge text-bg-danger"><?= $content_count > 99 ? '99+' : $content_count ?></span><?php endif; ?>
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= base_url('Actualites') ?>" class="nav-link <?= $this->uri->segment(1) == 'Actualites' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Actualités</p></a></li>
                        
                        <li class="nav-item"><a href="<?= base_url('Partenaires') ?>" class="nav-link <?= $this->uri->segment(1) == 'Partenaires' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Partenaires</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('Temoignages') ?>" class="nav-link <?= $this->uri->segment(1) == 'Temoignages' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Témoignages</p></a></li>
                       
                       
                        <li class="nav-item"><a href="<?= base_url('Chiffres_cles') ?>" class="nav-link <?= $this->uri->segment(1) == 'Chiffres_cles' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Chiffres Clés</p></a></li>
                        
                       

                        <li class="nav-item"><a href="<?= base_url('Faq') ?>" class="nav-link <?= $this->uri->segment(1) == 'Faq' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>FAQ</p></a></li>
                    
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (medecin_view() || admin_view() || user_dashboard_view()): ?>
                <!-- SECTION: DIGITAL HEALTH -->
                <li class="nav-header">Digital Health</li>
                <?php $health_active = in_array($this->uri->segment(1), ['Consultations','Health','home-patient']); ?>
                <li class="nav-item <?= $health_active ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $health_active ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-heart-pulse"></i>
                        <p>
                            Digital Health
                            <span class="nav-badge badge text-bg-success">Live</span>
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (user_dashboard_view()): ?>
                        <li class="nav-item"><a href="<?= base_url('home-patient') ?>" class="nav-link <?= $this->uri->segment(1) == 'home-patient' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Nouvelle Demande</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('home-patient') ?>" class="nav-link <?= $this->uri->segment(1) == 'home-patient' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Live Video</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('home-patient') ?>" class="nav-link <?= $this->uri->segment(1) == 'home-patient' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Historique</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('home-patient') ?>" class="nav-link <?= $this->uri->segment(1) == 'home-patient' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Messages</p></a></li>
                        <?php else: ?>
                        <li class="nav-item"><a href="<?= base_url('Consultations/Entente') ?>" class="nav-link <?= $this->uri->segment(2) == 'Entente' && !$this->uri->segment(3) ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Nouvelle Demande</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('Consultations/Entente/confirme') ?>" class="nav-link <?= $this->uri->segment(3) == 'confirme' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Live Video</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('Consultations') ?>" class="nav-link <?= $this->uri->segment(1) == 'Consultations' && !$this->uri->segment(2) ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Historique</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('Consultations/Consultation_chats') ?>" class="nav-link <?= $this->uri->segment(2) == 'Consultation_chats' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Messages</p></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (admin_view()): ?>
                <!-- SECTION: E-COMMERCE -->
                <li class="nav-header">E-Commerce</li>
                <?php $ecommerce_active = in_array($this->uri->segment(1), ['commande_whatsapp','advertise-product','product_categories']); ?>
                <li class="nav-item <?= $ecommerce_active ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $ecommerce_active ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-bag"></i>
                        <p>
                            E-Commerce
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= base_url('commande_whatsapp') ?>" class="nav-link <?= $this->uri->segment(1) == 'commande_whatsapp' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>commande whatsapp</p></a></li>

                        <li class="nav-item"><a href="<?= base_url('product-categories') ?>" class="nav-link <?= $this->uri->segment(1) == 'product-categories' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>product categories</p></a></li>
                        
                        <li class="nav-item"><a href="<?= base_url('advertise-product') ?>" class="nav-link <?= $this->uri->segment(1) == 'advertise-product' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>advertise product</p></a></li>
                        
                    </ul>
                </li>

                <!-- SECTION: INVESTMENT -->
                <li class="nav-header">Investissement</li>
                <?php $investment_active = in_array($this->uri->segment(1), ['Investissement_phases','Brokers','Investors']); ?>
                <li class="nav-item <?= $investment_active ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $investment_active ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>
                            Investissement
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        
                        <li class="nav-item"><a href="<?= base_url('Investissement_phases') ?>" class="nav-link <?= $this->uri->segment(1) == 'Investissement_phases' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Phases</p></a></li>
                        
                        <li class="nav-item"><a href="<?= base_url('Brokers') ?>" class="nav-link <?= $this->uri->segment(1) == 'Brokers' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Brokers</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('Investors') ?>" class="nav-link <?= $this->uri->segment(1) == 'Investors' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Investisseurs</p></a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (investor_view() && !admin_view()): ?>
                <!-- SECTION: MON ESPACE INVESTISSEUR -->
                <li class="nav-header">Investissement</li>
                <li class="nav-item <?= $this->uri->segment(1) == 'Dashboard' || $this->uri->segment(1) == 'investor' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $this->uri->segment(1) == 'Dashboard' || $this->uri->segment(1) == 'investor' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>
                            Mon Espace
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= base_url('Dashboard') ?>" class="nav-link <?= $this->uri->segment(2) == 'investisseur' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Mon espace</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('investor') ?>" class="nav-link <?= $this->uri->segment(1) == 'investor' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Mon profil</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('investment-projection') ?>" class="nav-link <?= $this->uri->segment(1) == 'investment-projection' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Projections</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('investor-commitment') ?>" class="nav-link <?= $this->uri->segment(1) == 'investor-commitment' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Engagements</p></a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (broker_view() && !admin_view()): ?>
                <!-- SECTION: COURTier -->
                <li class="nav-header">Courtier</li>
                <li class="nav-item <?= in_array($this->uri->segment(1), ['broker','Broker']) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= in_array($this->uri->segment(1), ['broker','Broker']) ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-briefcase"></i>
                        <p>
                            Courtier
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= base_url('broker/dashboard') ?>" class="nav-link <?= $this->uri->segment(2) == 'dashboard' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Mon tableau de bord</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('broker') ?>" class="nav-link <?= $this->uri->segment(1) == 'broker' && !$this->uri->segment(2) ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Mon profil</p></a></li>
                    </ul>
                </li>
                <?php endif; ?>






<?php if (admin_view()): ?>
                <!-- SECTION: MÉDIAS -->
                <li class="nav-header">Médias</li>
                <?php $media_active = $this->uri->segment(2) == 'media'; ?>
                <li class="nav-item <?= $media_active ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $media_active ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-images"></i>
                        <p>
                            Médias
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= base_url('admin/media') ?>" class="nav-link <?= $media_active && !$this->uri->segment(3) ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Tous</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('admin/media/index/audio') ?>" class="nav-link <?= $this->uri->segment(3) == 'audio' ? 'active' : '' ?>"><i class="nav-icon bi bi-music-note-beamed"></i><p>Audio</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('admin/media/index/video') ?>" class="nav-link <?= $this->uri->segment(3) == 'video' ? 'active' : '' ?>"><i class="nav-icon bi bi-camera-video"></i><p>Vidéo</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('admin/media/index/image') ?>" class="nav-link <?= $this->uri->segment(3) == 'image' ? 'active' : '' ?>"><i class="nav-icon bi bi-image"></i><p>Image</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('admin/media/index/document') ?>" class="nav-link <?= $this->uri->segment(3) == 'document' ? 'active' : '' ?>"><i class="nav-icon bi bi-file-earmark-text"></i><p>Documents</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('admin/media/index/link') ?>" class="nav-link <?= $this->uri->segment(3) == 'link' ? 'active' : '' ?>"><i class="nav-icon bi bi-link-45deg"></i><p>Liens</p></a></li>
                    </ul>
                </li>
                <?php endif; ?>








                <?php if (admin_view()): ?>
            

                <!-- SECTION: ADMINISTRATION -->
                <li class="nav-header">Administration</li>
                <?php $admin_active = in_array($this->uri->segment(1), ['Users','Roles','Slides','Configurations','Newsletter','contact_us','Brokers','Email_templates','Publication','Investors','Mode_payement']); ?>
                <li class="nav-item <?= $admin_active ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $admin_active ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>
                            Administration
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= base_url('users') ?>" class="nav-link <?= $this->uri->segment(1) == 'users' && !$this->uri->segment(2) ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Utilisateurs</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('Roles') ?>" class="nav-link <?= $this->uri->segment(1) == 'Roles' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Rôles</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('users/Medecins') ?>" class="nav-link <?= strtolower($this->uri->segment(1)) == 'users' && $this->uri->segment(2) == 'Medecins' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Médecins</p></a></li>
                        
                        <li class="nav-item"><a href="<?= base_url('Newsletter') ?>" class="nav-link <?= $this->uri->segment(1) == 'Newsletter' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Newsletter</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('contact_us/Contact_Us') ?>" class="nav-link <?= $this->uri->segment(1) == 'contact_us' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Contact_us</p></a></li>
                       
                        <li class="nav-item"><a href="<?= base_url('Mode_payement') ?>" class="nav-link <?= $this->uri->segment(1) == 'Mode_payement' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Mode_payement</p></a></li>
                        <li class="nav-item"><a href="<?= base_url('Configurations') ?>" class="nav-link <?= $this->uri->segment(1) == 'Configurations' ? 'active' : '' ?>"><i class="nav-icon bi bi-circle"></i><p>Paramètres</p></a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->