<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== INVESTOR & PARTNER COMMITMENT — STATIC VIEW ===================== -->
<style>
    .ipc-wrap{background:#fff}
    .ipc-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .ipc-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .ipc-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .ipc-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .ipc-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .ipc-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .ipc-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.6rem,3.2vw,2.4rem);line-height:1.3;margin:0 0 10px}
    .ipc-hero h1 .ipc-brace{color:#D4A017}
    .ipc-hero .ipc-tagline{position:relative;z-index:2;display:inline-flex;align-items:center;gap:10px;background:rgba(212,160,23,.14);border:1px solid rgba(212,160,23,.4);color:#F3D98B;font-size:.82rem;font-weight:600;letter-spacing:.4px;padding:7px 16px;border-radius:50px;margin:0 0 16px}
    .ipc-hero .ipc-tagline i{color:#D4A017}
    .ipc-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    .ipc-body{padding:48px 0 70px}
    .ipc-card{display:flex;gap:22px;background:#fff;border:1px solid #E3EDE6;border-radius:14px;padding:28px 30px;margin:0 0 22px;box-shadow:0 6px 18px rgba(8,61,42,.05)}
    .ipc-card .ipc-ico{flex:0 0 auto;width:54px;height:54px;display:flex;align-items:center;justify-content:center;background:#083D2A;color:#D4A017;font-size:1.25rem;border-radius:14px;box-shadow:0 6px 16px rgba(8,61,42,.22)}
    .ipc-card .ipc-content{flex:1;min-width:0}
    .ipc-card h2{margin:2px 0 12px;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.1rem,1.7vw,1.4rem);color:#083D2A}
    .ipc-card p{color:#3d4f45;font-size:.93rem;line-height:1.8;margin:0 0 10px}
    .ipc-list{margin:0;padding:0;list-style:none}
    .ipc-list li{position:relative;padding:7px 0 7px 28px;color:#3d4f45;font-size:.9rem;line-height:1.7}
    .ipc-list li::before{content:"\f058";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;left:2px;top:9px;font-size:.8rem;color:#D4A017}
    .ipc-list--dot li::before{content:"\f111";font-size:.5rem;top:15px;color:#0B5D3B}
    .ipc-foot{background:linear-gradient(135deg,#083D2A,#0B5D3B);border-radius:14px;padding:28px 32px;color:#fff;position:relative;overflow:hidden;margin-top:8px}
    .ipc-foot::before{content:"";position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:rgba(212,160,23,.15);filter:blur(40px)}
    .ipc-foot h3{position:relative;z-index:2;margin:0 0 14px;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.15rem;color:#F3D98B}
    .ipc-foot ul{position:relative;z-index:2;margin:0;padding:0;list-style:none}
    .ipc-foot ul li{position:relative;padding:7px 0 7px 28px;color:rgba(255,255,255,.92);font-size:.9rem;line-height:1.7}
    .ipc-foot ul li::before{content:"\f058";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;left:2px;top:9px;font-size:.8rem;color:#D4A017}
    @media(max-width:768px){.ipc-card{flex-direction:column;gap:14px}}
</style>

<section class="ipc-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="ipc-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Notre engagement envers les investisseurs partenaires</span>
            </div>
            <div class="ipc-tagline"><i class="fa-solid fa-handshake"></i> Bâtir la confiance par la transparence, la responsabilité et la croissance stratégique</div>
            <h1>Notre engagement envers les investisseurs <span class="ipc-brace">{</span> partenaires</h1>
            <div class="ipc-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="ipc-body">
        <div class="container">

            <!-- Introduction -->
            <div class="ipc-card">
                <div class="ipc-ico"><i class="fa-solid fa-shield-heart"></i></div>
                <div class="ipc-content">
                    <p>
                        Chez NUFOTEC, nous nous engageons à maximiser l'impact tout en garantissant l'intégrité
                        financière. Notre approche combine :
                    </p>
                    <ul class="ipc-list">
                        <li><strong>Politique de non-détournement des fonds :</strong> clause contractuelle juridiquement contraignante sur l'utilisation restreinte du produit</li>
                        <li><strong>Transparence :</strong> rapports clairs sur les fonds, les dépenses et les résultats des projets</li>
                        <li><strong>Responsabilité :</strong> structures de gouvernance et processus prêts pour l'audit</li>
                    </ul>
                </div>
            </div>

            <!-- Gouvernance financière et supervision -->
            <div class="ipc-card">
                <div class="ipc-ico"><i class="fa-solid fa-scale-balanced"></i></div>
                <div class="ipc-content">
                    <h2>Gouvernance financière et supervision</h2>
                    <p>NUFOTEC adhère aux meilleures pratiques internationales en matière de gestion financière, y compris :</p>
                    <ul class="ipc-list">
                        <li>Audits et examens financiers indépendants</li>
                        <li>Contrôles internes robustes pour protéger les actifs</li>
                        <li>Supervision au niveau du conseil d'administration de toutes les décisions financières</li>
                        <li>Conformité aux réglementations et normes de reporting locales et internationales</li>
                    </ul>
                    <p>
                        Les investisseurs et donateurs peuvent avoir l'assurance que les fonds sont gérés de manière
                        efficace, éthique et en conformité totale.
                    </p>
                </div>
            </div>

            <!-- Opportunités d'investissement -->
            <div class="ipc-card">
                <div class="ipc-ico"><i class="fa-solid fa-chart-pie"></i></div>
                <div class="ipc-content">
                    <h2>Opportunités d'investissement</h2>
                    <p>
                        NUFOTEC offre des opportunités d'investissement structurées stratégiquement conçues pour générer
                        des rendements mesurables tout en soutenant les initiatives en matière de santé, de nutrition,
                        de développement économique durable et d'agriculture durable :
                    </p>
                    <ul class="ipc-list">
                        <li>Financement d'amorçage et de croissance pour NUFOTEC-PHYTOMED INDUSTRIES et les plateformes numériques</li>
                        <li>Opportunités de co-investissement avec des partenaires régionaux et internationaux</li>
                        <li>Projets alignés sur les critères ESG axés sur la nutrition préventive, les aliments fonctionnels et l'agriculture biologique de plantes médicinales cibles, de cultures fonctionnelles riches en nutriments, de fruits et légumes servant de matières premières pour notre installation de fabrication</li>
                        <li>Les investissements sont liés à des KPI clairs et à des métriques d'expansion du marché, garantissant un impact et une visibilité mesurables</li>
                    </ul>
                </div>
            </div>

            <!-- Engagement des donateurs et impact -->
            <div class="ipc-card">
                <div class="ipc-ico"><i class="fa-solid fa-hand-holding-heart"></i></div>
                <div class="ipc-content">
                    <h2>Engagement des donateurs et impact</h2>
                    <p>Les donateurs sont un élément essentiel de la mission d'NUFOTEC. Nous fournissons :</p>
                    <ul class="ipc-list">
                        <li>Rapports d'impact réguliers mettant en évidence les résultats sociaux, environnementaux et sanitaires</li>
                        <li>Suivi transparent des fonds alloués aux programmes communautaires, à la recherche et à la sensibilisation numérique</li>
                        <li>Reconnaissance des donateurs conformément à leurs préférences et à l'ampleur de leur contribution</li>
                    </ul>
                    <p>
                        Les donateurs peuvent voir comment chaque contribution se traduit par un impact réel sur la
                        santé, l'économie, la création d'emplois sensibles au genre et l'impact social.
                    </p>
                </div>
            </div>

            <!-- Rapports financiers et transparence -->
            <div class="ipc-card">
                <div class="ipc-ico"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div class="ipc-content">
                    <h2>Rapports financiers et transparence</h2>
                    <p>NUFOTEC priorise la visibilité financière en temps réel :</p>
                    <ul class="ipc-list">
                        <li>États financiers trimestriels et annuels</li>
                        <li>Tableaux de bord en ligne avec l'allocation des fonds par projet et par programme</li>
                        <li>Suivi des indicateurs clés de performance (KPI) liés à la croissance du marché, à l'engagement communautaire et aux performances ESG</li>
                    </ul>
                    <p>Cela garantit que les investisseurs et les donateurs peuvent suivre l'impact de leur soutien à tout moment.</p>
                </div>
            </div>

            <!-- Pourquoi s'associer à NUFOTEC ? -->
            <div class="ipc-foot">
                <h3>Pourquoi s'associer à NUFOTEC ?</h3>
                <ul>
                    <li>Parcours prouvé : 235+ groupes WhatsApp, 211 500+ participants, en croissance quotidienne</li>
                    <li>Marketing numérique et engagement communautaire pilotés par l'IA pour une portée mesurable</li>
                    <li>Gouvernance robuste, conformité et processus prêts pour l'audit</li>
                    <li>Structure d'investissement claire avec des projets à fort impact et alignés sur les critères ESG</li>
                    <li>Équipe dédiée aux relations avec les investisseurs, donateurs et courtiers fournissant un soutien et des rapports personnalisés</li>
                </ul>
            </div>

        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
