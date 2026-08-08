<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="canonical" href="<?= base_url('background-strategic-rationale') ?>">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "<?= base_url() ?>" },
    { "@type": "ListItem", "position": 2, "name": "Corporate", "item": "<?= base_url('background-strategic-rationale') ?>" },
    { "@type": "ListItem", "position": 3, "name": "Contexte et justification stratégique", "item": "<?= base_url('background-strategic-rationale') ?>" }
  ]
}
</script>

<style>
/* ============================================================
   PAGE : CONTEXTE ET JUSTIFICATION STRATÉGIQUE (statique)
   NUFOTEC-PHYTOMED INDUSTRIES
   ============================================================ */
.bgr-page {
    font-family: 'Poppins', sans-serif;
    color: #33403A;
    background: #fff;
    overflow-x: hidden;
}
.bgr-page * { font-family: 'Poppins', sans-serif; }
.bgr-page :is(h1, h2, h3, h4, h5, h6) { color: #083D2A; }

:root {
    --bgr-green: #0B5D3B;
    --bgr-green-dark: #083D2A;
    --bgr-green-deep: #052B1E;
    --bgr-gold: #D4A017;
    --bgr-soft: #EAF6EF;
    --bgr-gray: #F5F8F6;
    --bgr-text: #33403A;
    --bgr-muted: #5C6B64;
    --bgr-radius: 24px;
    --bgr-shadow: 0 18px 44px rgba(8, 61, 42, .10);
}

.bgr-container { max-width: 1320px; margin: 0 auto; padding: 0 24px; }
.bgr-section { padding: 96px 0; }
.bgr-section-gray { background: var(--bgr-gray); }

.bgr-badge {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--bgr-gold); color: #083D2A;
    font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 9px 26px; border-radius: 50px; margin-bottom: 26px;
}
.bgr-badge-green {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--bgr-soft); color: var(--bgr-green);
    font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 9px 26px; border-radius: 50px; margin-bottom: 26px; border: 1px solid #BBD8C6;
}
.bgr-h2 {
    font-size: clamp(1.7rem, 3vw, 2.5rem);
    font-weight: 800; line-height: 1.25; color: var(--bgr-green-dark);
    margin-bottom: 22px;
}
.bgr-lead { font-size: 1.12rem; color: var(--bgr-muted); line-height: 1.8; margin-bottom: 0; }

.bgr-text { font-size: 1.03rem; line-height: 1.9; color: var(--bgr-text); margin-bottom: 18px; }
.bgr-text strong { color: var(--bgr-green-dark); font-weight: 700; }

