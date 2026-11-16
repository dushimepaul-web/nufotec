

<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<style>
/* Page Header */
.page-header {
background: linear-gradient(135deg, #0B4F2E 0%, #1B7B4B 100%);
padding: 120px 0 60px;
position: relative;
overflow: hidden;
}

.page-header::before {
content: '';
position: absolute;
top: 0;
left: 0;
right: 0;
bottom: 0;
background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><path fill="white" d="M11.4,41.8c-0.8,0-1.5-0.3-2.1-0.9c-1.2-1.2-1.2-3.1,0-4.2l9-9c1.2-1.2,3.1-1.2,4.2,0c1.2,1.2,1.2,3.1,0,4.2l-9,9C12.9,41.5,12.2,41.8,11.4,41.8z M29.4,60.8c-0.8,0-1.5-0.3-2.1-0.9c-1.2-1.2-1.2-3.1,0-4.2l9-9c1.2-1.2,3.1-1.2,4.2,0c1.2,1.2,1.2,3.1,0,4.2l-9,9C30.9,60.5,30.2,60.8,29.4,60.8z M47.4,42.8c-0.8,0-1.5-0.3-2.1-0.9c-1.2-1.2-1.2-3.1,0-4.2l9-9c1.2-1.2,3.1-1.2,4.2,0c1.2,1.2,1.2,3.1,0,4.2l-9,9C48.9,42.5,48.2,42.8,47.4,42.8z M65.4,60.8c-0.8,0-1.5-0.3-2.1-0.9c-1.2-1.2-1.2-3.1,0-4.2l9-9c1.2-1.2,3.1-1.2,4.2,0c1.2,1.2,1.2,3.1,0,4.2l-9,9C66.9,60.5,66.2,60.8,65.4,60.8z M83.4,42.8c-0.8,0-1.5-0.3-2.1-0.9c-1.2-1.2-1.2-3.1,0-4.2l9-9c1.2-1.2,3.1-1.2,4.2,0c1.2,1.2,1.2,3.1,0,4.2l-9,9C84.9,42.5,84.2,42.8,83.4,42.8z"/></svg>') repeat;
}

.page-header-content {
text-align: center;
color: white;
position: relative;
z-index: 2;
}

.page-header h1 {
font-size: 48px;
font-weight: 800;
margin-bottom: 15px;
}

.page-header h1 span {
color: #FFD700;
}

.page-header p {
font-size: 18px;
opacity: 0.95;
max-width: 700px;
margin: 0 auto;
}

.breadcrumb {
background: transparent;
justify-content: center;
margin-top: 20px;
}

.breadcrumb-item {
color: rgba(255, 255, 255, 0.8);
}

.breadcrumb-item a {
color: white;
text-decoration: none;
}

.breadcrumb-item.active {
color: #FFD700;
}

/* Section Title */
.section-title {
text-align: center;
margin-bottom: 50px;
}

.section-title h2 {
font-size: 36px;
font-weight: 700;
color: #1a2e3f;
margin-bottom: 15px;
}

.section-title h2 span {
color: #27ae60;
}

.section-title p {
color: #6c757d;
font-size: 18px;
max-width: 700px;
margin: 0 auto;
}
</style>

<!-- Page Header -->
<section class="page-header">
<div class="container">
<div class="page-header-content" data-aos="fade-up">
<h1>Relations <span>Investisseurs</span></h1>
<p>Transparence financière et informations dédiées à nos partenaires</p>
<nav aria-label="breadcrumb">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="index.html">Accueil</a></li>
<li class="breadcrumb-item"><a href="page8-investissement.html">Investissement</a></li>
<li class="breadcrumb-item active" aria-current="page">Relations Investisseurs</li>
</ol>
</nav>
</div>
</div>
</section>



 <!-- ============================================
    Voici la section Strategic Overview corrigée, optimisée et allégée, en m'appuyant sur votre base de données réelle (configurations, project_phases, facility_details, etc.) :
     ============================================ -->

<!-- ============================================
     SECTION STRATEGIC OVERVIEW - AGF PHYTOMED
     ============================================ -->
<section class="strategic-overview py-5">
    <div class="container">
        <!-- En-tête -->
        <div class="section-header text-center mb-5">
            <span class="section-subtitle">Strategic Overview</span>
            <h2 class="section-title">Notre Vision Stratégique</h2>
            <div class="title-separator">
                <span class="separator-line"></span>
                <i class="bi bi-bullseye"></i>
                <span class="separator-line"></span>
            </div>
            <p class="section-description mx-auto">
                AGF-PHYTOMED positionne le Burundi comme un hub d'excellence agro-industrielle et phytomédicinale. 
                Notre stratégie intègre recherche de pointe, production durable et accès équitable aux soins.
            </p>
        </div>

        <!-- 4 Piliers Stratégiques (Basés sur configurations DB) -->
        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <i class="bi bi-flask"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>Recherche & Développement de molécules issues de la pharmacopée traditionnelle</p>
                    <div class="pillar-stats">
                        <span>7 brevets</span>
                        <span>en cours</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <i class="bi bi-tree"></i>
                    </div>
                    <h3>Durabilité</h3>
                    <p>Agriculture biologique et préservation de la biodiversité locale</p>
                    <div class="pillar-stats">
                        <span>50 ha</span>
                        <span>certifiés bio</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <i class="bi bi-building-gear"></i>
                    </div>
                    <h3>Industrie</h3>
                    <p>Unités de production aux normes BPF (Bonnes Pratiques de Fabrication)</p>
                    <div class="pillar-stats">
                        <span>10,000 tonnes</span>
                        <span>capacité/an</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <h3>Impact</h3>
                    <p>Accès aux soins pour les populations à revenu modeste</p>
                    <div class="pillar-stats">
                        <span>2M+</span>
                        <span>patients ciblés</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avantages Compétitifs + Données Facility (DB: facility_details) -->
        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <div class="vision-content">
                    <h3 class="vision-title">Notre Avantage Compétitif</h3>
                    
                    <div class="advantage-item">
                        <div class="advantage-number">01</div>
                        <div class="advantage-text">
                            <h4>Intégration Verticale</h4>
                            <p>De la graine au médicament : contrôle total de la chaîne de valeur sur 50 hectares</p>
                        </div>
                    </div>
                    
                    <div class="advantage-item">
                        <div class="advantage-number">02</div>
                        <div class="advantage-text">
                            <h4>Innovation Locale</h4>
                            <p>Valorisation des savoirs traditionnels burundais avec laboratoires analytiques modernes</p>
                        </div>
                    </div>
                    
                    <div class="advantage-item">
                        <div class="advantage-number">03</div>
                        <div class="advantage-text">
                            <h4>Positionnement Géographique</h4>
                            <p>Muyinga, Burundi : accès privilégié aux matières premières et marchés régionaux</p>
                        </div>
                    </div>
                    
                    <div class="advantage-item">
                        <div class="advantage-number">04</div>
                        <div class="advantage-text">
                            <h4>Modèle Économique Durable</h4>
                            <p>ROI estimé à 25% sur 5 ans avec $5M d'investissement requis</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="facility-showcase">
                    <div class="facility-image">
                        <img src="assets/images/facility-layout.jpg" alt="AGF Facility Layout" class="img-fluid rounded-4 shadow-lg">
                        <div class="facility-badge">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Muyinga, Burundi</span>
                        </div>
                    </div>
                    <div class="facility-specs row g-2 mt-3">
                        <div class="col-6">
                            <div class="spec-item">
                                <i class="bi bi-rulers"></i>
                                <span>50 ha Superficie</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="spec-item">
                                <i class="bi bi-droplet"></i>
                                <span>Accès Eau garanti</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="spec-item">
                                <i class="bi bi-lightning"></i>
                                <span>Électricité stable</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="spec-item">
                                <i class="bi bi-truck"></i>
                                <span>Routes d'accès</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Stratégique (DB: project_phases) -->
        <div class="strategic-timeline mt-5">
            <h3 class="text-center mb-4">Feuille de Route 2024-2028</h3>
            
            <div class="timeline-wrapper">
                <div class="timeline-progress">
                    <div class="progress-fill" style="width: 35%"></div>
                </div>
                
                <div class="timeline-items">
                    <div class="timeline-item completed">
                        <div class="timeline-marker">
                            <span class="year">2024</span>
                            <span class="dot"></span>
                        </div>
                        <div class="timeline-card">
                            <h4>Phase 1: Fondation</h4>
                            <ul>
                                <li>Construction laboratoires R&D</li>
                                <li>Lancement cultures pilotes</li>
                                <li>Dépôt brevets initiaux</li>
                            </ul>
                            <span class="status completed">Terminé</span>
                        </div>
                    </div>
                    
                    <div class="timeline-item active">
                        <div class="timeline-marker">
                            <span class="year">2025</span>
                            <span class="dot"></span>
                        </div>
                        <div class="timeline-card">
                            <h4>Phase 2: Lancement</h4>
                            <ul>
                                <li>Commercialisation produits</li>
                                <li>Extension 50 ha</li>
                                <li>Certification BPF</li>
                            </ul>
                            <span class="status in-progress">En cours</span>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <span class="year">2026</span>
                            <span class="dot"></span>
                        </div>
                        <div class="timeline-card">
                            <h4>Phase 3: Expansion</h4>
                            <ul>
                                <li>Nouveaux produits</li>
                                <li>Export régional</li>
                                <li>Plateforme e-santé</li>
                            </ul>
                            <span class="status pending">Prévu</span>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <span class="year">2027-28</span>
                            <span class="dot"></span>
                        </div>
                        <div class="timeline-card">
                            <h4>Phase 4: Rayonnement</h4>
                            <ul>
                                <li>Centre d'excellence</li>
                                <li>Partenariats internationaux</li>
                                <li>Pleine capacité</li>
                            </ul>
                            <span class="status pending">Prévu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Objectifs Chiffrés (DB: configurations) -->
        <div class="objectives-section mt-5">
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="objective-card">
                        <div class="objective-icon">
                            <i class="bi bi-capsule"></i>
                        </div>
                        <div class="objective-number">7</div>
                        <div class="objective-label">Produits phares</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-6">
                    <div class="objective-card">
                        <div class="objective-icon">
                            <i class="bi bi-tree"></i>
                        </div>
                        <div class="objective-number">50 ha</div>
                        <div class="objective-label">Plantations bio</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-6">
                    <div class="objective-card">
                        <div class="objective-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="objective-number">500+</div>
                        <div class="objective-label">Emplois créés</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-6">
                    <div class="objective-card">
                        <div class="objective-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="objective-number">25%</div>
                        <div class="objective-label">ROI 5 ans</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="strategic-cta text-center mt-5">
            <div class="cta-box">
                <h3>Prêt à investir dans l'excellence agro-industrielle ?</h3>
                <p>Rejoignez notre projet avec un ticket minimum d'investissement défini selon votre profil</p>
                <div class="cta-buttons">
                    <a href="<?= base_url('documents/investment-memorandum') ?>" class="btn-cta-primary">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Télécharger le Memorandum
                    </a>
                    <a href="<?= base_url('contact?type=investissement') ?>" class="btn-cta-secondary">
                        <i class="bi bi-calendar-check"></i>
                        Devenir Investisseur
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ============================================
   STRATEGIC OVERVIEW - CLEAN & OPTIMIZED
   ============================================ */
:root {
    --primary-green: #1a5f2a;
    --secondary-green: #2d8a4e;
    --accent-green: #27ae60;
    --light-green: #52d681;
    --bg-light: #f8faf9;
    --text-dark: #2c3e50;
    --text-muted: #6c757d;
    --border-green: rgba(39, 174, 96, 0.2);
}

.strategic-overview {
    background: linear-gradient(135deg, #ffffff 0%, var(--bg-light) 100%);
    padding: 80px 0;
}

/* Header */
.section-subtitle {
    display: inline-block;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--accent-green);
    margin-bottom: 10px;
}

.section-title {
    font-size: 38px;
    font-weight: 800;
    color: var(--primary-green);
    margin-bottom: 15px;
}

.title-separator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin: 20px 0;
}

