<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== PHASED INVESTMENT PROJECTION — STATIC VIEW ===================== -->
<style>
    .pip-wrap{background:#fff}
    .pip-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .pip-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .pip-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .pip-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .pip-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .pip-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .pip-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.4vw,2.5rem);line-height:1.25;margin:0 0 10px}
    .pip-hero h1 .pip-brace{color:#D4A017}
    .pip-hero .pip-tagline{position:relative;z-index:2;display:inline-flex;align-items:center;gap:10px;background:rgba(212,160,23,.14);border:1px solid rgba(212,160,23,.4);color:#F3D98B;font-size:.82rem;font-weight:600;letter-spacing:.4px;padding:7px 16px;border-radius:50px;margin:0 0 16px}
    .pip-hero .pip-tagline i{color:#D4A017}
    .pip-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    .pip-body{padding:48px 0 70px}
    .pip-intro{background:#F5F9F6;border:1px solid #E3EDE6;border-radius:12px;padding:26px 30px;margin:0 0 34px}
    .pip-intro p{margin:0 0 16px;font-size:.96rem;line-height:1.85;color:#2c3e35}
    .pip-intro p:last-child{margin-bottom:0}
    .pip-statbar{display:flex;flex-wrap:wrap;align-items:center;gap:22px;margin-bottom:18px}
    .pip-stat{background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;border-radius:14px;padding:18px 26px;min-width:250px}
    .pip-stat .pip-val{font-family:'Poppins',sans-serif;font-weight:700;font-size:1.7rem;color:#F3D98B;line-height:1.2}
    .pip-stat .pip-lbl{font-size:.8rem;letter-spacing:.6px;color:rgba(255,255,255,.8);text-transform:uppercase;margin-top:4px}
    .pip-note{background:#FBF7EA;border:1px solid #EADFAE;border-left:5px solid #D4A017;border-radius:10px;padding:14px 18px;font-size:.9rem;line-height:1.7;color:#6b5d2e}
    .pip-note strong{color:#083D2A}
    .pip-block{display:flex;gap:22px;margin:0 0 40px}
    .pip-block .pip-num{flex:0 0 auto;width:52px;height:52px;display:flex;align-items:center;justify-content:center;background:#083D2A;color:#D4A017;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.15rem;border-radius:14px;box-shadow:0 6px 16px rgba(8,61,42,.22)}
    .pip-block .pip-content{flex:1;min-width:0}
    .pip-block h2{margin:4px 0 14px;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.15rem,1.8vw,1.45rem);color:#083D2A}
    .pip-block p{color:#3d4f45;font-size:.93rem;line-height:1.8;margin:0 0 12px}
    .pip-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:0 0 14px}
    .pip-item{display:flex;gap:10px;align-items:flex-start;background:#fff;border:1px solid #E3EDE6;border-radius:10px;padding:12px 14px;font-size:.87rem;line-height:1.6;color:#3d4f45;box-shadow:0 3px 10px rgba(8,61,42,.04)}
    .pip-item i{color:#D4A017;margin-top:3px;font-size:.8rem}
    .pip-equip{background:#fff;border:1px solid #E3EDE6;border-left:5px solid #0B5D3B;border-radius:10px;padding:16px 18px;margin:0 0 12px;font-size:.9rem;line-height:1.75;color:#3d4f45}
    .pip-lab{margin:0;padding:0;list-style:none;counter-reset:lab}
    .pip-lab li{position:relative;padding:9px 0 9px 42px;color:#3d4f45;font-size:.9rem;line-height:1.65;border-bottom:1px dashed #EDF3EF}
    .pip-lab li:last-child{border-bottom:none}
    .pip-lab li::before{counter-increment:lab;content:counter(lab);position:absolute;left:0;top:7px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;background:#FBF7EA;border:1px solid #EADFAE;color:#9a7a10;font-size:.75rem;font-weight:700;border-radius:8px}
    .pip-chips{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin:0 0 20px}
    .pip-chip{display:flex;gap:10px;align-items:flex-start;background:#F5F9F6;border:1px solid #E3EDE6;border-radius:10px;padding:11px 14px;font-size:.85rem;line-height:1.55;color:#3d4f45}
    .pip-chip i{color:#0B5D3B;margin-top:3px;font-size:.72rem}
    .pip-table{width:100%;border-collapse:collapse;margin:6px 0 14px;font-size:.85rem;border:1px solid #E3EDE6;border-radius:10px;overflow:hidden}
    .pip-table thead th{background:#083D2A;color:#fff;text-align:left;font-size:.72rem;letter-spacing:.8px;text-transform:uppercase;padding:11px 14px}
    .pip-table thead th:last-child{background:#0B5D3B}
    .pip-table tbody td{padding:9px 14px;border-top:1px solid #EDF3EF;color:#33403A;vertical-align:top}
    .pip-table tbody td:last-child{font-weight:600;color:#0B5D3B;white-space:nowrap}
    .pip-table tbody tr:nth-child(even){background:#FAFCFB}
    .pip-duo{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:0 0 40px}
    .pip-duo .pip-card{background:#F5F9F6;border:1px solid #E3EDE6;border-radius:12px;padding:22px 24px}
    .pip-duo .pip-card h3{margin:0 0 12px;font-family:'Poppins',sans-serif;font-weight:700;font-size:1rem;color:#083D2A}
    .pip-list{margin:0;padding:0;list-style:none}
    .pip-list li{position:relative;padding:7px 0 7px 28px;color:#3d4f45;font-size:.88rem;line-height:1.65}
    .pip-list li::before{content:"\f058";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;left:2px;top:9px;font-size:.8rem;color:#D4A017}
    .pip-list--dot li::before{content:"\f111";font-size:.5rem;top:15px;color:#0B5D3B}
    .pip-quote{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);border-radius:14px;padding:30px 34px;color:#fff;margin:0 0 40px;overflow:hidden}
    .pip-quote::before{content:"\201C";position:absolute;top:-26px;left:18px;font-size:7rem;font-family:Georgia,serif;color:rgba(212,160,23,.25)}
    .pip-quote p{margin:0;position:relative;z-index:2;font-family:'Poppins',sans-serif;font-size:clamp(1rem,1.6vw,1.25rem);font-weight:600;line-height:1.6}
    .pip-quote p span{color:#F3D98B}
    @media(max-width:768px){.pip-grid,.pip-chips,.pip-duo{grid-template-columns:1fr}.pip-block{flex-direction:column;gap:12px}}
</style>

<section class="pip-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="pip-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Projection d'investissement par phase</span>
            </div>
            <div class="pip-tagline"><i class="fa-solid fa-sack-dollar"></i> Capital d'amorçage de plus de 40 millions USD (2026–2029)</div>
            <h1>Projection d'investissement <span class="pip-brace">{</span> par phase</h1>
            <div class="pip-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="pip-body">
        <div class="container">

            <!-- Intro -->
            <div class="pip-intro">
                <div class="pip-statbar">
                    <div class="pip-stat">
                        <div class="pip-val">40M+ USD</div>
                        <div class="pip-lbl">Capital d'amorçage</div>
                    </div>
                </div>
                <p>
                    African Green Farmers Limited (NUFOTEC) – L'INSTALLATION NUFOTEC-PHYTOMED INDUSTRIES – cherche à
                    mobiliser plus de 40 millions USD de capital d'amorçage par phases dédié à la construction, la mise
                    en service et l'opérationnalisation d'une installation de transformation de phytomédicaments et de
                    nutraceutiques conforme aux BPF.
                </p>
                <div class="pip-note">
                    <strong>Pendant les 5 premières années d'exploitation,</strong> NUFOTEC n'allouera pas de capital
                    d'amorçage aux activités agricoles commerciales. Tous les intrants botaniques bruts, de spiruline et
                    d'engrais organiques seront obtenus par le biais d'accords contractuels d'approvisionnement externes
                    structurés.
                </div>
                <p style="margin-top:14px">
                    Ce modèle réduit considérablement les risques agricoles en phase de démarrage et accélère la
                    génération de revenus industriels.
                </p>
            </div>

            <!-- A. Construction d'installations -->
            <div class="pip-block">
                <div class="pip-num">A</div>
                <div class="pip-content">
                    <h2>Investir dans la construction d'installations et les infrastructures industrielles</h2>
                    <div class="pip-grid">
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Construction de l'installation de transformation NUFOTEC-PHYTOMED INDUSTRIES</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Salles blanches pré-construites conformes aux BPF pour les zones de traitement critiques</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Systèmes CVC avec gradients de pression contrôlés</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Systèmes de vapeur propre et d'eau purifiée</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Systèmes de gestion des déchets et de récupération des solvants</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Systèmes d'alimentation hybrides (solaire + groupe électrogène de secours)</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Systèmes d'extinction d'incendie et de conformité en matière de sécurité</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Unités résidentielles pour le personnel</div>
                    </div>
                </div>
            </div>

            <!-- B. Équipements de transformation -->
            <div class="pip-block">
                <div class="pip-num">B</div>
                <div class="pip-content">
                    <h2>Investir dans les équipements de transformation et de production</h2>
                    <div class="pip-equip">
                        Un système de ligne clé en main entièrement automatisé et intégré d'<strong>Extraction Assistée
                        par Ultrasons (UAE)</strong> intégrant (dans un seul système de ligne) essentiellement le
                        traitement post-récolte des matières premières végétales, l'extraction par solvant validé, la
                        concentration, la purification chromatographique, le séchage sous vide, la micronisation et le
                        tamisage vibratoire par ultrasons, l'incorporation aseptique d'agent anti-mottant, la production
                        de gélules et de comprimés, le remplissage des flacons, le capsulage et le scellage, la mise en
                        boîte, l'étiquetage des instructions et l'insertion de la notice, l'optimisation par traitement
                        à haute pression (HPP) post-conditionnement (stérilisation non thermique) et le conditionnement
                        en carton.
                    </div>
                    <p>
                        Il s'agit d'un système d'efficacité de fabrication conforme aux BPF au sein d'un seul système de
                        ligne continue de bout en bout, d'environ <strong>60 m × 5 m</strong>. Ce système de ligne
                        fabrique tous les extraits cibles de toutes les plantes médicinales selon nos besoins. C'est le
                        <strong>seul système de ligne de traitement</strong> qui sera obtenu en utilisant le capital
                        d'amorçage.
                    </p>
                    <p>
                        Le reste des systèmes de ligne de fabrication tels que le Système de Ligne de Fabrication
                        Intégré pour les Produits à Base de Soja (Lait de Soja Liquide Fortifié, Poudre de Lait de Soja
                        et Tofu Séché sous Vide), le Système de Ligne de Traitement Clé en Main Intégré pour les
                        Compléments Alimentaires à Base de Spiruline, le Système de Ligne de Traitement Intégré Clé en
                        Main pour les Jus de Fruits, et le Système de Ligne de Traitement Intégré Clé en Main pour les
                        Engrais Organiques seront obtenus et utilisés grâce aux activités génératrices de revenus de
                        l'installation, principalement les ventes.
                    </p>
                </div>
            </div>

            <!-- C. Laboratoires -->
            <div class="pip-block">
                <div class="pip-num">C</div>
                <div class="pip-content">
                    <h2>Investir dans les infrastructures de laboratoire et de contrôle qualité</h2>
                    <p>
                        Équipement de laboratoire analytique et de laboratoire de recherche scientifique, y compris
                        <strong>UHPLC-MS/MS, GC-MS/MS 8000, HPAAS, Microscope à Balayage Laser AH SN600, RT-qPCR et
                        Cytomètre en Flux Numérique à Hauts Paramètres</strong> pour :
                    </p>
                    <ol class="pip-lab">
                        <li><strong>Laboratoire de microbiologie</strong> pour les tests de micro-organismes et de contamination biologique des matières premières et des produits finis</li>
                        <li><strong>Laboratoire analytique (Labo CQ)</strong> qui effectue des tests pour le profilage et la standardisation des produits</li>
                        <li><strong>Laboratoire préclinique</strong> effectuant la toxicologie, la sécurité, et la pharmacocinétique des produits</li>
                        <li><strong>Laboratoire BPL (Bonnes Pratiques de Laboratoire)</strong> qui génère des données non cliniques avant les essais sur l'homme</li>
                        <li><strong>Laboratoire de recherche clinique</strong> qui analyse les échantillons des études cliniques</li>
                    </ol>
                </div>
            </div>

            <!-- D. Autorisations réglementaires -->
            <div class="pip-block">
                <div class="pip-num">D</div>
                <div class="pip-content">
                    <h2>Investir dans les autorisations réglementaires, les licences et la certification</h2>
                    <p>Les autorisations réglementaires et les licences pour lesquelles le capital d'amorçage sera utilisé comprennent :</p>
                    <div class="pip-chips">
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Demande d'exploitation et enregistrement – PACRA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Licence d'investissement – ZDA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Certificat d'Étude d'Impact Environnemental (EIE) – ZEMA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Certificat de Rapport EPB de Sélection – ZEMA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Licence de Gestion des Déchets – ZEMA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Certificat d'Approbation de Site – ZMRA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Enregistrement de Produit – ZMRA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Inspection BPF – ZAMRA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Licence de Fabrication – ZAMRA</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Permis d'Investisseur – IMMIGRATION</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Test de Produit et Demande – ZABS</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Inspection d'Usine – ZABS</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Marque de Certification – ZABS</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Certificat de Salubrité des Locaux – Département de Santé Local (Municipal/District)</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Certificat d'Inspection/Conformité à la Sécurité Sanitaire des Aliments – Département de Santé Local</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Certificat de Manipulation/Sécurité Alimentaire – Département de Santé Local</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Certificat d'Évaluation des Risques pour la Santé au Travail – Département de Santé Local</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Vérification annuelle de la conformité sanitaire – Département de Santé Local</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>Titres fonciers – Chefs de village, Chef, District de Chiebombo, Province et Ministère des Terres</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 9001 – Système de Management de la Qualité – Certification initiale</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 9001 – Système de Management de la Qualité – Surveillance annuelle</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 22000 – Sécurité des aliments</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 14001 – Gouvernance environnementale et durabilité – Certification initiale</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 14001 – Gouvernance environnementale et durabilité – Surveillance annuelle</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 45001 – Santé et sécurité au travail – Certification initiale</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 45001 – Santé et sécurité au travail – Audits de surveillance annuels</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 14644 – Qualification des salles blanches ISO 5 / Salle × 10</div>
                        <div class="pip-chip"><i class="fa-solid fa-circle-check"></i>ISO 14644 – Audits annuels de recertification des salles blanches ISO 5 / Salle × 10</div>
                    </div>
                    <table class="pip-table">
                        <thead>
                            <tr><th>Certification / Licence</th><th>Autorité</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Demande d'exploitation et enregistrement</td><td>PACRA</td></tr>
                            <tr><td>Licence d'investissement</td><td>ZDA</td></tr>
                            <tr><td>Étude d'Impact Environnemental</td><td>ZEMA</td></tr>
                            <tr><td>Rapport EPB de sélection</td><td>ZEMA</td></tr>
                            <tr><td>Licence de gestion des déchets</td><td>ZEMA</td></tr>
                            <tr><td>Certificat d'approbation de site</td><td>ZMRA</td></tr>
                            <tr><td>Enregistrement de produit</td><td>ZMRA</td></tr>
                            <tr><td>Inspection BPF</td><td>ZAMRA</td></tr>
                            <tr><td>Licence de fabrication</td><td>ZAMRA</td></tr>
                            <tr><td>Permis d'investisseur</td><td>IMMIGRATION</td></tr>
                            <tr><td>Test et demande de produit</td><td>ZABS</td></tr>
                            <tr><td>Inspection d'usine</td><td>ZABS</td></tr>
                            <tr><td>Marque de certification</td><td>ZABS</td></tr>
                            <tr><td>Certificat d'hygiène des locaux</td><td>Dépt Santé Local</td></tr>
                            <tr><td>Inspection de sécurité sanitaire des aliments</td><td>Dépt Santé Local</td></tr>
                            <tr><td>Certificat de manipulation des aliments</td><td>Dépt Santé Local</td></tr>
                            <tr><td>Évaluation des risques professionnels</td><td>Dépt Santé Local</td></tr>
                            <tr><td>Vérification annuelle de la conformité sanitaire</td><td>Dépt Santé Local</td></tr>
                            <tr><td>Titres fonciers</td><td>Ministère des Terres</td></tr>
                            <tr><td>ISO 9001 Gestion de la qualité</td><td>Initiale + Annuelle</td></tr>
                            <tr><td>ISO 22000 Sécurité des aliments</td><td>Certification</td></tr>
                            <tr><td>ISO 14001 Environnement</td><td>Initiale + Annuelle</td></tr>
                            <tr><td>ISO 45001 Santé et sécurité</td><td>Initiale + Annuelle</td></tr>
                            <tr><td>ISO 14644 Salle blanche</td><td>ISO 5 (10 salles)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- E. Opérationnalisation -->
            <div class="pip-block">
                <div class="pip-num">E</div>
                <div class="pip-content">
                    <h2>Investir dans l'opérationnalisation et le déploiement sur le marché</h2>
                    <div class="pip-grid">
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Salaires pour le recrutement et la formation du personnel qualifié</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Salaires de la direction générale</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Loyer du siège social, des boutiques de santé nationales et des points de vente régionaux</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Acquisition d'une flotte de distribution</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Véhicule de coordination de la direction</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Systèmes ERP et de traçabilité numérique</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Image de marque, conception d'emballage et lancement sur le marché</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Assurance (installations, responsabilité civile, indemnisation professionnelle)</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Systèmes de sécurité et contrôle d'accès aux installations</div>
                    </div>
                </div>
            </div>

            <!-- Première phase d'exploitation + stratégie -->
            <div class="pip-duo">
                <div class="pip-card">
                    <h3>Pendant la première phase d'exploitation</h3>
                    <ul class="pip-list">
                        <li>Matières premières végétales médicinales approvisionnées par le biais d'accords contractuels avec des fournisseurs</li>
                        <li>Biomasse de spiruline approvisionnée par le biais de partenariats de production externes structurés</li>
                        <li>Diversification multi-fournisseurs pour réduire le risque de dépendance</li>
                        <li>Spécifications de qualité imposées par validation en laboratoire avant approvisionnement</li>
                    </ul>
                </div>
                <div class="pip-card">
                    <h3>Cette stratégie garantit</h3>
                    <ul class="pip-list">
                        <li>Une exposition moindre du capital en phase de démarrage</li>
                        <li>Un risque réduit lié au climat et à la production agricole</li>
                        <li>Un délai de commercialisation plus rapide</li>
                        <li>Une concentration immédiate sur la transformation à valeur ajoutée</li>
                    </ul>
                </div>
            </div>

            <!-- Modèle de génération de revenus -->
            <div class="pip-block">
                <div class="pip-num">3</div>
                <div class="pip-content">
                    <h2>Modèle de génération de revenus (Années 1–5)</h2>
                    <p>L'expansion de l'agriculture commerciale et l'acquisition d'équipements supplémentaires seront financées par les revenus générés en interne par :</p>
                    <div class="pip-grid">
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Extraits botaniques standardisés (MTCAs, Nutraceutiques/Compléments Alimentaires, Phytomédicaments et Phytopharmaceutiques – Comprimés, gélules, poudres micronisées, concentrés liquides)</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Engrais organiques haute-nutrition (poudres micronisées et concentrés liquides)</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Services de fabrication contractuelle (transformation pour des tiers)</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Production sous marque de distributeur</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Exportation d'ingrédients phytochimiques semi-transformés</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Services d'analyse en laboratoire</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Partenariats de licence et de formulation</div>
                    </div>
                    <p><strong style="color:#083D2A">Le réinvestissement des revenus financera principalement :</strong></p>
                    <div class="pip-grid">
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>Des clusters de culture biologique commerciale de plantes médicinales cibles, de cultures fonctionnelles, de fruits...</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>L'acquisition de lignes de transformation supplémentaires à haute capacité</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>L'intégration verticale des cultures médicinales prioritaires</div>
                        <div class="pip-item"><i class="fa-solid fa-circle-check"></i>La mise à l'échelle régionale des infrastructures de distribution</div>
                    </div>
                </div>
            </div>

            <!-- Justification stratégique -->
            <div class="pip-block">
                <div class="pip-num"><i class="fa-solid fa-scale-balanced"></i></div>
                <div class="pip-content">
                    <h2>Justification stratégique</h2>
                    <ul class="pip-list">
                        <li>Réduit l'exposition au CAPEX agricole</li>
                        <li>Convertit le capital d'amorçage en actifs générateurs de revenus</li>
                        <li>Accélère la stabilisation de l'EBITDA</li>
                        <li>Réduit les risques pour le capital des investisseurs</li>
                        <li>Renforce la bancabilité</li>
                        <li>Préserve le potentiel d'intégration verticale</li>
                    </ul>
                </div>
            </div>

            <!-- Citation -->
            <div class="pip-quote">
                <p>« Les investisseurs financent d'abord la transformation — <span>pas le risque agricole brut.</span> »</p>
            </div>

            <!-- Confiance des investisseurs -->
            <div class="pip-block">
                <div class="pip-num"><i class="fa-solid fa-handshake"></i></div>
                <div class="pip-content">
                    <h2>Confiance des investisseurs</h2>
                    <ul class="pip-list">
                        <li>Priorise la génération de flux de trésorerie industriels</li>
                        <li>Minimise l'exposition climatique en phase de démarrage</li>
                        <li>Maintient une utilisation disciplinée du capital</li>
                        <li>Utilise les bénéfices non distribués pour se développer</li>
                        <li>S'aligne sur les attentes de risque des institutions de financement du développement (IFD)</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
