<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== DIGITAL GROWTH PLATFORM — STATIC VIEW ===================== -->
<style>
    .dgp-wrap{background:#fff}
    .dgp-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .dgp-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .dgp-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .dgp-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .dgp-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .dgp-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .dgp-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.5rem,3vw,2.3rem);line-height:1.3;margin:0 0 10px;max-width:980px}
    .dgp-hero h1 .dgp-brace{color:#D4A017}
    .dgp-hero .dgp-tagline{position:relative;z-index:2;display:inline-flex;align-items:center;gap:10px;background:rgba(212,160,23,.14);border:1px solid rgba(212,160,23,.4);color:#F3D98B;font-size:.82rem;font-weight:600;letter-spacing:.4px;padding:7px 16px;border-radius:50px;margin:0 0 16px}
    .dgp-hero .dgp-tagline i{color:#D4A017}
    .dgp-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    .dgp-body{padding:52px 0 70px}
    .dgp-block{display:flex;gap:22px;margin:0 0 34px}
    .dgp-block .dgp-num{flex:0 0 auto;width:52px;height:52px;display:flex;align-items:center;justify-content:center;background:#083D2A;color:#D4A017;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.15rem;border-radius:14px;box-shadow:0 6px 16px rgba(8,61,42,.22)}
    .dgp-block .dgp-content{flex:1;min-width:0}
    .dgp-block h2{margin:4px 0 14px;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.15rem,1.8vw,1.45rem);color:#083D2A}
    .dgp-block p{color:#3d4f45;font-size:.93rem;line-height:1.8;margin:0 0 10px}
    .dgp-callout{background:#FBF7EA;border:1px solid #EADFAE;border-left:5px solid #D4A017;border-radius:10px;padding:14px 18px;font-size:.9rem;line-height:1.7;color:#6b5d2e}
    .dgp-callout strong{color:#083D2A}
    .dgp-stats{display:flex;flex-wrap:wrap;gap:14px;margin:0 0 18px}
    .dgp-stat{background:#F5F9F6;border:1px solid #E3EDE6;border-radius:12px;padding:16px 22px;min-width:210px}
    .dgp-stat .dgp-stat-val{font-family:'Poppins',sans-serif;font-weight:700;font-size:1.5rem;color:#0B5D3B}
    .dgp-stat .dgp-stat-val span{color:#D4A017}
    .dgp-stat .dgp-stat-lbl{font-size:.8rem;color:#5b6f62;margin-top:2px}
    .dgp-partner{display:inline-flex;align-items:center;gap:8px;background:#E8F2EC;border:1px solid #C9DFD1;color:#0B5D3B;font-size:.82rem;font-weight:600;padding:6px 14px;border-radius:50px;margin:0 0 16px}
    .dgp-partner i{color:#D4A017}
    .dgp-list{margin:0 0 12px;padding:0;list-style:none}
    .dgp-list li{position:relative;padding:7px 0 7px 28px;color:#3d4f45;font-size:.9rem;line-height:1.65}
    .dgp-list li::before{content:"\f058";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;left:2px;top:9px;font-size:.82rem;color:#D4A017}
    .dgp-list--dot li::before{content:"\f111";font-size:.5rem;top:15px;color:#0B5D3B}
    .dgp-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin:0 0 14px}
    .dgp-item{background:#fff;border:1px solid #E3EDE6;border-radius:12px;padding:16px 18px;box-shadow:0 4px 14px rgba(8,61,42,.05)}
    .dgp-item h3{margin:0 0 8px;font-size:.92rem;font-weight:700;color:#083D2A;font-family:'Poppins',sans-serif}
    .dgp-item h3 i{color:#D4A017;margin-right:8px}
    .dgp-item p{margin:0;font-size:.86rem;line-height:1.65;color:#3d4f45}
    .dgp-foot{background:linear-gradient(135deg,#083D2A,#0B5D3B);border-radius:14px;padding:26px 30px;color:#fff;margin-top:10px;position:relative;overflow:hidden}
    .dgp-foot::before{content:"";position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:rgba(212,160,23,.15);filter:blur(40px)}
    .dgp-foot p{margin:0;position:relative;z-index:2;font-size:.94rem;line-height:1.8;color:rgba(255,255,255,.92)}
    .dgp-foot p strong{color:#F3D98B}
    .dgp-foot p .dgp-quote{color:#D4A017}
    @media(max-width:768px){.dgp-grid{grid-template-columns:1fr}.dgp-block{flex-direction:column;gap:12px}}
</style>

<section class="dgp-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="dgp-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Plateforme de croissance digitale</span>
            </div>
            <div class="dgp-tagline"><i class="fa-solid fa-robot"></i> Croissance du marché pilotée par l'IA, multicanal et communautaire</div>
            <h1>Plateforme de croissance digitale <span class="dgp-brace">{</span> d'expansion commerciale</h1>
            <div class="dgp-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="dgp-body">
        <div class="container">

            <!-- 1) Aperçu stratégique -->
            <div class="dgp-block">
                <div class="dgp-num">1</div>
                <div class="dgp-content">
                    <h2>Aperçu stratégique</h2>
                    <p>
                        La plateforme de croissance digitale et d'expansion commerciale d'NUFOTEC centralise tous les
                        canaux de marketing digital stratégiques, y compris les groupes WhatsApp éducatifs existants et
                        en constante expansion, Telegram, Facebook, Instagram, TikTok, YouTube, les e-mails et le site
                        web, dans un système unique et coordonné utilisant HubSpot Marketing Hub. Intégrée à ChatGPT IA,
                        cette plateforme automatise le contenu quotidien, les publicités et l'engagement communautaire,
                        garantissant une expansion du marché scalable, mesurable et professionnelle.
                    </p>
                    <div class="dgp-callout">
                        <strong>Avantage pour l'investisseur :</strong> KPI transparents, analyses en temps réel et
                        croissance prévisible sur les marchés domestiques, régionaux et internationaux.
                    </div>
                </div>
            </div>

            <!-- 2) Engagement communautaire et d'experts -->
            <div class="dgp-block">
                <div class="dgp-num">2</div>
                <div class="dgp-content">
                    <h2>Engagement communautaire et d'experts</h2>
                    <div class="dgp-stats">
                        <div class="dgp-stat">
                            <div class="dgp-stat-val">235<span>+</span></div>
                            <div class="dgp-stat-lbl">Groupes WhatsApp</div>
                        </div>
                        <div class="dgp-stat">
                            <div class="dgp-stat-val">211 500<span>+</span></div>
                            <div class="dgp-stat-lbl">Participants, en expansion quotidienne</div>
                        </div>
                    </div>
                    <div class="dgp-partner"><i class="fa-solid fa-handshake"></i> Partenaire clé : NUFOTEC BURUNDI (EAC)</div>
                    <p>
                        <strong style="color:#083D2A">Contenu dirigé par des experts :</strong> des nutritionnistes,
                        diététiciens et professionnels de santé agréés fournissent des conseils quotidiens et des
                        informations sur :
                    </p>
                    <ul class="dgp-list">
                        <li><strong>Maladies aiguës :</strong> ex. toux, diarrhée, infections respiratoires</li>
                        <li><strong>Maladies chroniques :</strong> cancer, diabète, maladies cardiovasculaires, obésité, malnutrition infantile</li>
                        <li><strong>Nutrition préventive,</strong> remèdes maison et agriculture biologique</li>
                    </ul>
                    <p>
                        Les communautés agissent comme des canaux actifs de marketing et d'éducation, convertissant
                        l'engagement en adoption de produits et en développement économique.
                    </p>
                </div>
            </div>

            <!-- 3) Services ChatGPT IA -->
            <div class="dgp-block">
                <div class="dgp-num">3</div>
                <div class="dgp-content">
                    <h2>Services ChatGPT IA</h2>
                    <p><strong style="color:#083D2A">Rôle :</strong> pilote tout le contenu automatisé et la publicité sur le réseau de canaux de marketing digital stratégique d'NUFOTEC.</p>
                    <div class="dgp-grid">
                        <div class="dgp-item">
                            <h3><i class="fa-solid fa-calendar-check"></i>Contenu quotidien automatisé</h3>
                            <p>Conseils santé, nutrition préventive, actualités produits et remèdes maison distribués sur toutes nos plateformes.</p>
                        </div>
                        <div class="dgp-item">
                            <h3><i class="fa-solid fa-bullhorn"></i>Publicités automatisées</h3>
                            <p>Campagnes ciblées nationales, régionales et internationales.</p>
                        </div>
                        <div class="dgp-item">
                            <h3><i class="fa-solid fa-headset"></i>Interaction communautaire en temps réel</h3>
                            <p>Assistance et collecte de commentaires 24h/24 et 7j/7 alimentées par l'IA.</p>
                        </div>
                        <div class="dgp-item">
                            <h3><i class="fa-solid fa-chart-line"></i>Analyses et reporting</h3>
                            <p>Suit l'engagement, l'adoption et l'impact ESG via des tableaux de bord en temps réel.</p>
                        </div>
                    </div>
                    <div class="dgp-callout">
                        <strong>Impact :</strong> ChatGPT assure une sensibilisation continue, coordonnée et mesurable,
                        transformant les communautés en canaux de marché actifs.
                    </div>
                </div>
            </div>

            <!-- 4) Impact de l'expansion commerciale -->
            <div class="dgp-block">
                <div class="dgp-num">4</div>
                <div class="dgp-content">
                    <h2>Impact de l'expansion commerciale</h2>
                    <ul class="dgp-list dgp-list--dot">
                        <li>Adoption rapide et reconnaissance de la marque</li>
                        <li>Régional (EAC, COMESA, SADC)</li>
                        <li>Contenu optimisé par l'IA pour l'engagement transfrontalier</li>
                        <li>Portée internationale facilitée</li>
                        <li>Les campagnes automatisées atteignent les distributeurs et partenaires mondiaux</li>
                    </ul>
                </div>
            </div>

            <!-- 5) Points forts pour les investisseurs et donateurs -->
            <div class="dgp-block">
                <div class="dgp-num">5</div>
                <div class="dgp-content">
                    <h2>Points forts pour les investisseurs et donateurs</h2>
                    <ul class="dgp-list dgp-list--dot">
                        <li><strong>Traction prouvée :</strong> 235+ groupes WhatsApp, 211 500+ participants</li>
                        <li>L'automatisation par IA HubSpot + ChatGPT garantit un engagement cohérent et des mises à jour quotidiennes</li>
                        <li>Les conseils d'experts renforcent la confiance et l'adoption</li>
                        <li>Les tableaux de bord en temps réel fournissent des KPI transparents et des rapports ESG</li>
                        <li>Un système unique et coordonné permet une croissance scalable et prévisible</li>
                    </ul>
                </div>
            </div>

            <!-- Pied de page / Phrase d'accroche -->
            <div class="dgp-foot">
                <p>
                    <span class="dgp-quote">«</span> Propulsée par la société NUFOTEC et HubSpot intégré à ChatGPT IA,
                    la plateforme d'NUFOTEC fournit du contenu quotidien automatisé, des conseils d'experts et une
                    promotion de produits axée sur la communauté, transformant les communautés en canaux de croissance
                    actifs pour la santé, le développement économique et l'expansion du marché mondial.
                    <span class="dgp-quote">»</span>
                </p>
            </div>

        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
