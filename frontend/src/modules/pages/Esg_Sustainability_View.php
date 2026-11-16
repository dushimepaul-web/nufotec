<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================================
   PAGE : ESG ET DURABILITÉ (statique)
   NUFOTEC-PHYTOMED INDUSTRIES
   ============================================================ */
.esg-page {
    font-family: 'Poppins', sans-serif;
    background: #fff;
    color: #33403A;
    overflow-x: hidden;
}
.esg-page * { font-family: 'Poppins', sans-serif; }

/* En-tête de page */
.esg-page-head {
    position: relative;
    background: linear-gradient(135deg, #083D2A, #0B5D3B);
    padding: 84px 0 64px;
    text-align: center;
    overflow: hidden;
}
.esg-page-head::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -60px;
    width: 340px; height: 340px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212,160,23,.18) 0%, transparent 70%);
}
.esg-page-head .esg-crumb {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.85); font-size: .8rem; font-weight: 500;
    padding: 7px 16px; border-radius: 50px; margin-bottom: 22px;
}
.esg-page-head .esg-crumb a { color: rgba(255,255,255,.85); text-decoration: none; }
.esg-page-head .esg-crumb a:hover { color: #D4A017; }
.esg-page-title {
    color: #fff;
    font-size: clamp(1.8rem, 4vw, 2.9rem);
    font-weight: 800;
    line-height: 1.25;
    margin: 0;
    position: relative;
    z-index: 1;
}
.esg-page-title span { color: #D4A017; }

/* Contenu */
.esg-content { max-width: 1180px; margin: 0 auto; padding: 72px 24px 40px; }
.esg-content p { font-size: 1.03rem; line-height: 1.95; color: #33403A; margin-bottom: 22px; text-align: justify; }
.esg-content p strong { color: #0B5D3B; }

/* Blocs numérotés */
.esg-block {
    background: #fff;
    border: 1px solid #E3EBE6;
    border-radius: 24px;
    box-shadow: 0 12px 32px rgba(8,61,42,.06);
    padding: 44px 44px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.esg-block-num {
    position: absolute; top: 26px; right: 34px;
    font-size: 4.2rem; font-weight: 800; line-height: 1;
    color: #EAF6EF; z-index: 0;
    font-family: 'Poppins', sans-serif;
}
.esg-block > * { position: relative; z-index: 1; }
.esg-block-title { font-size: 1.45rem; font-weight: 800; color: #083D2A; margin-bottom: 18px; }

/* Listes */
.esg-list { list-style: none; margin: 20px 0 0; padding: 0; }
.esg-list li {
    position: relative;
    background: #F7FAF8;
    border: 1px solid #E3EBE6;
    border-radius: 14px;
    padding: 15px 20px 15px 52px;
    margin-bottom: 12px;
    font-size: .98rem;
    line-height: 1.7;
    color: #33403A;
    transition: all .25s ease;
}
.esg-list li:hover { border-color: #BBD8C6; background: #fff; box-shadow: 0 8px 20px rgba(11,93,59,.07); }
.esg-list li::before {
    content: '\F26E';
    font-family: 'bootstrap-icons';
    position: absolute; left: 20px; top: 17px;
    width: 24px; height: 24px;
    border-radius: 8px;
    background: #EAF6EF; color: #0B5D3B;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
}
.esg-list li strong { color: #083D2A; }

/* CTA callout */
.esg-callout {
    display: flex; align-items: flex-start; gap: 16px;
    background: #EAF6EF; border-left: 5px solid #D4A017;
    border-radius: 16px; padding: 22px 28px; margin-top: 26px;
}
.esg-callout i { color: #D4A017; font-size: 1.6rem; margin-top: 3px; }
.esg-callout p { margin: 0; font-weight: 600; color: #083D2A; line-height: 1.7; }

/* Chips ISO */
.esg-chips { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 22px; }
.esg-chip {
    display: inline-flex; align-items: center; gap: 8px;
    background: #fff; border: 1px solid #DCE8E0; color: #083D2A;
    font-size: .9rem; font-weight: 600; padding: 10px 20px; border-radius: 50px;
    transition: all .3s ease;
}
.esg-chip i { color: #D4A017; }
.esg-chip:hover { background: #EAF6EF; border-color: #0B5D3B; transform: translateY(-2px); }

/* Licences */
.esg-licenses {
    list-style: none;
    margin: 26px 0 0;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.esg-licenses li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #F7FAF8;
    border: 1px solid #E3EBE6;
    border-radius: 14px;
    padding: 14px 18px;
    font-size: .94rem;
    line-height: 1.55;
    color: #33403A;
    transition: all .25s ease;
}
.esg-licenses li:hover { border-color: #BBD8C6; background: #fff; box-shadow: 0 8px 20px rgba(11,93,59,.08); }
.esg-licenses li i { color: #0B5D3B; font-size: 1.05rem; margin-top: 2px; }
.esg-licenses li strong { color: #083D2A; }

/* Panneau final positionnement */
.esg-position {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #083D2A, #0B5D3B);
    border-radius: 24px;
    padding: 52px 48px;
    margin-bottom: 30px;
}
.esg-position::before {
    content: '\F56F';
    font-family: 'bootstrap-icons';
    position: absolute; right: 26px; bottom: -30px; font-size: 150px;
    color: rgba(255,255,255,.06);
}
.esg-position .esg-block-title { color: #fff; }
.esg-position p { color: rgba(255,255,255,.9); }
.esg-position p strong { color: #D4A017; }
.esg-position .esg-tag {
    display: inline-flex; align-items: center; gap: 10px;
    background: #D4A017; color: #083D2A;
    font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 8px 22px; border-radius: 50px; margin-bottom: 20px;
}

@media (max-width: 767.98px) {
    .esg-page-head { padding: 64px 0 48px; }
    .esg-content { padding: 52px 18px 24px; }
    .esg-block { padding: 34px 24px; }
    .esg-licenses { grid-template-columns: 1fr; }
    .esg-position { padding: 38px 26px; }
    .esg-block-num { font-size: 3.2rem; top: 20px; right: 22px; }
}
</style>

<main class="esg-page">

    <!-- En-tête de page -->
    <div class="esg-page-head">
        <div class="esg-crumb">
            <i class="bi bi-house-door"></i>
            <a href="<?= base_url() ?>">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Corporate</span>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>ESG &amp; Durabilité</span>
        </div>
        <h1 class="esg-page-title">ESG et <span>durabilité</span></h1>
    </div>

    <div class="esg-content">

        <!-- ══════════════ (1) GOUVERNANCE, CONFORMITÉ ET TRANSPARENCE ══════════════ -->
        <div class="esg-block">
            <div class="esg-block-num">01</div>
            <h2 class="esg-block-title">(1) Gouvernance, conformité et transparence</h2>
            <p>
                NUFOTEC s'engage en faveur d'une gouvernance d'entreprise solide, d'une conformité réglementaire
                et d'une transparence financière en tant que piliers fondamentaux d'une croissance industrielle
                durable. Le déploiement de <strong>plus de 40 millions USD</strong> en capital d'amorçage donne la
                priorité aux infrastructures conformes aux BPF (Bonnes Pratiques de Fabrication), aux systèmes de
                certification ISO (ISO 9001, 22000, 14001, 45001 et 14644), à la tenue d'audits financiers
                indépendants annuels, à la production de rapports sur les performances ESG (Environnementales,
                Sociales et de Gouvernance), à la supervision des risques et aux structures de gouvernance au
                niveau du conseil d'administration, garantissant ainsi la responsabilité et l'intégrité
                institutionnelle.
            </p>
            <p style="font-weight:700;color:#0B5D3B;margin-bottom:0;">Engagements en matière de gouvernance :</p>
            <ul class="esg-list">
                <li>Mise en œuvre complète des BPF et documentation</li>
                <li>Achèvement de la feuille de route de certification ISO</li>
                <li>Audits financiers indépendants annuels</li>
                <li>Suivi et rapport des performances ESG</li>
                <li>Systèmes structurés de conformité et de gestion des risques</li>
            </ul>
        </div>

        <!-- ══════════════ (2) SUPERVISION INSTITUTIONNELLE ══════════════ -->
        <div class="esg-block">
            <div class="esg-block-num">02</div>
            <h2 class="esg-block-title">(2) Supervision institutionnelle et responsabilité</h2>
            <p>
                La société NUFOTEC opère selon un modèle de gouvernance d'entreprise structuré qui garantit :
            </p>
            <ul class="esg-list">
                <li>Une <strong>séparation claire</strong> entre les actionnaires, le Conseil d'administration et la Direction Générale</li>
                <li>Des <strong>responsabilités fiduciaires définies</strong> et des cadres de délégation de pouvoir</li>
                <li>Une <strong>expertise consultative indépendante</strong> en sciences réglementaires, finances, ESG et gestion des risques</li>
                <li>Une <strong>supervision structurée par le conseil d'administration</strong> de la stratégie, de la conformité et de l'allocation du capital</li>
            </ul>
            <div class="esg-callout">
                <i class="bi bi-award"></i>
                <p>
                    Ce cadre de gouvernance s'aligne sur les meilleures pratiques internationales reconnues par des
                    institutions telles que la Société Financière Internationale et la Banque mondiale.
                </p>
            </div>
        </div>

        <!-- ══════════════ (2) CONFORMITÉ RÉGLEMENTAIRE ══════════════ -->
        <div class="esg-block">
            <div class="esg-block-num">03</div>
            <h2 class="esg-block-title">(2) Conformité réglementaire, assurance qualité et autorisations</h2>
            <p>
                NUFOTEC construit un écosystème de conformité intégré aligné sur les normes internationales
                reconnues, y compris mais sans s'y limiter :
            </p>
            <div class="esg-chips">
                <span class="esg-chip"><i class="bi bi-patch-check"></i> ISO 9001 – Système de Management de la Qualité</span>
                <span class="esg-chip"><i class="bi bi-patch-check"></i> ISO 22000 – Sécurité des Denrées Alimentaires</span>
                <span class="esg-chip"><i class="bi bi-patch-check"></i> ISO 14001 – Gouvernance Environnementale et Durabilité</span>
                <span class="esg-chip"><i class="bi bi-patch-check"></i> ISO 45001 – Santé &amp; Sécurité au Travail</span>
                <span class="esg-chip"><i class="bi bi-patch-check"></i> ISO 14644 – Qualification des Salles Blanches</span>
            </div>
            <p style="margin-top:26px;">
                Notre feuille de route de production intègre les <strong>Bonnes Pratiques de Fabrication (BPF)</strong>,
                des systèmes de laboratoire validés, des contrôles de traçabilité, et des protocoles de
                documentation réglementaire requis pour les marchés d'exportation régionaux et internationaux des
                MTCAs, des Nutraceutiques/Compléments Alimentaires, des Phytomédicines et Phytopharmaceutiques, des
                aliments santé fortifiés clean-label et des boissons aux fruits.
            </p>
            <p style="font-weight:700;color:#0B5D3B;margin-bottom:0;">
                NUFOTEC-PHYTOMED INDUSTRIES est soumise aux autorisations réglementaires suivantes :
            </p>
            <ul class="esg-licenses">
                <li><i class="bi bi-patch-check"></i> Demande d'exploitation &amp; Enregistrement – <strong>PACRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Licence d'Investissement – <strong>ZDA</strong></li>
                <li><i class="bi bi-patch-check"></i> Certificat d'Étude d'Impact Environnemental (EIE) – <strong>ZEMA</strong></li>
                <li><i class="bi bi-patch-check"></i> Certificat de Rapport EPB de Sélection – <strong>ZEMA</strong></li>
                <li><i class="bi bi-patch-check"></i> Licence de Gestion des Déchets – <strong>ZEMA</strong></li>
                <li><i class="bi bi-patch-check"></i> Certificat d'Approbation de Site – <strong>ZMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Enregistrement de Produit – <strong>ZMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Inspection BPF – <strong>ZAMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Licence de Fabrication – <strong>ZAMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Permis d'Investisseur – <strong>Immigration</strong></li>
                <li><i class="bi bi-patch-check"></i> Test de Produit &amp; Demande – <strong>ZABS</strong></li>
                <li><i class="bi bi-patch-check"></i> Inspection d'Usine – <strong>ZABS</strong></li>
                <li><i class="bi bi-patch-check"></i> Marque de Certification – <strong>ZABS</strong></li>
                <li><i class="bi bi-patch-check"></i> Certificat de Salubrité des Locaux – <strong>Département de Santé Local (Municipal/District)</strong></li>
                <li><i class="bi bi-patch-check"></i> Certificat d'Inspection/Conformité à la Sécurité Sanitaire des Aliments – <strong>Département de Santé Local (Municipal/District)</strong></li>
                <li><i class="bi bi-patch-check"></i> Certificat de Manipulation/Sécurité Alimentaire – <strong>Département de Santé Local (Municipal/District)</strong></li>
                <li><i class="bi bi-patch-check"></i> Certificat d'Évaluation des Risques pour la Santé au Travail – <strong>Département de Santé Local (Municipal/District)</strong></li>
            </ul>
        </div>

        <!-- ══════════════ (3) TRANSPARENCE ET INTÉGRITÉ FINANCIÈRE ══════════════ -->
        <div class="esg-block">
            <div class="esg-block-num">04</div>
            <h2 class="esg-block-title">(3) Transparence et intégrité financière</h2>
            <p>Nous nous engageons à :</p>
            <ul class="esg-list">
                <li>Des audits statutaires indépendants</li>
                <li>Des rapports financiers transparents</li>
                <li>La divulgation des performances ESG</li>
                <li>La traçabilité de l'utilisation des fonds pour le capital institutionnel</li>
                <li>La mesure de l'impact et son rapport aligné sur les métriques de durabilité</li>
            </ul>
            <div class="esg-callout">
                <i class="bi bi-shield-check"></i>
                <p>
                    NUFOTEC maintient une tolérance zéro envers la fraude, la corruption, les achats contraires à
                    l'éthique ou la non-conformité réglementaire.
                </p>
            </div>
        </div>

        <!-- ══════════════ (4) ASSURANCE INVESTISSEURS ══════════════ -->
        <div class="esg-block">
            <div class="esg-block-num">05</div>
            <h2 class="esg-block-title">(4) Assurance pour les investisseurs et donateurs</h2>
            <p>
                Nos systèmes de gouvernance et de conformité sont conçus pour garantir que le capital déployé dans
                l'installation NUFOTEC-PHYTOMED INDUSTRIES :
            </p>
            <ul class="esg-list">
                <li>Est <strong>protégé par une supervision structurée</strong></li>
                <li>Est <strong>alloué conformément aux jalons stratégiques approuvés</strong></li>
                <li>Est <strong>suivi à travers des indicateurs de performance ESG et financiers mesurables</strong></li>
                <li>Est <strong>rapporté de manière transparente aux actionnaires</strong></li>
            </ul>
        </div>

        <!-- ══════════════ (5) POSITIONNEMENT STRATÉGIQUE ══════════════ -->
        <div class="esg-position">
            <span class="esg-tag"><i class="bi bi-rocket-takeoff"></i> Positionnement</span>
            <h2 class="esg-block-title">(5) Déclaration de positionnement stratégique pour l'investissement</h2>
            <p>
                L'installation NUFOTEC-PHYTOMED INDUSTRIES est structurée par African Green Farmers Limited en tant
                que plateforme industrielle <strong>bancable, prête pour l'audit, alignée sur les critères ESG</strong>
                et capable d'absorber les capitaux institutionnels de manière responsable tout en générant des
                rendements environnementaux, sociaux et financiers mesurables.
            </p>
            <p style="margin-bottom:0;">
                Notre cadre de gouvernance réduit les risques systémiques, renforce l'acceptation réglementaire et
                améliore la crédibilité sur les marchés internationaux – positionnant NUFOTEC comme
                <strong>un partenaire de confiance</strong> pour un investissement à long terme et axé sur l'impact.
            </p>
        </div>

    </div>

</main>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
