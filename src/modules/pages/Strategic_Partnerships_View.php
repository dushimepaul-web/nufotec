<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== STRATEGIC PARTNERSHIPS — STATIC VIEW ===================== -->
<style>
    .stp-wrap{background:#fff}
    .stp-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .stp-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .stp-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .stp-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .stp-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .stp-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .stp-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.4vw,2.5rem);line-height:1.25;margin:0 0 10px}
    .stp-hero h1 .stp-brace{color:#D4A017}
    .stp-hero .stp-tagline{position:relative;z-index:2;display:inline-flex;align-items:center;gap:10px;background:rgba(212,160,23,.14);border:1px solid rgba(212,160,23,.4);color:#F3D98B;font-size:.82rem;font-weight:600;letter-spacing:.4px;padding:7px 16px;border-radius:50px;margin:0 0 16px}
    .stp-hero .stp-tagline i{color:#D4A017}
    .stp-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    .stp-body{padding:48px 0 70px}
    .stp-intro{background:#F5F9F6;border:1px solid #E3EDE6;border-left:5px solid #D4A017;border-radius:12px;padding:26px 30px;margin:0 0 36px}
    .stp-intro p{margin:0;font-size:.96rem;line-height:1.85;color:#2c3e35}
    .stp-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:24px}
    .stp-card{display:flex;flex-direction:column;background:#fff;border:1px solid #E3EDE6;border-radius:14px;padding:28px 26px 24px;box-shadow:0 8px 24px rgba(8,61,42,.06);transition:.25s}
    .stp-card:hover{transform:translateY(-4px);box-shadow:0 14px 34px rgba(8,61,42,.13);border-color:#c9dfd1}
    .stp-card .stp-head{display:flex;gap:14px;align-items:flex-start;margin:0 0 14px}
    .stp-card .stp-ico{flex:0 0 auto;width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:#083D2A;color:#D4A017;font-size:1.1rem;border-radius:12px;box-shadow:0 6px 16px rgba(8,61,42,.22)}
    .stp-card h2{margin:4px 0 0;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.08rem;color:#083D2A;line-height:1.4}
    .stp-obj{background:#FBF7EA;border:1px solid #EADFAE;border-radius:8px;padding:11px 14px;font-size:.85rem;line-height:1.6;color:#6b5d2e;margin:0 0 14px}
    .stp-obj strong{color:#9a7a10;text-transform:uppercase;font-size:.72rem;letter-spacing:.6px;display:block;margin-bottom:3px}
    .stp-scopes{display:flex;flex-direction:column;gap:10px;margin-top:auto}
    .stp-scope{display:flex;gap:10px;align-items:flex-start;background:#FAFCFB;border:1px solid #EDF3EF;border-radius:10px;padding:10px 13px}
    .stp-scope .stp-tag{flex:0 0 auto;width:88px;font-size:.68rem;font-weight:700;letter-spacing:.6px;text-align:center;padding:5px 0;border-radius:6px;margin-top:1px}
    .stp-tag--dom{background:#E8F2EC;color:#0B5D3B}
    .stp-tag--reg{background:#FBF7EA;color:#9a7a10}
    .stp-tag--int{background:#083D2A;color:#D4A017}
    .stp-scope p{margin:0;font-size:.84rem;line-height:1.65;color:#3d4f45}
    @media(max-width:992px){.stp-grid{grid-template-columns:1fr}}
</style>

<section class="stp-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="stp-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Partenariats stratégiques</span>
            </div>
            <div class="stp-tagline"><i class="fa-solid fa-handshake"></i> Partenaires financiers, techniques et scientifiques</div>
            <h1>Partenariats <span class="stp-brace">{</span> stratégiques</h1>
            <div class="stp-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="stp-body">
        <div class="container">

            <!-- Introduction -->
            <div class="stp-intro">
                <p>
                    L'INSTALLATION NUFOTEC-PHYTOMED INDUSTRIES collabore avec un réseau soigneusement structuré de
                    partenaires domestiques, régionaux et internationaux. Ces partenariats intègrent la recherche
                    scientifique, la validation clinique, l'expertise en fabrication, les conseils réglementaires et le
                    soutien financier afin de garantir que tous les médicaments naturels que nous produisons sont sûrs,
                    standardisés et crédibles à l'échelle mondiale.
                </p>
            </div>

            <div class="stp-grid">

                <!-- 1. Recherche et validation scientifique -->
                <div class="stp-card">
                    <div class="stp-head">
                        <div class="stp-ico"><i class="fa-solid fa-flask-vial"></i></div>
                        <h2>Recherche et validation scientifique</h2>
                    </div>
                    <div class="stp-obj">
                        <strong>Objectif</strong>
                        Valider les composés végétaux pour les maladies chroniques, notamment le cancer, le diabète et
                        les maladies cardiovasculaires.
                    </div>
                    <div class="stp-scopes">
                        <div class="stp-scope"><span class="stp-tag stp-tag--dom">Domestique</span><p>Université de Zambie – recherche préclinique, identification de biomarqueurs et études translationnelles.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--reg">Régional</span><p>Université de Cape Town – études pharmacologiques et modélisation des maladies chroniques.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--int">International</span><p>Harvard Medical School, Université d'Oxford, Karolinska Institutet – validation de mécanismes, recherche translationnelle et développement conjoint de propriété intellectuelle.</p></div>
                    </div>
                </div>

                <!-- 2. Essais cliniques et réseaux de santé -->
                <div class="stp-card">
                    <div class="stp-head">
                        <div class="stp-ico"><i class="fa-solid fa-user-doctor"></i></div>
                        <h2>Essais cliniques et réseaux de santé</h2>
                    </div>
                    <div class="stp-obj">
                        <strong>Objectif</strong>
                        Mener des études sur l'homme pour démontrer la sécurité et l'efficacité.
                    </div>
                    <div class="stp-scopes">
                        <div class="stp-scope"><span class="stp-tag stp-tag--dom">Domestique</span><p>Hôpitaux de Lusaka et cliniques MNT – recrutement de patients et suivi clinique.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--reg">Régional</span><p>Hôpitaux de référence régionaux – coordination et gestion des études cliniques.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--int">International</span><p>MD Anderson Cancer Center, Mayo Clinic – collaboration sur les essais en oncologie et métabolisme, analyse statistique et conformité GCP.</p></div>
                    </div>
                </div>

                <!-- 3. Autorisation réglementaire et de mise sur le marché -->
                <div class="stp-card">
                    <div class="stp-head">
                        <div class="stp-ico"><i class="fa-solid fa-file-shield"></i></div>
                        <h2>Autorisation réglementaire et de mise sur le marché</h2>
                    </div>
                    <div class="stp-obj">
                        <strong>Objectif</strong>
                        Assurer la conformité aux normes nationales et internationales.
                    </div>
                    <div class="stp-scopes">
                        <div class="stp-scope"><span class="stp-tag stp-tag--dom">Domestique</span><p>Zambia Medicines Regulatory Authority – enregistrement des produits et conseils en pharmacovigilance.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--reg">Régional</span><p>Autorités réglementaires du COMESA – alignement régional et soutien à la conformité.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--int">International</span><p>Organisation mondiale de la santé – conseils sur l'intégration de la médecine traditionnelle et les cadres mondiaux des MNT.</p></div>
                    </div>
                </div>

                <!-- 4. Fabrication et assurance qualité -->
                <div class="stp-card">
                    <div class="stp-head">
                        <div class="stp-ico"><i class="fa-solid fa-gears"></i></div>
                        <h2>Fabrication et assurance qualité</h2>
                    </div>
                    <div class="stp-obj">
                        <strong>Objectif</strong>
                        Production à l'échelle industrielle selon les normes BPF internationalement reconnues.
                    </div>
                    <div class="stp-scopes">
                        <div class="stp-scope"><span class="stp-tag stp-tag--dom">Domestique</span><p>Consultants locaux en BPF – audits d'installations, validation des processus et gestion de la qualité.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--reg">Régional</span><p>Sociétés de certification régionales – standardisation des lots et assurance qualité.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--int">International</span><p>SGS, Bureau Veritas, TÜV Rheinland – certification BPF, validation des processus et conformité à l'exportation.</p></div>
                    </div>
                </div>

                <!-- 5. Tests de sécurité et précliniques -->
                <div class="stp-card">
                    <div class="stp-head">
                        <div class="stp-ico"><i class="fa-solid fa-dna"></i></div>
                        <h2>Tests de sécurité et précliniques</h2>
                    </div>
                    <div class="stp-obj">
                        <strong>Objectif</strong>
                        Confirmer la sécurité de qualité réglementaire des produits avant les essais sur l'homme.
                    </div>
                    <div class="stp-scopes">
                        <div class="stp-scope"><span class="stp-tag stp-tag--dom">Domestique</span><p>Laboratoires précliniques locaux – criblage initial de toxicité et de sécurité.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--reg">Régional</span><p>Laboratoires BPL régionaux – toxicité organique et études d'exposition chronique.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--int">International</span><p>Charles River Laboratories, Evotec – toxicologie conforme aux BPL, pharmacocinétique et études habilitantes pour les IND.</p></div>
                    </div>
                </div>

                <!-- 6. Partenaires financiers et de développement -->
                <div class="stp-card">
                    <div class="stp-head">
                        <div class="stp-ico"><i class="fa-solid fa-building-columns"></i></div>
                        <h2>Partenaires financiers et de développement</h2>
                    </div>
                    <div class="stp-obj">
                        <strong>Objectif</strong>
                        Fournir un financement, des conseils ESG et une mesure d'impact.
                    </div>
                    <div class="stp-scopes">
                        <div class="stp-scope"><span class="stp-tag stp-tag--dom">Domestique</span><p>Banque de Zambie, IFD locales – financement de démarrage et conseil ESG.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--reg">Régional</span><p>Banque africaine de développement – mobilisation de capitaux, cadres ESG et financement mixte.</p></div>
                        <div class="stp-scope"><span class="stp-tag stp-tag--int">International</span><p>International Finance Corporation, Fondation Bill &amp; Melinda Gates – financement à impact, structuration de financements mixtes et alignement avec les donateurs mondiaux.</p></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
