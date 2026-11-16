<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== RESEARCH & INNOVATION — STATIC VIEW ===================== -->
<style>
    .rni-wrap{background:#fff}
    .rni-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .rni-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .rni-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .rni-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .rni-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .rni-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .rni-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.4vw,2.5rem);line-height:1.25;margin:0 0 10px}
    .rni-hero h1 .rni-brace{color:#D4A017}
    .rni-hero .rni-lead{position:relative;z-index:2;max-width:860px;font-size:.95rem;line-height:1.8;color:rgba(255,255,255,.85);margin:0}
    .rni-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin:16px 0 0;position:relative;z-index:2}
    .rni-body{padding:52px 0 70px}
    .rni-intro{background:#F5F9F6;border:1px solid #E3EDE6;border-left:5px solid #D4A017;border-radius:12px;padding:26px 30px;margin:0 0 44px}
    .rni-intro p{margin:0;font-size:.98rem;line-height:1.85;color:#2c3e35}
    .rni-sec-title{display:flex;align-items:center;gap:14px;margin:0 0 8px}
    .rni-sec-title .rni-num{flex:0 0 auto;width:46px;height:46px;display:flex;align-items:center;justify-content:center;background:#083D2A;color:#fff;font-weight:700;font-size:1.05rem;border-radius:12px;box-shadow:0 6px 16px rgba(8,61,42,.22)}
    .rni-sec-title h2{margin:0;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.25rem,2vw,1.6rem);color:#083D2A}
    .rni-sub{margin:0 0 30px;padding-left:60px;color:#5b6f62;font-size:.95rem}
    .rni-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:24px}
    .rni-card{position:relative;background:#fff;border:1px solid #E3EDE6;border-radius:14px;padding:30px 26px 26px;box-shadow:0 8px 24px rgba(8,61,42,.06);transition:.25s}
    .rni-card:hover{transform:translateY(-4px);box-shadow:0 14px 34px rgba(8,61,42,.13);border-color:#c9dfd1}
    .rni-card .rni-badge{position:absolute;top:-17px;left:26px;min-width:42px;height:42px;padding:0 8px;display:flex;align-items:center;justify-content:center;background:#D4A017;color:#083D2A;font-weight:700;font-size:1rem;border-radius:10px;box-shadow:0 6px 14px rgba(212,160,23,.35)}
    .rni-card h3{margin:6px 0 12px;padding-top:6px;font-family:'Poppins',sans-serif;font-weight:600;font-size:1.08rem;color:#083D2A;line-height:1.4}
    .rni-card .rni-txt{color:#3d4f45;font-size:.92rem;line-height:1.75;margin:0 0 12px}
    .rni-list{margin:0;padding:0;list-style:none}
    .rni-list li{position:relative;padding:7px 0 7px 28px;color:#3d4f45;font-size:.9rem;line-height:1.6}
    .rni-list li::before{content:"\f058";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;left:2px;top:9px;font-size:.82rem;color:#D4A017}
    .rni-note{margin:14px 0 0;padding:12px 16px;background:#FBF7EA;border-left:3px solid #D4A017;border-radius:8px;font-size:.88rem;line-height:1.7;color:#6b5d2e}
    .rni-card--wide{grid-column:1 / -1}
    .rni-steps{display:grid;grid-template-columns:repeat(7,1fr);gap:12px;margin:0 0 16px}
    .rni-step{background:#F5F9F6;border:1px solid #E3EDE6;border-radius:10px;padding:14px 12px;text-align:center}
    .rni-step .rni-step-n{font-size:.75rem;color:#D4A017;font-weight:700;letter-spacing:.6px}
    .rni-step p{margin:6px 0 0;font-size:.78rem;line-height:1.5;color:#3d4f45}
    @media(max-width:992px){.rni-grid{grid-template-columns:1fr}.rni-steps{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:576px){.rni-sub{padding-left:0}.rni-steps{grid-template-columns:1fr}}
</style>

<section class="rni-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="rni-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Recherche &amp; Innovation</span>
            </div>
            <h1>Recherche <span class="rni-brace">{</span> Innovation</h1>
            <p class="rni-lead">
                Chez NUFOTEC, la recherche et l'innovation constituent l'épine dorsale scientifique de notre modèle
                intégré verticalement – de la culture durable de plantes médicinales et de cultures fonctionnelles au
                développement de MTCAs standardisées, de nutraceutiques/compléments alimentaires, d'aliments santé
                fortifiés clean-label et de boissons aux fruits, de phytomédicines et phytopharmaceutiques basés sur la
                recherche préclinique, de produits phytopharmaceutiques cliniquement validés pour les marchés
                domestiques, régionaux et internationaux. Notre stratégie de recherche intègre l'intelligence
                ethnobotanique, la pharmacognosie moderne, la phytochimie, la biologie moléculaire, la biotechnologie et
                les sciences précliniques translationnelles pour transformer les bioressources indigènes en produits de
                santé fondés sur des preuves et conformes à la réglementation.
            </p>
            <div class="rni-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="rni-body">
        <div class="container">

            <!-- 1. Architecture de recherche intégrée -->
            <div class="rni-sec-title">
                <div class="rni-num">1</div>
                <h2>Architecture de recherche intégrée</h2>
            </div>
            <p class="rni-sub">Notre écosystème d'innovation est structuré autour de cinq piliers techniques :</p>

            <div class="rni-grid">

                <!-- A -->
                <div class="rni-card">
                    <div class="rni-badge">A</div>
                    <h3>Intelligence ethnobotanique et valorisation de la biodiversité</h3>
                    <p class="rni-txt">
                        Nous documentons, validons et priorisons systématiquement les espèces de plantes médicinales en
                        utilisant des critères de criblage basés sur des preuves, en nous concentrant sur les maladies à
                        forte charge de morbidité, les troubles métaboliques, la santé immunitaire et la nutrition
                        préventive. Cela garantit à la fois une pertinence scientifique et une forte viabilité
                        commerciale.
                    </p>
                </div>

                <!-- B -->
                <div class="rni-card">
                    <div class="rni-badge">B</div>
                    <h3>Standardisation phytochimique et profilage moléculaire</h3>
                    <p class="rni-txt">
                        En utilisant des plateformes analytiques avancées (UHPLC-MS/MS, GC-MS/MS 8000, HPAAS, AH SN600
                        Laser Scanning Microscope, RT-qPCR, et High-Parameter Digital Flow Cytometer), nous :
                    </p>
                    <ul class="rni-list">
                        <li>Identifions les composés marqueurs actifs</li>
                        <li>Établissons des empreintes phytochimiques quantitatives</li>
                        <li>Garantissons la reproductibilité lot à lot</li>
                        <li>Développons des extraits standardisés répondant aux seuils pharmacopéiques internationaux</li>
                    </ul>
                    <p class="rni-note">
                        Cette rigueur scientifique différencie nos produits des préparations à base de plantes
                        traditionnelles et les positionne comme des formulations standardisées de qualité
                        phytopharmaceutique.
                    </p>
                </div>

                <!-- C -->
                <div class="rni-card">
                    <div class="rni-badge">C</div>
                    <h3>Technologies de bioprocédés et de bio-amélioration</h3>
                    <p class="rni-txt">
                        Nous appliquons des systèmes de fermentation contrôlée, des biotechnologies microbiennes (y
                        compris des souches de LAB sélectionnées le cas échéant) et des technologies d'extraction verte
                        pour :
                    </p>
                    <ul class="rni-list">
                        <li>Augmenter la biodisponibilité</li>
                        <li>Améliorer la concentration des composés actifs</li>
                        <li>Améliorer la stabilité et la durée de conservation</li>
                        <li>Optimiser les performances pharmacodynamiques</li>
                    </ul>
                </div>

                <!-- D -->
                <div class="rni-card">
                    <div class="rni-badge">D</div>
                    <h3>Recherche préclinique et translationnelle</h3>
                    <p class="rni-txt">
                        Grâce à notre plateforme de recherche sur les animaux de laboratoire prévue, nous menons :
                    </p>
                    <ul class="rni-list">
                        <li>Des profils toxicologiques</li>
                        <li>Des études pharmacocinétiques et pharmacodynamiques</li>
                        <li>Une optimisation des doses</li>
                        <li>Une validation du mécanisme d'action</li>
                    </ul>
                    <p class="rni-note">
                        Cela fait le pont entre les connaissances traditionnelles et la médecine factuelle et soutient
                        les voies d'approbation réglementaire.
                    </p>
                </div>

                <!-- E -->
                <div class="rni-card">
                    <div class="rni-badge">E</div>
                    <h3>Développement de produits et alignement réglementaire</h3>
                    <p class="rni-txt">
                        Nous concevons des formulations alignées sur :
                    </p>
                    <ul class="rni-list">
                        <li>La Stratégie de Médecine Traditionnelle de l'OMS</li>
                        <li>Les cadres d'harmonisation de l'Agence Africaine du Médicament</li>
                        <li>Les normes internationales BPF</li>
                        <li>Les référentiels de conformité ESG et de durabilité</li>
                    </ul>
                    <p class="rni-note">
                        Chaque produit progresse à travers des Niveaux de Maturité Technologique (TRL) définis,
                        garantissant des jalons clairs de réduction des risques pour les investisseurs.
                    </p>
                </div>

                <!-- F -->
                <div class="rni-card rni-card--wide">
                    <div class="rni-badge">F</div>
                    <h3>Pipeline de la recherche au marché</h3>
                    <p class="rni-txt">Notre pipeline d'innovation suit un modèle structuré en 7 étapes :</p>
                    <div class="rni-steps">
                        <div class="rni-step">
                            <div class="rni-step-n">ÉTAPE 1</div>
                            <p>Cartographie de la biodiversité &amp; sélection des espèces</p>
                        </div>
                        <div class="rni-step">
                            <div class="rni-step-n">ÉTAPE 2</div>
                            <p>Criblage phytochimique &amp; validation des composés actifs</p>
                        </div>
                        <div class="rni-step">
                            <div class="rni-step-n">ÉTAPE 3</div>
                            <p>Développement d'extraits standardisés</p>
                        </div>
                        <div class="rni-step">
                            <div class="rni-step-n">ÉTAPE 4</div>
                            <p>Études précliniques de sécurité &amp; d'efficacité</p>
                        </div>
                        <div class="rni-step">
                            <div class="rni-step-n">ÉTAPE 5</div>
                            <p>Production pilote BPF</p>
                        </div>
                        <div class="rni-step">
                            <div class="rni-step-n">ÉTAPE 6</div>
                            <p>Préparation des dossiers réglementaires</p>
                        </div>
                        <div class="rni-step">
                            <div class="rni-step-n">ÉTAPE 7</div>
                            <p>Mise à l'échelle commerciale &amp; déploiement sur le marché</p>
                        </div>
                    </div>
                    <p class="rni-note">
                        Ce pipeline réduit l'incertitude technique, accélère le délai de mise sur le marché et protège
                        le capital des investisseurs grâce à une atténuation des risques par phases.
                    </p>
                </div>

                <!-- G -->
                <div class="rni-card">
                    <div class="rni-badge">G</div>
                    <h3>Innovation avec des résultats mesurables (Objectifs 2031)</h3>
                    <p class="rni-txt">D'ici 2031, NUFOTEC-PHYTOMED INDUSTRIES aura :</p>
                    <ul class="rni-list">
                        <li>Développé plusieurs gammes de produits phytopharmaceutiques standardisés</li>
                        <li>Établi un laboratoire analytique de recherche entièrement opérationnel</li>
                        <li>Déposé des formulations propriétaires et des actifs de propriété intellectuelle</li>
                        <li>Obtenu des certifications de qualité internationale</li>
                        <li>Généré des données cliniques validées soutenant la commercialisation mondiale</li>
                        <li>Positionnée comme un pôle régional d'innovation en phytomédecine fondée sur des preuves</li>
                    </ul>
                </div>

                <!-- H -->
                <div class="rni-card">
                    <div class="rni-badge">H</div>
                    <h3>Innovation scientifique axée sur les critères ESG</h3>
                    <p class="rni-txt">Notre plateforme de recherche repose sur :</p>
                    <ul class="rni-list">
                        <li>Un approvisionnement durable en matières premières</li>
                        <li>Une culture biologique climato-intelligente</li>
                        <li>Des bioprocédés zéro déchet</li>
                        <li>Des chaînes de valeur inclusives pour les communautés</li>
                        <li>Un développement de la main-d'œuvre scientifique inclusive en matière de genre</li>
                    </ul>
                    <p class="rni-note">
                        L'excellence scientifique et la durabilité ne sont pas des stratégies parallèles – ce sont des
                        moteurs intégrés de création de valeur à long terme.
                    </p>
                </div>

                <!-- I -->
                <div class="rni-card rni-card--wide">
                    <div class="rni-badge">I</div>
                    <h3>Durabilité et création de valeur à long terme</h3>
                    <p class="rni-txt">
                        NUFOTEC intègre l'agriculture climato-intelligente, la régénération des sols, l'utilisation
                        responsable des ressources et la participation économique inclusive dans ses activités
                        principales. L'expansion à plus de 2 000 hectares intégrera des milliers de petits exploitants
                        agricoles, augmentera les revenus ruraux et générera des emplois industriels qualifiés tout en
                        contribuant à l'autonomie pharmaceutique régionale et à la croissance industrielle durable.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
