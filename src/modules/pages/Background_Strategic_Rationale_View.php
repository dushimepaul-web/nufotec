<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================================
   PAGE : CONTEXTE ET JUSTIFICATION STRATÉGIQUE (statique)
   NUFOTEC-PHYTOMED INDUSTRIES
   ============================================================ */
.bgr-simple {
    font-family: 'Poppins', sans-serif;
    background: #fff;
    color: #33403A;
    overflow-x: hidden;
}
.bgr-simple * { font-family: 'Poppins', sans-serif; }

/* En-tête de page */
.bgr-page-head {
    position: relative;
    background: linear-gradient(135deg, #083D2A, #0B5D3B);
    padding: 84px 0 64px;
    text-align: center;
    overflow: hidden;
}
.bgr-page-head::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -60px;
    width: 340px; height: 340px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212,160,23,.18) 0%, transparent 70%);
}
.bgr-page-head .bgr-crumb {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.85); font-size: .8rem; font-weight: 500;
    padding: 7px 16px; border-radius: 50px; margin-bottom: 22px;
}
.bgr-page-head .bgr-crumb a { color: rgba(255,255,255,.85); text-decoration: none; }
.bgr-page-head .bgr-crumb a:hover { color: #D4A017; }
.bgr-page-title {
    color: #fff;
    font-size: clamp(1.8rem, 4vw, 2.9rem);
    font-weight: 800;
    line-height: 1.25;
    margin: 0;
    position: relative;
    z-index: 1;
}
.bgr-page-title span { color: #D4A017; }

/* Contenu */
.bgr-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 72px 24px 96px;
}
.bgr-content p {
    font-size: 1.05rem;
    line-height: 1.95;
    color: #33403A;
    margin-bottom: 26px;
    text-align: justify;
}
.bgr-content p strong { color: #0B5D3B; }

.bgr-proc-list {
    list-style: none;
    margin: 34px 0 26px;
    padding: 0;
    counter-reset: bgr-proc;
}
.bgr-proc-list li {
    position: relative;
    background: #F7FAF8;
    border: 1px solid #E3EBE6;
    border-left: 5px solid #0B5D3B;
    border-radius: 16px;
    padding: 22px 26px 22px 88px;
    margin-bottom: 18px;
    font-size: 1.02rem;
    line-height: 1.85;
    color: #33403A;
    counter-increment: bgr-proc;
}
.bgr-proc-list li::before {
    content: counter(bgr-proc);
    position: absolute;
    left: 22px; top: 50%;
    transform: translateY(-50%);
    width: 46px; height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, #0B5D3B, #0E7A4E);
    color: #fff;
    font-size: 1.35rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bgr-proc-list li strong { color: #083D2A; }

.bgr-cool-note {
    background: #EAF6EF;
    border-left: 5px solid #D4A017;
    border-radius: 16px;
    padding: 24px 30px;
    margin-top: 30px;
    font-size: 1.02rem;
    line-height: 1.85;
    color: #083D2A;
}

@media (max-width: 575.98px) {
    .bgr-page-head { padding: 64px 0 48px; }
    .bgr-content { padding: 52px 18px 72px; }
    .bgr-proc-list li { padding-left: 20px; padding-top: 82px; }
    .bgr-proc-list li::before { top: 20px; transform: none; }
}
</style>

<main class="bgr-simple">

    <!-- En-tête de page -->
    <div class="bgr-page-head">
        <div class="bgr-crumb">
            <i class="bi bi-house-door"></i>
            <a href="<?= base_url() ?>">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Corporate</span>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Contexte stratégique</span>
        </div>
        <h1 class="bgr-page-title">Contexte et justification <span>stratégique</span></h1>
    </div>

    <!-- Contenu -->
    <div class="bgr-content">

        <p>
            Le Burundi, comme de nombreuses nations africaines, est dotée d'une riche biodiversité de plantes
            médicinales et d'un fort héritage culturel d'utilisation de la médecine traditionnelle. Cependant, le
            secteur des Médecines Traditionnelles, Complémentaires et Alternatives (MTCAs) reste largement
            sous-développé, caractérisé par des pratiques rudimentaires, un manque de standardisation, un soutien
            minimal à la recherche et l'absence d'infrastructures de fabrication modernes. Malgré une volonté
            politique croissante, la reconnaissance par le ministère de la Santé et la mise en place d'organismes de
            réglementation tels que la ZAMRA, le pays manque encore de la capacité technologique nécessaire pour
            transformer les connaissances traditionnelles en produits de santé de haute qualité, sûrs et
            standardisés.
        </p>

        <p>
            NUFOTEC-PHYTOMED Industries est structurée, via son installation NUFOTEC-PHYTOMED Industries, comme une
            plateforme de fabrication agro-biotechnologique et phytopharmaceutique intégrée verticalement, alignée
            sur les BPF et aux normes ISO, conçue pour capturer la demande de croissance élevée pour les produits de
            santé préventifs et thérapeutiques à base de plantes sur les marchés domestiques et régionaux. En faisant
            passer la culture biologique commerciale de <strong>90 hectares à plus de 2 000 hectares</strong> de
            cultures médicinales et de cultures riches en nutriments à haute valeur ajoutée, y compris l'
            <strong>Aloe vera</strong> et la <strong>Carica papaya</strong>, et en déployant
            <strong>plus de 40 millions USD</strong> dans des infrastructures de transformation avancées, des
            environnements de fabrication en salle blanche, des laboratoires phytochimiques et microbiologiques et
            des systèmes qualité certifiés ISO, NUFOTEC assure la traçabilité des matières premières, établit de
            solides barrières à l'entrée, optimise les marges de valeur ajoutée et réduit la dépendance aux
            importations régionales. L'architecture de produits diversifiée – couvrant les MTCAs standardisées, les
            nutraceutiques, les aliments fonctionnels fortifiés clean-label, les engrais organiques riches en
            nutriments, les phytomédicines et phytopharmaceutiques cliniquement validés – crée des flux de revenus
            superposés avec une génération de trésorerie précoce et un positionnement phytopharmaceutique à long
            terme à haute marge, visant un chiffre d'affaires annuel de plusieurs millions de dollars, une
            performance EBITDA durable et une expansion exportable à l'échelle d'ici 2031.
        </p>

        <p>
            L'installation NUFOTEC-PHYTOMED INDUSTRIES émerge comme l'une des principales initiatives privées
            burundaises visant à mettre en œuvre des solutions industrielles automatisées, entièrement intégrées et
            de pointe dans le secteur des herbes et nutraceutiques. L'entreprise a adopté un système de ligne de
            production clé en main automatisé et personnalisé, rare, conforme aux BPF et aux normes ISO pour
            l'extraction, l'encapsulation et la mise en comprimés d'API (Principes Actifs Pharmaceutiques) à base de
            plantes. Cette configuration industrielle complexe et automatisée ― TURNKEY ULTRASOUND-ASSISTED
            EXTRACTION (UAE), CAPSULE PRODUCTION AND TABLETING LINE SYSTEM combine trois techniques de
            transformation avancées :
        </p>

        <ul class="bgr-proc-list">
            <li>
                <strong>Procédé d'extraction et de purification phytochimique</strong> – Traitement post-récolte,
                dégraissage éventuel, concentration, purification éventuelle, séchage sous vide et conditionnement
                de l'extrait pour stockage.
            </li>
            <li>
                <strong>Procédé de fabrication de gélules</strong> – Encapsulation, remplissage des flacons,
                capsulage et scellage, étiquetage des flacons, mise en carton des flacons, insertion de la notice et
                scellage et emballage en caisse de 15 flacons.
            </li>
            <li>
                <strong>Procédé de fabrication de comprimés</strong> – Mélange d'extrait sec, granulation, séchage
                sous vide éventuel, sécheur à lit fluidisé, compression des comprimés, enrobage des comprimés,
                remplissage des flacons, capsulage, scellage des flacons, étiquetage des flacons, mise en carton des
                flacons, insertion de la notice ainsi que l'emballage en caisse de 15 flacons.
            </li>
        </ul>

        <p>
            Le système de ligne fonctionne à des températures réduites (<strong>40–60°C</strong>) pour préserver les
            principes actifs pharmaceutiques thermosensibles (composés bioactifs) ciblés pour développer des
            phytomédicaments sûrs et efficaces, des nutraceutiques, des aliments santé fortifiés riches en
            nutriments et des engrais organiques riches en nutriments – une technologie de pointe sans précédent
            dans le secteur des MTCAs/phytomédecine au Burundi.
        </p>

        <div class="bgr-cool-note">
            <strong>Vision 2026-2031 :</strong> une technologie de pointe sans précédent dans le secteur des
            MTCAs/phytomédecine, au service d'une plateforme industrielle standardisée, sûre et à haute valeur
            ajoutée.
        </div>

    </div>

</main>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