.separator-line {
    width: 50px;
    height: 2px;
    background: var(--accent-green);
}

.section-description {
    max-width: 700px;
    font-size: 16px;
    line-height: 1.7;
    color: var(--text-muted);
}

/* Pillar Cards */
.pillar-card {
    background: white;
    border-radius: 16px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: 1px solid var(--border-green);
    height: 100%;
    transition: all 0.3s ease;
}

.pillar-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(39, 174, 96, 0.12);
}

.pillar-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(145deg, var(--accent-green), var(--light-green));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 30px;
    color: white;
}

.pillar-card h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 12px;
    color: var(--primary-green);
}

.pillar-card p {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 15px;
}

.pillar-stats {
    background: rgba(39, 174, 96, 0.08);
    padding: 10px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    color: var(--accent-green);
}

.pillar-stats span:last-child {
    font-size: 11px;
    font-weight: 400;
    color: var(--text-muted);
    display: block;
}

/* Vision Content */
.vision-content {
    padding: 20px;
}

.vision-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 25px;
}

.advantage-item {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    padding: 18px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}

.advantage-item:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 25px rgba(39, 174, 96, 0.08);
}

.advantage-number {
    font-size: 28px;
    font-weight: 800;
    color: rgba(39, 174, 96, 0.15);
    line-height: 1;
    min-width: 40px;
}