.bgr-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    padding: 15px 34px; border-radius: 50px; font-weight: 600; font-size: 1rem;
    text-decoration: none; transition: all .3s ease; border: 2px solid transparent;
}
.bgr-btn-green { background: var(--bgr-green); color: #fff; }
.bgr-btn-green:hover { background: #0E7A4E; color: #fff; transform: translateY(-3px); box-shadow: 0 14px 30px rgba(11, 93, 59, .3); }
.bgr-btn-gold { background: var(--bgr-gold); color: #083D2A; }
.bgr-btn-gold:hover { background: #E0AF2E; color: #083D2A; transform: translateY(-3px); box-shadow: 0 14px 30px rgba(212, 160, 23, .35); }
.bgr-btn-ghost { background: transparent; color: #fff; border-color: rgba(255,255,255,.55); }
.bgr-btn-ghost:hover { background: rgba(255,255,255,.12); color: #fff; transform: translateY(-3px); }

/* ══════════════ HERO ══════════════ */
.bgr-hero {
    position: relative; min-height: 560px;
    display: flex; align-items: center;
    overflow: hidden; padding: 130px 0 90px;
}
.bgr-hero-bg {
    position: absolute; inset: 0;
    background: url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
    transform: scale(1.05);
}
.bgr-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(115deg, rgba(5,43,30,.94) 20%, rgba(8,61,42,.82) 55%, rgba(8,61,42,.55) 100%);
}
.bgr-hero-content { position: relative; z-index: 3; max-width: 880px; }
.bgr-hero-crumb {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.85); font-size: .82rem; font-weight: 500;
    padding: 8px 18px; border-radius: 50px; margin-bottom: 28px; backdrop-filter: blur(6px);
}
.bgr-hero-crumb a { color: rgba(255,255,255,.85); text-decoration: none; }
.bgr-hero-crumb a:hover { color: var(--bgr-gold); }
.bgr-hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--bgr-gold); color: #083D2A;
    font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 9px 22px; border-radius: 50px; margin-bottom: 24px;
}
.bgr-hero-title {
    color: #fff; font-size: clamp(2.1rem, 4.6vw, 3.6rem);
    font-weight: 800; line-height: 1.18; margin-bottom: 24px;
}
.bgr-hero-title span { color: var(--bgr-gold); }
.bgr-hero-sub { color: rgba(255,255,255,.88); font-size: 1.12rem; line-height: 1.85; max-width: 720px; margin-bottom: 38px; }

/* ══════════════ CALLOUT ══════════════ */
.bgr-callout {
    display: flex; align-items: flex-start; gap: 18px;
    background: var(--bgr-soft); border-left: 5px solid var(--bgr-green);
    border-radius: 18px; padding: 22px 28px; margin: 30px 0;
}
.bgr-callout i { color: var(--bgr-green); font-size: 1.7rem; margin-top: 3px; }
.bgr-callout p { margin: 0; font-weight: 600; color: var(--bgr-green-dark); line-height: 1.7; font-size: 1.02rem; }

/* ══════════════ POINTS / CHIPS ══════════════ */
.bgr-points { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 30px; }
.bgr-point {
    display: flex; align-items: flex-start; gap: 14px;
    background: #fff; border: 1px solid #E3EBE6; border-radius: 16px;
    padding: 18px 20px; transition: all .3s ease;
}
.bgr-point:hover { border-color: #BBD8C6; box-shadow: 0 10px 24px rgba(11, 93, 59, .08); transform: translateY(-3px); }
.bgr-point i { color: var(--bgr-gold); font-size: 1.35rem; margin-top: 2px; }
.bgr-point p { margin: 0; font-size: .95rem; font-weight: 500; color: var(--bgr-text); line-height: 1.6; }

.bgr-chips { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
.bgr-chip {
    display: inline-flex; align-items: center; gap: 8px;
    background: #fff; border: 1px solid #DCE8E0; color: var(--bgr-green-dark);
    font-size: .9rem; font-weight: 600; padding: 10px 20px; border-radius: 50px;
    transition: all .3s ease;
}
.bgr-chip i { color: var(--bgr-gold); }
.bgr-chip:hover { background: var(--bgr-soft); border-color: var(--bgr-green); transform: translateY(-2px); }

/* ══════════════ IMAGE CARD ══════════════ */
.bgr-img-card {
    position: relative; border-radius: var(--bgr-radius);
    overflow: hidden; box-shadow: var(--bgr-shadow);
}
.bgr-img-card img { width: 100%; height: 100%; object-fit: cover; display: block; }
.bgr-img-badge {
    position: absolute; bottom: 22px; left: 22px;
    background: rgba(255,255,255,.95); color: var(--bgr-green-dark);
    font-weight: 700; font-size: .85rem; padding: 10px 20px; border-radius: 50px;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
}

/* ══════════════ STATS ══════════════ */
.bgr-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-top: 34px; }
.bgr-stat {
    background: #fff; border: 1px solid #E3EBE6; border-radius: 20px;
    padding: 28px 22px; text-align: center; transition: all .3s ease;
}
.bgr-stat:hover { transform: translateY(-6px); box-shadow: 0 16px 34px rgba(11, 93, 59, .10); border-color: #BBD8C6; }
.bgr-stat-value { font-size: 1.9rem; font-weight: 800; color: var(--bgr-green); line-height: 1.15; }
.bgr-stat-unit { font-size: 1rem; font-weight: 600; color: var(--bgr-gold); }
.bgr-stat-label { font-size: .88rem; font-weight: 500; color: var(--bgr-muted); margin-top: 10px; line-height: 1.5; }

/* ══════════════ TURNKEY HIGHLIGHT ══════════════ */
.bgr-tk {
    position: relative; overflow: hidden;
    background: linear-gradient(135deg, var(--bgr-green-dark), #0B5D3B);
    border-radius: var(--bgr-radius); padding: 36px 40px; margin-top: 34px;
}
.bgr-tk::after {
    content: '\F5C6'; font-family: 'bootstrap-icons';
    position: absolute; right: 30px; bottom: -30px; font-size: 130px; color: rgba(255,255,255,.07);
}
.bgr-tk-label { color: var(--bgr-gold); font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px; }
.bgr-tk-title { color: #fff; font-size: 1.35rem; font-weight: 700; line-height: 1.6; margin: 0; position: relative; z-index: 1; }
.bgr-tk-title small { display: block; color: rgba(255,255,255,.75); font-size: .95rem; font-weight: 500; margin-top: 10px; line-height: 1.7; }

/* ══════════════ PROCESS CARDS ══════════════ */
.bgr-proc { position: relative; background: #fff; border-radius: var(--bgr-radius); padding: 40px 34px; height: 100%; box-shadow: 0 10px 30px rgba(8,61,42,.06); border: 1px solid #EDF2EE; transition: all .35s ease; }
.bgr-proc:hover { transform: translateY(-8px); box-shadow: 0 22px 46px rgba(11, 93, 59, .14); border-color: #BBD8C6; }
.bgr-proc-num {
    width: 62px; height: 62px; border-radius: 18px;
    background: linear-gradient(135deg, var(--bgr-green), #0E7A4E);
    color: #fff; font-size: 1.7rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center; margin-bottom: 24px;
}
.bgr-proc-title { font-size: 1.25rem; font-weight: 700; color: var(--bgr-green-dark); margin-bottom: 18px; line-height: 1.4; }
.bgr-proc-steps { list-style: none; padding: 0; margin: 0; }
.bgr-proc-steps li {
    position: relative; padding: 9px 0 9px 30px;
    color: var(--bgr-text); font-size: .95rem; line-height: 1.65; border-bottom: 1px dashed #E3EBE6;
}
.bgr-proc-steps li:last-child { border-bottom: none; }
.bgr-proc-steps li::before {
    content: ''; position: absolute; left: 4px; top: 17px;
    width: 9px; height: 9px; border-radius: 50%;
    background: var(--bgr-gold); border: 2px solid #F6E3B5;
}
.bgr-proc-steps li strong { color: var(--bgr-green-dark); }

/* ══════════════ LOW TEMP PANEL ══════════════ */
.bgr-cool { position: relative; overflow: hidden; }
.bgr-cool-bg { position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat; }
.bgr-cool-overlay { position: absolute; inset: 0; background: linear-gradient(115deg, rgba(5,43,30,.95) 35%, rgba(8,61,42,.85) 100%); }
.bgr-cool-content { position: relative; z-index: 2; }
.bgr-cool h2 { color: #fff; }
.bgr-cool .bgr-text { color: rgba(255,255,255,.88); }
.bgr-temp {
    display: inline-flex; align-items: center; gap: 18px;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.22);
    border-radius: 18px; padding: 20px 30px; margin-bottom: 28px; backdrop-filter: blur(6px);
}
.bgr-temp i { color: var(--bgr-gold); font-size: 2rem; }
.bgr-temp-value { font-size: 2.2rem; font-weight: 800; color: var(--bgr-gold); line-height: 1; }
.bgr-temp-label { font-size: .92rem; color: rgba(255,255,255,.85); font-weight: 500; }

/* ══════════════ CTA ══════════════ */
.bgr-cta {
    position: relative; overflow: hidden; text-align: center;
    background: linear-gradient(135deg, var(--bgr-green-dark), #0B5D3B);
    border-radius: var(--bgr-radius); padding: 76px 40px;
}
.bgr-cta::before {
    content: ''; position: absolute; top: -70%; right: -30%; width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(212,160,23,.22) 0%, transparent 65%);
}
.bgr-cta h2 { color: #fff; font-size: clamp(1.8rem, 3.2vw, 2.7rem); font-weight: 800; margin-bottom: 22px; }
.bgr-cta p { color: rgba(255,255,255,.82); font-size: 1.12rem; max-width: 720px; margin: 0 auto 38px; }

/* ══════════════ RESPONSIVE ══════════════ */
@media (max-width: 991.98px) {
    .bgr-section { padding: 64px 0; }
    .bgr-stats { grid-template-columns: 1fr 1fr; }
    .bgr-hero { min-height: 480px; padding: 120px 0 70px; }
}
@media (max-width: 575.98px) {
    .bgr-hero { min-height: 440px; padding: 110px 0 60px; }
    .bgr-hero-title { font-size: 1.8rem; }
    .bgr-points { grid-template-columns: 1fr; }
    .bgr-stats { grid-template-columns: 1fr; }
    .bgr-btn { width: 100%; justify-content: center; }
}
</style>

<main class="bgr-page">

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- HERO -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-hero" id="bgr-hero">
        <div class="bgr-hero-bg"></div>
        <div class="bgr-hero-overlay"></div>
        <div class="container">
            <div class="bgr-hero-content">
                <div class="bgr-hero-crumb">
                    <i class="bi bi-house-door"></i>
                    <a href="<?= base_url() ?>">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
                    <span>Corporate</span>
                    <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
                    <span>Contexte stratégique</span>
                </div>
                <div class="bgr-hero-badge">
                    <i class="bi bi-compass"></i> Vision 2026-2031
                </div>
                <h1 class="bgr-hero-title" data-aos="fade-up">
                    Contexte et justification <span>stratégique</span>
                </h1>
                <p class="bgr-hero-sub" data-aos="fade-up" data-aos-delay="120">
                    Le Burundi possède l'une des biodiversités botaniques les plus riches d'Afrique et un héritage
                    profond de la médecine traditionnelle. NUFOTEC-PHYTOMED INDUSTRIES transforme cet héritage en
                    une plateforme industrielle moderne, standardisée et à haute valeur ajoutée.
                </p>
                <div class="d-flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="240">
                    <a href="<?= base_url('About/presentation') ?>" class="bgr-btn bgr-btn-gold">
                        <i class="bi bi-bullseye"></i> Découvrir notre vision
                    </a>
                    <a href="<?= base_url('Investors') ?>" class="bgr-btn bgr-btn-ghost">
                        <i class="bi bi-graph-up-arrow"></i> Investir
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 1 : LE CONTEXTE -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-section bg-white" id="bgr-contexte">
        <div class="container">
            <div class="row align-items-start g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="bgr-img-card" style="height: 520px;">
                        <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=1200&q=80"
                             alt="Plantes médicinales et produits naturels"
                             onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80'">
                        <div class="bgr-img-badge">
                            <i class="bi bi-flower1"></i> Héritage botanique africain
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div style="max-width: 850px;">
                        <span class="bgr-badge-green">Le contexte</span>
                        <h2 class="bgr-h2">Une richesse naturelle encore largement sous-exploitée</h2>
                        <p class="bgr-text">
                            Le Burundi, comme de nombreuses nations africaines, est dotée d'une riche biodiversité
                            de plantes médicinales et d'un fort héritage culturel d'utilisation de la médecine
                            traditionnelle. Cependant, le secteur des Médecines Traditionnelles, Complémentaires et
                            Alternatives (MTCAs) reste largement sous-développé, caractérisé par des pratiques
                            rudimentaires, un manque de standardisation, un soutien minimal à la recherche et
                            l'absence d'infrastructures de fabrication modernes.
                        </p>
                        <p class="bgr-text">
                            Malgré une volonté politique croissante, la reconnaissance par le ministère de la Santé
                            et la mise en place d'organismes de réglementation tels que la ZAMRA, le pays manque
                            encore de la capacité technologique nécessaire pour transformer les connaissances
                            traditionnelles en produits de santé de haute qualité, sûrs et standardisés.
                        </p>
                        <div class="bgr-points">
                            <div class="bgr-point">
                                <i class="bi bi-flower2"></i>
                                <p>Riche biodiversité de plantes médicinales et héritage traditionnel fort</p>
                            </div>
                            <div class="bgr-point">
                                <i class="bi bi-exclamation-triangle"></i>
                                <p>Pratiques rudimentaires, manque de standardisation et soutien minimal à la recherche</p>
                            </div>
                            <div class="bgr-point">
                                <i class="bi bi-bank"></i>
                                <p>Volonté politique croissante et réglementation émergente (ministère de la Santé, ZAMRA)</p>
                            </div>
                            <div class="bgr-point">
                                <i class="bi bi-lightbulb"></i>
                                <p>Capacité technologique à construire pour produire des MTCAs sûrs et standardisés</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 2 : LA PLATEFORME -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-section bgr-section-gray" id="bgr-plateforme">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 820px;" data-aos="fade-up">
                <span class="bgr-badge-green">La plateforme</span>
                <h2 class="bgr-h2">NUFOTEC-PHYTOMED INDUSTRIES : une plateforme agro-biotechnologique intégrée BPF et ISO</h2>
            </div>

            <div data-aos="fade-up">
                <p class="bgr-text">
                    NUFOTEC-PHYTOMED Industries est structurée, via son installation NUFOTEC-PHYTOMED Industries,
                    comme une plateforme de fabrication agro-biotechnologique et phytopharmaceutique intégrée
                    verticalement, alignée sur les BPF et aux normes ISO, conçue pour capturer la demande de
                    croissance élevée pour les produits de santé préventifs et thérapeutiques à base de plantes sur
                    les marchés domestiques et régionaux.
                </p>
                <p class="bgr-text">
                    En faisant passer la culture biologique commerciale de <strong>90 hectares à plus de 2 000
                    hectares</strong> de cultures médicinales et de cultures riches en nutriments à haute valeur
                    ajoutée, y compris l'<strong>Aloe vera</strong> et la <strong>Carica papaya</strong>, et en
                    déployant <strong>plus de 40 millions USD</strong> dans des infrastructures de transformation
                    avancées, des environnements de fabrication en salle blanche, des laboratoires phytochimiques et
                    microbiologiques et des systèmes qualité certifiés ISO, NUFOTEC assure la traçabilité des
                    matières premières, établit de solides barrières à l'entrée, optimise les marges de valeur
                    ajoutée et réduit la dépendance aux importations régionales.
                </p>
                <p class="bgr-text">
                    L'architecture de produits diversifiée – couvrant les MTCAs standardisées, les nutraceutiques,
                    les aliments fonctionnels fortifiés clean-label, les engrais organiques riches en nutriments,
                    les phytomédicines et phytopharmaceutiques cliniquement validés – crée des flux de revenus
                    superposés avec une génération de trésorerie précoce et un positionnement phytopharmaceutique à
                    long terme à haute marge, visant un chiffre d'affaires annuel de plusieurs millions de dollars,
                    une performance EBITDA durable et une expansion exportable à l'échelle d'ici 2031.
                </p>
            </div>

            <div class="bgr-stats" data-aos="fade-up" data-aos-delay="100">
                <div class="bgr-stat">
                    <div class="bgr-stat-value">90 → 2000<span class="bgr-stat-unit">+</span></div>
                    <div class="bgr-stat-label">Hectares de cultures médicinales et riches en nutriments</div>
                </div>
                <div class="bgr-stat">
                    <div class="bgr-stat-value">40<span class="bgr-stat-unit">+</span> M<span class="bgr-stat-unit"> USD</span></div>
                    <div class="bgr-stat-label">Déployés dans les infrastructures de transformation</div>
                </div>
                <div class="bgr-stat">
                    <div class="bgr-stat-value">6</div>
                    <div class="bgr-stat-label">Gammes de produits à flux de revenus superposés</div>
                </div>
                <div class="bgr-stat">
                    <div class="bgr-stat-value">BPF <span class="bgr-stat-unit">· ISO</span></div>
                    <div class="bgr-stat-label">Conformité BPF et systèmes qualité certifiés ISO</div>
                </div>
            </div>

            <div class="bgr-chips" data-aos="fade-up" data-aos-delay="180">
                <span class="bgr-chip"><i class="bi bi-capsule"></i> MTCAs standardisées</span>
                <span class="bgr-chip"><i class="bi bi-heart-pulse"></i> Nutraceutiques</span>
                <span class="bgr-chip"><i class="bi bi-basket2"></i> Aliments fonctionnels clean-label</span>
                <span class="bgr-chip"><i class="bi bi-tree"></i> Engrais organiques riches en nutriments</span>
                <span class="bgr-chip"><i class="bi bi-eyedropper"></i> Phytomédicines cliniquement validées</span>
                <span class="bgr-chip"><i class="bi bi-flask"></i> Phytopharmaceutiques</span>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 3 : L'INSTALLATION INDUSTRIELLE -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-section bg-white" id="bgr-installation">
        <div class="container">
            <div class="row align-items-start g-5">
                <div class="col-lg-6" data-aos="fade-left">
                    <div style="max-width: 850px;">
                        <span class="bgr-badge-green">L'installation</span>
                        <h2 class="bgr-h2">Une ligne de production clé en main, automatisée et de pointe</h2>
                        <p class="bgr-text">
                            L'installation NUFOTEC-PHYTOMED INDUSTRIES émerge comme l'une des principales initiatives
                            privées visant à mettre en œuvre des solutions industrielles automatisées, entièrement
                            intégrées et de pointe dans le secteur des herbes et nutraceutiques.
                        </p>
                        <p class="bgr-text">
                            L'entreprise a adopté un système de ligne de production clé en main automatisé et
                            personnalisé, rare, conforme aux BPF et aux normes ISO pour l'extraction, l'encapsulation
                            et la mise en comprimés d'<strong>API (Principes Actifs Pharmaceutiques)</strong> à base
                            de plantes. Cette configuration industrielle complexe et automatisée combine trois
                            techniques de transformation avancées.
                        </p>

                        <div class="bgr-tk">
                            <div class="bgr-tk-label"><i class="bi bi-gear-wide-connected"></i> Configuration industrielle</div>
                            <div class="bgr-tk-title">
                                TURNKEY ULTRASOUND-ASSISTED EXTRACTION (UAE)
                                <small>CAPSULE PRODUCTION AND TABLETING LINE SYSTEM — un système automatisé intégrant extraction par ultrasons, production de gélules et de comprimés.</small>
                            </div>
                        </div>

                        <div class="bgr-callout">
                            <i class="bi bi-patch-check"></i>
                            <p>
                                Trois techniques de transformation avancées combinées au sein d'une seule
                                configuration industrielle automatisée et clé en main.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="bgr-img-card" style="height: 560px;">
                        <img src="https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=1200&q=80"
                             alt="Ligne de production industrielle automatisée"
                             onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80'">
                        <div class="bgr-img-badge">
                            <i class="bi bi-cpu"></i> Ligne automatisée clé en main
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 4 : LES TROIS PROCÉDÉS -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-section bgr-section-gray" id="bgr-procedes">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;" data-aos="fade-up">
                <span class="bgr-badge-green">Nos procédés</span>
                <h2 class="bgr-h2">Trois procédés de transformation de pointe</h2>
                <p class="bgr-lead">De la matière première végétale au produit fini standardisé : une chaîne complète, automatisée et conforme aux BPF.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="bgr-proc">
                        <div class="bgr-proc-num">1</div>
                        <h3 class="bgr-proc-title">Extraction et purification phytochimique</h3>
                        <ul class="bgr-proc-steps">
                            <li><strong>Traitement post-récolte</strong></li>
                            <li><strong>Dégraissage</strong> éventuel</li>
                            <li><strong>Concentration</strong></li>
                            <li><strong>Purification</strong> éventuelle</li>
                            <li><strong>Séchage sous vide</strong></li>
                            <li><strong>Conditionnement de l'extrait</strong> pour stockage</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="120">
                    <div class="bgr-proc">
                        <div class="bgr-proc-num">2</div>
                        <h3 class="bgr-proc-title">Fabrication de gélules</h3>
                        <ul class="bgr-proc-steps">
                            <li><strong>Encapsulation</strong></li>
                            <li><strong>Remplissage des flacons</strong></li>
                            <li><strong>Capsulage et scellage</strong></li>
                            <li><strong>Étiquetage des flacons</strong></li>
                            <li><strong>Mise en carton</strong> des flacons</li>
                            <li><strong>Insertion de la notice</strong> et emballage en caisse de 15 flacons</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="240">
                    <div class="bgr-proc">
                        <div class="bgr-proc-num">3</div>
                        <h3 class="bgr-proc-title">Fabrication de comprimés</h3>
                        <ul class="bgr-proc-steps">
                            <li><strong>Mélange d'extrait sec</strong></li>
                            <li><strong>Granulation</strong> et séchage sous vide éventuel</li>
                            <li><strong>Sécheur à lit fluidisé</strong></li>
                            <li><strong>Compression</strong> et <strong>enrobage des comprimés</strong></li>
                            <li><strong>Remplissage, capsulage, scellage et étiquetage</strong> des flacons</li>
                            <li><strong>Mise en carton, notice</strong> et emballage en caisse de 15 flacons</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 5 : TECHNOLOGIE BASSE TEMPÉRATURE -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-section bgr-cool" id="bgr-technologie">
        <div class="bgr-cool-bg"></div>
        <div class="bgr-cool-overlay"></div>
        <div class="container">
            <div class="bgr-cool-content" style="max-width: 980px;" data-aos="fade-up">
                <span class="bgr-hero-badge"><i class="bi bi-thermometer-snow"></i> Technologie douce</span>
                <h2 class="bgr-h2" style="color:#fff;">Une technologie basse température qui préserve les principes actifs</h2>

                <div class="bgr-temp">
                    <i class="bi bi-thermometer-half"></i>
                    <div>
                        <div class="bgr-temp-value">40 – 60°C</div>
                        <div class="bgr-temp-label">Températures de fonctionnement réduites</div>
                    </div>
                </div>

                <p class="bgr-text">
                    Le système de ligne fonctionne à des températures réduites (40–60°C) pour préserver les
                    principes actifs pharmaceutiques thermosensibles (composés bioactifs) ciblés pour développer des
                    phytomédicaments sûrs et efficaces, des nutraceutiques, des aliments santé fortifiés riches en
                    nutriments et des engrais organiques riches en nutriments.
                </p>

                <div class="bgr-chips" style="margin-top: 12px;">
                    <span class="bgr-chip" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.25);color:#fff;"><i class="bi bi-capsule"></i> Phytomédicaments sûrs et efficaces</span>
                    <span class="bgr-chip" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.25);color:#fff;"><i class="bi bi-heart-pulse"></i> Nutraceutiques</span>
                    <span class="bgr-chip" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.25);color:#fff;"><i class="bi bi-basket2"></i> Aliments santé fortifiés</span>
                    <span class="bgr-chip" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.25);color:#fff;"><i class="bi bi-tree"></i> Engrais organiques</span>
                </div>

                <div class="bgr-callout" style="background:rgba(255,255,255,.08);border-left-color:var(--bgr-gold);margin-top:34px;">
                    <i class="bi bi-stars" style="color:var(--bgr-gold);"></i>
                    <p style="color:#fff;">
                        Une technologie de pointe sans précédent dans le secteur des MTCAs / phytomédecine.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 6 : CTA -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-section bg-white" id="bgr-cta">
        <div class="container">
            <div class="bgr-cta" data-aos="fade-up">
                <span class="bgr-hero-badge">Rejoignez-nous</span>
                <h2>Construisons ensemble l'avenir de la phytomédecine industrielle en Afrique.</h2>
                <p>
                    Investissez dans une plateforme industrielle d'envergure internationale, devenez courtier
                    contractuel, ou découvrez nos produits de santé naturelle d'exception.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= base_url('About/presentation') ?>" class="bgr-btn bgr-btn-gold">
                        <i class="bi bi-bullseye"></i> Découvrir la présentation
                    </a>
                    <a href="<?= base_url('Investors') ?>" class="bgr-btn bgr-btn-ghost">
                        <i class="bi bi-graph-up-arrow"></i> Investir
                    </a>
                    <a href="<?= base_url('Home/Contact') ?>" class="bgr-btn bgr-btn-ghost">
                        <i class="bi bi-envelope"></i> Nous contacter
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION PARTENAIRES STRATÉGIQUES & ACADÉMIQUES (SCROLL HORIZONTAL) -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <section class="bgr-section py-5" id="bgr-partenaires-scroll" style="background: #f8fafc; overflow: hidden;">
        <div class="container-fluid px-4">
            <div class="text-center mx-auto mb-4" style="max-width: 760px;" data-aos="fade-up">
                <span class="bgr-hero-badge"><i class="bi bi-handshake"></i> Réseau & Collaboration</span>
                <h2 style="color: #111; font-weight: 700;">Nos Partenaires Stratégiques & Académiques</h2>
                <p style="color: #555;">Nos alliances mondiales avec des universités, institutions et organismes de premier plan.</p>
            </div>

            <?php
            $CI =& get_instance();
            if (!isset($CI->Model)) { $CI->load->model('Model'); }
            $partenaires_list = $CI->Model->read('partenaires', ['est_actif' => 1, 'deleted_at' => NULL], 'id_partenaire', 'DESC');
            if (!empty($partenaires_list)):
            ?>
            <style>
                .partners-marquee-container {
                    position: relative;
                    width: 100%;
                    overflow: hidden;
                    white-space: nowrap;
                    padding: 20px 0;
                }
                .partners-marquee-track {
                    display: inline-flex;
                    gap: 25px;
                    animation: scrollPartners 35s linear infinite;
                }
                .partners-marquee-container:hover .partners-marquee-track {
                    animation-play-state: paused;
                }
                @keyframes scrollPartners {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .partner-card-item {
                    flex: 0 0 320px;
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 16px;
                    padding: 24px;
                    text-align: center;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
                    white-space: normal;
                    display: inline-flex;
                    flex-direction: column;
                    justify-content: space-between;
                    height: 280px;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                }
                .partner-card-item:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 25px rgba(11,93,59,0.1);
                    border-color: #0B5D3B;
                }
            </style>

            <div class="partners-marquee-container">
                <div class="partners-marquee-track">
                    <?php 
                    $loop_items = array_merge($partenaires_list, $partenaires_list);
                    foreach ($loop_items as $part): 
                    ?>
                    <div class="partner-card-item">
                        <div>
                            <div class="mb-2 d-flex align-items-center justify-content-center" style="height: 60px;">
                                <?php if (!empty($part['logo_url'])): ?>
                                    <img src="<?= base_url($part['logo_url']) ?>" alt="<?= htmlspecialchars($part['nom']) ?>" style="max-height: 50px; max-width: 120px; object-fit: contain;" onerror="this.src='<?= base_url('attachments/partenaires/default-logo.png') ?>'">
                                <?php else: ?>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 45px; height: 45px; background: #0B5D3B; font-size: 1rem;">
                                        <?= mb_substr($part['nom'], 0, 2) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="badge rounded-pill mb-2 px-2 py-1 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px; background: rgba(11,93,59,0.1); color: #0B5D3B;">
                                <?= htmlspecialchars($part['type_partenaire']) ?>
                            </span>
                            <h3 class="h6 fw-bold text-dark mb-1" style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($part['nom']) ?></h3>
                            <?php if (!empty($part['pays'])): ?>
                                <p class="text-muted small mb-2" style="font-size: 0.78rem;"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($part['pays']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($part['site_web'])): ?>
                            <div class="mt-2 pt-2 border-top w-100">
                                <a href="<?= htmlspecialchars($part['site_web']) ?>" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm w-100 rounded-pill fw-semibold py-1" style="font-size: 0.78rem; border-color: #0B5D3B; color: #0B5D3B;">
                                    <i class="bi bi-globe"></i> Visiter le site
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">Aucun partenaire répertorié pour le moment.</div>
            <?php endif; ?>
        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 700,
        easing: 'ease-out-cubic',
        once: true,
        offset: 60
    });
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