.advantage-text h4 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 5px;
    color: var(--text-dark);
}

.advantage-text p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.5;
}

/* Facility Showcase */
.facility-showcase {
    padding: 20px;
}

.facility-image {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
}

.facility-image img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.facility-badge {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: rgba(26, 95, 42, 0.95);
    color: white;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.spec-item {
    background: white;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    font-size: 13px;
    color: var(--text-dark);
    border: 1px solid var(--border-green);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.spec-item i {
    color: var(--accent-green);
    font-size: 16px;
}

/* Timeline */
.strategic-timeline {
    background: white;
    padding: 40px;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

.timeline-wrapper {
    position: relative;
    padding-top: 20px;
}

.timeline-progress {
    position: absolute;
    top: 35px;
    left: 0;
    right: 0;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent-green), var(--light-green));
    border-radius: 2px;
}

.timeline-items {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.timeline-item {
    flex: 1;
    text-align: center;
    max-width: 220px;
}

.timeline-marker {
    margin-bottom: 15px;
}

.year {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.dot {
    display: inline-block;
    width: 14px;
    height: 14px;
    background: #dee2e6;
    border: 3px solid white;
    border-radius: 50%;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item.completed .dot,
.timeline-item.active .dot {
    background: var(--accent-green);
    box-shadow: 0 0 0 2px var(--accent-green);
}

.timeline-item.active .dot {
    transform: scale(1.2);
}

.timeline-card {
    background: var(--bg-light);
    padding: 18px;
    border-radius: 12px;
    text-align: left;
    margin: 0 8px;
    border: 1px solid var(--border-green);
}

.timeline-card h4 {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--primary-green);
}

.timeline-card ul {
    list-style: none;
    padding: 0;
    margin: 0 0 12px;
}

.timeline-card li {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 4px;
    padding-left: 12px;
    position: relative;
}

.timeline-card li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: var(--accent-green);
}

.status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.status.completed {
    background: #d4edda;
    color: #155724;
}

.status.in-progress {
    background: #fff3cd;
    color: #856404;
}

.status.pending {
    background: #e9ecef;
    color: var(--text-muted);
}

/* Objectives */
.objective-card {
    background: white;
    border-radius: 16px;
    padding: 25px 15px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    border: 1px solid var(--border-green);
    transition: all 0.3s ease;
}

.objective-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(39, 174, 96, 0.1);
}

.objective-icon {
    width: 50px;
    height: 50px;
    background: rgba(39, 174, 96, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 24px;
    color: var(--accent-green);
}

.objective-number {
    font-size: 28px;
    font-weight: 800;
    color: var(--primary-green);
    line-height: 1;
}

.objective-label {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 5px;
}

/* CTA */
.cta-box {
    background: linear-gradient(145deg, var(--primary-green), var(--secondary-green));
    padding: 50px 40px;
    border-radius: 24px;
    color: white;
    position: relative;
    overflow: hidden;
}

.cta-box::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.cta-box h3 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 12px;
    position: relative;
    z-index: 2;
}

.cta-box p {
    font-size: 16px;
    opacity: 0.9;
    margin-bottom: 25px;
    position: relative;
    z-index: 2;
}

.cta-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    position: relative;
    z-index: 2;
    flex-wrap: wrap;
}

.btn-cta-primary, .btn-cta-secondary {
    padding: 14px 28px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-cta-primary {
    background: white;
    color: var(--primary-green);
}

.btn-cta-secondary {
    background: transparent;
    color: white;
    border: 2px solid rgba(255,255,255,0.5);
}

.btn-cta-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    background: var(--light-green);
    color: white;
}

.btn-cta-secondary:hover {
    background: white;
    color: var(--primary-green);
    border-color: white;
}

/* Responsive */
@media (max-width: 991px) {
    .section-title { font-size: 32px; }
    .timeline-items { flex-wrap: wrap; gap: 20px; justify-content: center; }
    .timeline-item { max-width: 45%; }
    .timeline-progress { display: none; }
    .cta-box { padding: 40px 25px; }
    .cta-box h3 { font-size: 24px; }
}

@media (max-width: 768px) {
    .section-title { font-size: 26px; }
    .timeline-item { max-width: 100%; }
    .vision-content { padding: 10px; }
    .facility-showcase { margin-top: 30px; }
    .btn-cta-primary, .btn-cta-secondary { width: 100%; justify-content: center; }
}
</style>


<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>