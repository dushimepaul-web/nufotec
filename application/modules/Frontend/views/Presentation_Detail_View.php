<?php
// ================================================================
// DONNÉES (lecture seule — aucune modification de contrôleur ni de données)
// ================================================================
$pre_chiffres = $this->Model->read('chiffres_cles', null, 'ordre', 'ASC');$pre_chiffres_map = [];
if (!empty($pre_chiffres)) {
    foreach ($pre_chiffres as $pc) {
        $pre_chiffres_map[trim((string)$pc['etiquette'])] = $pc;
    }
}
$pre_chiffre = function ($key) use ($pre_chiffres_map) {
    return $pre_chiffres_map[$key] ?? null;
};
$pre_icone = function ($nom) {
    $nom = trim((string)$nom);
    return $nom ? 'bi bi-' . $nom : 'bi bi-gem';
};

$pre_impact = [];
foreach (['Out-growers', 'Farmland', 'Seed Capital', 'WhatsApp Groups', 'WhatsApp Participants'] as $cle) {
    $c = $pre_chiffre($cle);
    if ($c) $pre_impact[] = $c;
}
$pre_vision = [];
foreach (['Seed Capital', 'Farmland', 'Export Share', 'Direct Jobs'] as $cle) {
    $c = $pre_chiffre($cle);
    if ($c) $pre_vision[] = $c;
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="canonical" href="<?= base_url('About/presentation') ?>">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "<?= base_url() ?>" },
    { "@type": "ListItem", "position": 2, "name": "À propos", "item": "<?= base_url('About/presentation') ?>" },
    { "@type": "ListItem", "position": 3, "name": "Présentation", "item": "<?= base_url('About/presentation') ?>" }
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "NUFOTEC-PHYTOMED INDUSTRIES",
  "description": "Entreprise de biotechnologie agro-industrielle intégrée verticalement, dédiée à l'industrialisation de l'économie des plantes médicinales en Afrique.",
  "slogan": "Industrialisation de l'économie des plantes médicinales en Afrique"
}
</script>

<style>
/* ═════════════════════════════════════════════════════════════════
   PAGE PRÉSENTATION — NUFOTEC-PHYTOMED INDUSTRIES
   ═════════════════════════════════════════════════════════════════ */
.presentation-page,
.presentation-page * { font-family: 'Poppins', sans-serif; }

.presentation-page {
    --pre-green: #0B5D3B;
    --pre-green-dark: #083D2A;
    --pre-gold: #D4A017;
    --pre-gray: #F8F9FA;
    --pre-muted: #5F6B64;
    --pre-radius: 24px;
    --pre-shadow: 0 20px 45px rgba(8, 61, 42, 0.07);
    color: #1F2A25;
}

.presentation-page .pre-section { padding: 100px 0; }
.presentation-page .pre-section-gray { background: var(--pre-gray); }
.presentation-page .pre-container { max-width: 1320px; }
.presentation-page h1, .presentation-page h2, .presentation-page h3, .presentation-page h4 { color: var(--pre-green-dark); }

.presentation-page p { font-size: 1.125rem; line-height: 1.8; color: #3E4A44; }

/* Badge de section */
.pre-badge {
    display: inline-block;
    padding: 8px 22px;
    background: #EAF6EF;
    color: var(--pre-green);
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.6px;
    border-radius: 50px;
    margin-bottom: 20px;
}

.pre-h2 { font-size: clamp(2rem, 3.2vw, 2.625rem); font-weight: 700; line-height: 1.25; letter-spacing: -0.02em; margin-bottom: 20px; }
.pre-h3 { font-size: 1.75rem; font-weight: 700; }
.pre-lead { font-size: 1.25rem; color: #4A5852; }

/* Boutons */
.pre-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 15px 32px; border-radius: 50px;
    font-weight: 600; font-size: 1rem; text-decoration: none;
    transition: all .3s ease; border: 2px solid transparent;
}
.pre-btn-green { background: var(--pre-green); color: #fff; box-shadow: 0 10px 25px rgba(11,93,59,.25); }
.pre-btn-green:hover { background: var(--pre-green-dark); color: #fff; transform: translateY(-3px); }
.pre-btn-gold { background: var(--pre-gold); color: #fff; box-shadow: 0 10px 25px rgba(212,160,23,.3); }
.pre-btn-gold:hover { background: #b88a13; color: #fff; transform: translateY(-3px); }
.pre-btn-white { background: #fff; color: var(--pre-green); }
.pre-btn-white:hover { background: #EAF6EF; color: var(--pre-green-dark); transform: translateY(-3px); }
.pre-btn-ghost { border-color: rgba(255,255,255,.55); color: #fff; background: transparent; }
.pre-btn-ghost:hover { background: #fff; color: var(--pre-green); transform: translateY(-3px); }

/* Cartes */
.pre-card {
    background: #fff;
    border-radius: var(--pre-radius);
    border: 1px solid rgba(0,0,0,.05);
    box-shadow: var(--pre-shadow);
    transition: transform .35s ease, box-shadow .35s ease;
}
.pre-card:hover { transform: translateY(-6px); box-shadow: 0 28px 55px rgba(8,61,42,.12); }

/* ══════════════ HERO ══════════════ */
.pre-hero {
    position: relative;
    min-height: 560px;
    display: flex;
    align-items: center;
    background:
        linear-gradient(120deg, rgba(8,61,42,.94) 0%, rgba(11,93,59,.85) 55%, rgba(11,93,59,.65) 100%),
        url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
    padding: 120px 0;
}
.pre-hero-breadcrumb {
    display: inline-flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.22);
    backdrop-filter: blur(10px);
    padding: 10px 24px; border-radius: 50px;
    font-size: .85rem; letter-spacing: 1.4px; text-transform: uppercase; font-weight: 600;
    margin-bottom: 34px;
}
.pre-hero-breadcrumb a { color: rgba(255,255,255,.8); text-decoration: none; transition: color .3s; }
.pre-hero-breadcrumb a:hover { color: #fff; }
.pre-hero-breadcrumb .sep { color: var(--pre-gold); }
.pre-hero-breadcrumb .current { color: var(--pre-gold); }
.pre-hero h1 {
    color: #fff; font-size: clamp(2.5rem, 5.2vw, 3.375rem);
    font-weight: 800; letter-spacing: -0.02em; line-height: 1.15; margin-bottom: 22px;
}
.pre-hero .pre-hero-sub {
    color: rgba(255,255,255,.92); font-size: clamp(1.15rem, 2vw, 1.45rem);
    font-weight: 400; line-height: 1.7; max-width: 860px; margin: 0 auto 28px;
}
.pre-hero .pre-hero-text {
    color: rgba(255,255,255,.8); font-size: 1.05rem; line-height: 1.8;
    max-width: 760px; margin: 0 auto 40px;
}
.pre-hero-badge {
    display: inline-block;
    background: var(--pre-gold); color: #083D2A;
    font-size: .8rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 9px 26px; border-radius: 50px; margin-bottom: 26px;
}

/* ══════════════ SECTION 1 : QUI SOMMES-NOUS ══════════════ */
.pre-who-img { border-radius: var(--pre-radius); overflow: hidden; box-shadow: var(--pre-shadow); }
.pre-who-img img { width: 100%; height: 560px; object-fit: cover; display: block; }
.pre-who-sticky { position: sticky; top: 180px; }
.pre-who-callout {
    display: flex; align-items: center; gap: 16px;
    background: #EAF6EF; border-left: 5px solid var(--pre-green);
    border-radius: 18px; padding: 20px 26px; margin: 30px 0;
}
.pre-who-callout i { color: var(--pre-green); font-size: 1.6rem; }
.pre-who-callout p { margin: 0; font-weight: 700; color: var(--pre-green); font-size: 1.05rem; line-height: 1.6; }
.pre-brief-title { font-size: 1.45rem; font-weight: 800; color: var(--pre-dark); margin: 34px 0 8px; }
.pre-brief-intro { font-size: 1.05rem; color: #33403A; margin-bottom: 22px; }
.pre-brief-list { display: flex; flex-direction: column; gap: 14px; }
.pre-brief-item {
    display: flex; align-items: flex-start; gap: 16px;
    background: #F8FAF9; border: 1px solid #E3EBE6; border-radius: 16px;
    padding: 16px 20px; transition: border-color .3s ease, box-shadow .3s ease;
}
.pre-brief-item:hover { border-color: #BBD8C6; box-shadow: 0 8px 20px rgba(11, 93, 59, .08); }
.pre-brief-letter {
    flex-shrink: 0; width: 42px; height: 42px; border-radius: 12px;
    background: linear-gradient(135deg, var(--pre-green), #0E7A4E);
    color: #fff; font-weight: 800; font-size: 1.15rem;
    display: flex; align-items: center; justify-content: center;
}
.pre-brief-item p { margin: 2px 0 0; color: #33403A; line-height: 1.65; font-size: 1rem; }
.pre-brief-item strong { color: var(--pre-dark); }

/* ══════════════ SECTION 2 : DOMAINES D'ACTIVITÉ ══════════════ */
.pre-act-card { padding: 42px 34px; text-align: left; height: 100%; }
.pre-act-icon {
    width: 76px; height: 76px; border-radius: 22px;
    background: linear-gradient(135deg, #EAF6EF, #D8EFE3);
    color: var(--pre-green); font-size: 2rem;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 26px; transition: transform .35s ease;
}
.pre-act-card:hover .pre-act-icon { transform: scale(1.08) rotate(-4deg); }
.pre-act-card h3 { font-size: 1.4rem; margin-bottom: 12px; }
.pre-act-card p { font-size: 1rem; line-height: 1.75; margin-bottom: 0; }

/* ══════════════ SECTION 3 : VISION 2026-2031 ══════════════ */
.pre-vision-card {
    background: linear-gradient(135deg, #083D2A 0%, #0B5D3B 60%, #11694A 100%);
    border-radius: 30px;
    padding: clamp(40px, 6vw, 80px);
    position: relative; overflow: hidden;
}
.pre-vision-card::before {
    content: ''; position: absolute; top: -140px; right: -140px;
    width: 380px; height: 380px; border-radius: 50%;
    background: radial-gradient(circle, rgba(212,160,23,.22) 0%, transparent 70%);
}
.pre-vision-card::after {
    content: ''; position: absolute; bottom: -180px; left: -120px;
    width: 420px; height: 420px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 70%);
}
.pre-vision-title { color: #fff; font-size: clamp(1.9rem, 3vw, 2.6rem); font-weight: 700; margin-bottom: 14px; }
.pre-vision-sub { color: rgba(255,255,255,.8); font-size: 1.1rem; max-width: 720px; margin-bottom: 50px; line-height: 1.8; }
.pre-stat {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.16);
    backdrop-filter: blur(8px);
    border-radius: 22px; padding: 30px 26px; text-align: center;
    height: 100%; transition: transform .35s ease, background .35s ease;
}
.pre-stat:hover { transform: translateY(-6px); background: rgba(255,255,255,.13); }
.pre-stat-icon { color: var(--pre-gold); font-size: 2.1rem; margin-bottom: 14px; }
.pre-stat-value { color: #fff; font-size: clamp(1.9rem, 3vw, 2.5rem); font-weight: 800; line-height: 1.1; }
.pre-stat-unit { color: var(--pre-gold); font-weight: 600; font-size: 1rem; }
.pre-stat-label { color: rgba(255,255,255,.92); font-weight: 600; font-size: 1rem; margin: 8px 0 6px; }
.pre-stat-desc { color: rgba(255,255,255,.72); font-size: .9rem; line-height: 1.6; margin-bottom: 0; }

/* ══════════════ SECTION 4 : PROCESSUS (TIMELINE) ══════════════ */
.pre-timeline { position: relative; padding: 30px 0 10px; }
.pre-timeline-line {
    position: absolute; top: 64px; left: 6%; right: 6%;
    height: 3px;
    background: linear-gradient(90deg, transparent, rgba(11,93,59,.35) 10%, rgba(11,93,59,.35) 90%, transparent);
}
.pre-step { text-align: center; position: relative; }
.pre-step-dot {
    width: 88px; height: 88px; margin: 0 auto 22px;
    border-radius: 50%;
    background: #fff; border: 3px solid #EAF6EF;
    box-shadow: 0 12px 30px rgba(8,61,42,.1);
    color: var(--pre-green); font-size: 2rem;
    display: flex; align-items: center; justify-content: center;
    transition: all .35s ease; position: relative; z-index: 2;
}
.pre-step:hover .pre-step-dot { background: var(--pre-green); border-color: var(--pre-green); color: #fff; transform: translateY(-4px); }
.pre-step h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: 6px; }
.pre-step p { font-size: .85rem; color: #6B7771; margin-bottom: 0; line-height: 1.6; }

/* ══════════════ SECTION 5 : VALEURS ══════════════ */
.pre-value-card { padding: 40px 30px; text-align: center; height: 100%; }
.pre-value-icon {
    width: 84px; height: 84px; border-radius: 50%;
    background: #EAF6EF; color: var(--pre-green);
    font-size: 2.1rem; margin: 0 auto 24px;
    display: flex; align-items: center; justify-content: center;
    transition: all .35s ease;
}
.pre-value-card:hover .pre-value-icon { background: var(--pre-green); color: #fff; transform: rotate(8deg) scale(1.06); }
.pre-value-card h3 { font-size: 1.3rem; margin-bottom: 10px; }
.pre-value-card p { font-size: .98rem; margin-bottom: 0; }

/* ══════════════ SECTION 6 : IMPACT ══════════════ */
.pre-impact { background: linear-gradient(135deg, #083D2A 0%, #0B5D3B 100%); }
.pre-impact .pre-h2 { color: #fff; }
.pre-impact .pre-lead { color: rgba(255,255,255,.78); }
.pre-impact .pre-badge { background: rgba(212,160,23,.16); color: var(--pre-gold); }
.pre-impact-stat {
    background: #fff; border-radius: 24px;
    padding: 38px 26px; text-align: center; height: 100%;
    box-shadow: 0 18px 40px rgba(0,0,0,.18);
    transition: transform .35s ease;
}
.pre-impact-stat:hover { transform: translateY(-6px); }
.pre-impact-icon {
    width: 66px; height: 66px; border-radius: 20px;
    background: #EAF6EF; color: var(--pre-green);
    font-size: 1.7rem; margin: 0 auto 20px;
    display: flex; align-items: center; justify-content: center;
}
.pre-impact-value { color: var(--pre-green-dark); font-size: clamp(1.8rem, 2.8vw, 2.3rem); font-weight: 800; line-height: 1.1; }
.pre-impact-unit { color: var(--pre-gold); font-weight: 700; font-size: 1rem; }
.pre-impact-label { color: var(--pre-green-dark); font-weight: 600; font-size: 1.05rem; margin-top: 10px; margin-bottom: 6px; }
.pre-impact-desc { color: #6B7771; font-size: .9rem; line-height: 1.6; margin-bottom: 0; }

/* ══════════════ SECTION 7 : POURQUOI NUFOTEC ══════════════ */
.pre-why-img { border-radius: var(--pre-radius); overflow: hidden; box-shadow: var(--pre-shadow); }
.pre-why-img img { width: 100%; height: 620px; object-fit: cover; display: block; }
.pre-why-list { display: flex; flex-direction: column; gap: 24px; }
.pre-why-item { display: flex; gap: 18px; align-items: flex-start; }
.pre-why-icon {
    width: 54px; height: 54px; border-radius: 16px; flex-shrink: 0;
    background: #EAF6EF; color: var(--pre-green);
    font-size: 1.4rem; display: flex; align-items: center; justify-content: center;
    transition: all .3s ease;
}
.pre-why-item:hover .pre-why-icon { background: var(--pre-green); color: #fff; transform: translateY(-3px); }
.pre-why-item h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 5px; }
.pre-why-item p { font-size: .98rem; line-height: 1.7; margin-bottom: 0; }

/* ══════════════ SECTION 9 : CTA ══════════════ */
.pre-cta {
    position: relative;
    border-radius: 30px; overflow: hidden;
    background:
        linear-gradient(120deg, rgba(8,61,42,.97) 0%, rgba(11,93,59,.92) 100%),
        url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
    padding: clamp(60px, 8vw, 110px) clamp(24px, 6vw, 90px);
    text-align: center;
}
.pre-cta h2 { color: #fff; font-size: clamp(1.9rem, 3.4vw, 2.8rem); font-weight: 800; line-height: 1.25; margin-bottom: 24px; }
.pre-cta p { color: rgba(255,255,255,.82); font-size: 1.15rem; max-width: 760px; margin: 0 auto 42px; }

/* Responsive */
@media (max-width: 991.98px) {
    .presentation-page .pre-section { padding: 64px 0; }
    .pre-who-img img { height: 400px; }
    .pre-who-sticky { position: static; }
    .pre-why-img img { height: 420px; }
    .pre-timeline-line { display: none; }
    .pre-step { margin-bottom: 34px; }
}
@media (max-width: 575.98px) {
    .pre-hero { min-height: 480px; padding: 90px 0; }
    .pre-btn { width: 100%; justify-content: center; }
}
</style>

<main class="presentation-page">

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- HERO — PRÉSENTATION -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-hero" aria-label="Présentation de NUFOTEC-PHYTOMED INDUSTRIES">
    <div class="container pre-container text-center">
        <!-- Fil d'ariane -->
        <nav aria-label="Fil d'ariane" class="pre-hero-breadcrumb justify-content-center" data-aos="fade-down">
            <a href="<?= base_url() ?>">Accueil</a>
            <span class="sep">›</span>
            <a href="<?= base_url('About/presentation') ?>">À propos</a>
            <span class="sep">›</span>
            <span class="current">Présentation</span>
        </nav>

        <span class="pre-hero-badge" data-aos="fade-up">Présentation</span>

        <h1 data-aos="fade-up" data-aos-delay="100">NUFOTEC-PHYTOMED INDUSTRIES</h1>

        <p class="pre-hero-sub" data-aos="fade-up" data-aos-delay="200">
            Industrialisation de l'économie des plantes médicinales en Afrique
        </p>

        <p class="pre-hero-text" data-aos="fade-up" data-aos-delay="300">
            Entreprise de biotechnologie agro-industrielle intégrée verticalement, NUFOTEC transforme
            l'agriculture biologique africaine en produits de santé naturelle standardisés, de la plante
            au laboratoire, jusqu'au marché international.
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap" data-aos="fade-up" data-aos-delay="400">
            <a href="#pre-vision" class="pre-btn pre-btn-gold">
                Découvrir notre vision <i class="bi bi-arrow-right"></i>
            </a>
            <a href="<?= base_url('Home/Contact') ?>" class="pre-btn pre-btn-ghost">
                <i class="bi bi-envelope"></i> Nous contacter
            </a>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 1 : QUI SOMMES-NOUS ? -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section bg-white" id="pre-qui-sommes-nous">
    <div class="container pre-container">
        <div class="row align-items-start g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="pre-who-img pre-who-sticky">
                    <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1200&q=80"
                         alt="Laboratoire moderne NUFOTEC-PHYTOMED Industries"
                         onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80'">
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div style="max-width: 850px;">
                    <span class="pre-badge">Qui sommes-nous ?</span>
                    <h2 class="pre-h2">
                        NUFOTEC-PHYTOMED INDUSTRIES – Industrialisation de l'économie des plantes médicinales en Afrique
                    </h2>

                    <div class="pre-who-callout">
                        <i class="bi bi-megaphone"></i>
                        <p>INVESTISSEZ EN NOUS OU DEVENEZ NOTRE COURTIER CONTRACTUEL DÈS AUJOURD'HUI !?</p>
                    </div>

                    <p>
                        Avec plus de 40 millions USD de capital d'amorçage dans le cadre de la vision quinquennale
                        (Vision 2026-2031), NUFOTEC Limited est une entreprise de biotechnologie agro-industrielle
                        privée, intégrée verticalement, basée au Burundi, qui s'emploie régulièrement à transformer
                        l'agriculture biologique commerciale de plantes médicinales ciblées et sélectionnées, de
                        cultures fonctionnelles, de fruits et de ressources agricoles riches en nutriments en MTCAs
                        standardisées à base de plantes (Médecines Traditionnelles, Complémentaires et Alternatives),
                        en Nutraceutiques/Compléments Alimentaires, en produits alimentaires et boissons santé
                        fortifiés clean-label totalement exempts de sucres et de produits chimiques nocifs ajoutés,
                        en engrais organiques haute-nutrition ainsi qu'en formulations phyto-médicinales et
                        phyto-pharmaceutiques scientifiquement avancées soumises à des essais précliniques et
                        cliniques via notre laboratoire de recherche scientifique et notre installation d'élevage
                        d'animaux de laboratoire.
                    </p>

                    <p>
                        En intégrant l'agriculture biologique commerciale climato-intelligente, des réseaux structurés
                        d'agriculteurs sous contrat, le profilage de produits piloté par le laboratoire, la
                        standardisation, les essais précliniques et cliniques, et une infrastructure de transformation
                        scalable, NUFOTEC fait passer l'agriculture d'une production de matières premières à faible
                        marge à une agriculture industrielle et une fabrication à haute valeur ajoutée.
                    </p>

                    <p>
                        L'entreprise construit une plateforme de croissance conçue pour étendre la superficie cultivée,
                        augmenter la capacité de transformation, renforcer la pénétration des exportations régionales
                        et générer un emploi durable, en particulier pour les jeunes et les femmes, tout en
                        contribuant à la transformation économique du Burundi et au développement de la chaîne de
                        valeur de la santé naturelle.
                    </p>

                    <p class="fw-semibold" style="color: #0B5D3B;">
                        Nous visons un chiffre d'affaires annuel de plusieurs millions de dollars, une performance
                        EBITDA durable et une expansion des exportations scalable d'ici 2031 (Vision 2026-2031).
                    </p>

                    <h3 class="pre-brief-title">En bref :</h3>
                    <p class="pre-brief-intro">
                        L'installation de NUFOTEC fabrique les produits standard et conformes aux BPF suivants :
                    </p>

                    <div class="pre-brief-list">
                        <div class="pre-brief-item">
                            <span class="pre-brief-letter">A</span>
                            <p><strong>MTCAs</strong> (Médecines Traditionnelles, Complémentaires et Alternatives), ciblant les maladies aiguës et chroniques ;</p>
                        </div>
                        <div class="pre-brief-item">
                            <span class="pre-brief-letter">B</span>
                            <p><strong>Nutraceutiques/Compléments Alimentaires</strong> ciblant les maladies aiguës et chroniques ;</p>
                        </div>
                        <div class="pre-brief-item">
                            <span class="pre-brief-letter">C</span>
                            <p><strong>Produits alimentaires et boissons santé fortifiés clean-label</strong> pour la prévention et la lutte contre la malnutrition, en particulier chez les enfants, les femmes enceintes et les mères allaitantes ;</p>
                        </div>
                        <div class="pre-brief-item">
                            <span class="pre-brief-letter">D</span>
                            <p><strong>Engrais organiques haute-nutrition</strong> pour la régénération et l'enrichissement des sols ;</p>
                        </div>
                        <div class="pre-brief-item">
                            <span class="pre-brief-letter">E</span>
                            <p><strong>Phytomédicaments</strong>, ciblant les maladies aiguës et chroniques ;</p>
                        </div>
                        <div class="pre-brief-item">
                            <span class="pre-brief-letter">F</span>
                            <p><strong>Phytopharmaceutiques</strong>, ciblant les maladies aiguës et chroniques.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 2 : NOS DOMAINES D'ACTIVITÉ -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section pre-section-gray" id="pre-domaines">
    <div class="container pre-container">
        <div class="text-center mx-auto mb-5" style="max-width: 760px;" data-aos="fade-up">
            <span class="pre-badge">Domaines d'excellence</span>
            <h2 class="pre-h2">Nos domaines d'activité</h2>
            <p class="pre-lead">Six pôles d'expertise complémentaires, de la plante médicinale au produit de santé scientifique.</p>
        </div>

        <div class="row g-4">
            <?php
            $pre_activites = [
                ['capsule', 'Médecines Traditionnelles', 'MTCAs standardisées à base de plantes, ciblant rigoureusement les maladies aiguës et chroniques.'],
                ['heart-pulse', 'Nutraceutiques', 'Compléments alimentaires scientifiques et naturels pour la prévention et la santé globale.'],
                ['cup-straw', 'Aliments Santé', 'Produits alimentaires et boissons fortifiés clean-label, exempts de sucres et de produits chimiques nocifs ajoutés.'],
                ['flower1', 'Engrais Organiques', 'Engrais organiques haute-nutrition pour la régénération et l\'enrichissement durable des sols.'],
                ['shield-plus', 'Phytomédicaments', 'Formulations phyto-pharmaceutiques avancées soumises à des essais précliniques et cliniques.'],
                ['clipboard2-pulse', 'Recherche Scientifique', 'Laboratoire de recherche et installation d\'élevage d\'animaux de laboratoire de pointe.'],
            ];
            $pre_delay = 0;
            foreach ($pre_activites as $pre_act): $pre_delay += 100;
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $pre_delay ?>">
                <div class="pre-card pre-act-card">
                    <div class="pre-act-icon"><i class="bi bi-<?= $pre_act[0] ?>"></i></div>
                    <h3><?= $pre_act[1] ?></h3>
                    <p><?= $pre_act[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 3 : NOTRE VISION 2026-2031 -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section bg-white" id="pre-vision">
    <div class="container pre-container">
        <div class="text-center mx-auto mb-5" style="max-width: 760px;" data-aos="fade-up">
            <span class="pre-badge">Vision 2026-2031</span>
            <h2 class="pre-h2">Une vision quinquennale ambitieuse</h2>
            <p class="pre-lead">Industrialiser la chaîne de valeur de la santé naturelle africaine avec une performance financière durable.</p>
        </div>

        <div class="pre-vision-card" data-aos="fade-up">
            <div class="position-relative" style="z-index: 2;">
                <div class="text-center mb-5">
                    <h3 class="pre-vision-title">La Vision 2026-2031 en chiffres</h3>
                    <p class="pre-vision-sub mx-auto">
                        De la graine au produit fini : un modèle intégré qui combine agriculture, recherche,
                        transformation industrielle et exportation.
                    </p>
                </div>

                <div class="row g-4">
                    <?php
                    $pre_vision_cards = [];
                    $pre_vision_map = [
                        'Seed Capital' => ['etiquette' => "Capital d'amorçage", 'unite' => 'M USD'],
                        'Farmland' => ['etiquette' => 'Plantations biologiques', 'unite' => 'hectares'],
                        'Export Share' => ['etiquette' => 'Exportation régionale', 'unite' => '%'],
                        'Direct Jobs' => ['etiquette' => "Création d'emplois", 'unite' => 'emplois'],
                    ];
                    foreach (['Seed Capital', 'Farmland', 'Export Share', 'Direct Jobs'] as $cle) {
                        $c = $pre_chiffre($cle);
                        if ($c) {
                            if (isset($pre_vision_map[$cle])) {
                                $c['etiquette'] = $pre_vision_map[$cle]['etiquette'];
                                $c['unite'] = $pre_vision_map[$cle]['unite'];
                            }
                            $pre_vision_cards[] = $c;
                        }
                    }
                    $pre_vision_cards[] = [
                        'icone' => 'flask', 'valeur' => 'GMP', 'unite' => '',
                        'etiquette' => 'Recherche scientifique',
                        'description' => 'Essais précliniques et cliniques pilotés par notre laboratoire scientifique interne.',
                    ];
                    $pre_vision_cards[] = [
                        'icone' => 'gear', 'valeur' => 'Scalable', 'unite' => '',
                        'etiquette' => 'Transformation industrielle',
                        'description' => 'Infrastructure de transformation scalable et fabrication à haute valeur ajoutée.',
                    ];

                    $pre_delay = 0;
                    foreach ($pre_vision_cards as $pre_v): $pre_delay += 100;
                        $pre_v_icone = isset($pre_v['icone']) && trim((string)$pre_v['icone']) !== '' ? trim((string)$pre_v['icone']) : 'gem';
                    ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $pre_delay ?>">
                        <div class="pre-stat">
                            <div class="pre-stat-icon"><i class="bi bi-<?= $pre_v_icone ?>"></i></div>
                            <div class="pre-stat-value">
                                <?= htmlspecialchars($pre_v['valeur']) ?>
                                <?php if (!empty($pre_v['unite'])): ?>
                                    <span class="pre-stat-unit"><?= htmlspecialchars($pre_v['unite']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="pre-stat-label"><?= htmlspecialchars($pre_v['etiquette']) ?></div>
                            <p class="pre-stat-desc"><?= htmlspecialchars($pre_v['description']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 4 : NOTRE PROCESSUS -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section pre-section-gray" id="pre-processus">
    <div class="container pre-container">
        <div class="text-center mx-auto mb-5" style="max-width: 760px;" data-aos="fade-up">
            <span class="pre-badge">Processus intégré</span>
            <h2 class="pre-h2">Notre chaîne de valeur</h2>
            <p class="pre-lead">Huit étapes maîtrisées, de la recherche fondamentale jusqu'à l'exportation des produits finis.</p>
        </div>

        <div class="pre-timeline" data-aos="fade-up">
            <div class="pre-timeline-line"></div>
            <div class="row">
                <?php
                $pre_etapes = [
                    ['search', 'Recherche', 'Plantes médicinales ciblées'],
                    ['tree', 'Culture', 'Agriculture biologique'],
                    ['basket', 'Récolte', 'Matières premières sélectionnées'],
                    ['gear', 'Transformation', 'Extraction & formulation'],
                    ['clipboard-check', 'Contrôle qualité', 'Standards GMP rigoureux'],
                    ['box-seam', 'Production', 'Fabrication industrielle'],
                    ['truck', 'Distribution', 'Réseaux régionaux'],
                    ['globe2', 'Exportation', 'Marchés internationaux'],
                ];
                $pre_delay = 0;
                foreach ($pre_etapes as $pre_etape): $pre_delay += 80;
                ?>
                <div class="col-lg-3 col-md-4 col-6 pre-step" data-aos="fade-up" data-aos-delay="<?= $pre_delay ?>">
                    <div class="pre-step-dot"><i class="bi bi-<?= $pre_etape[0] ?>"></i></div>
                    <h3><?= $pre_etape[1] ?></h3>
                    <p><?= $pre_etape[2] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 5 : NOS VALEURS -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section bg-white" id="pre-valeurs">
    <div class="container pre-container">
        <div class="text-center mx-auto mb-5" style="max-width: 760px;" data-aos="fade-up">
            <span class="pre-badge">Nos valeurs</span>
            <h2 class="pre-h2">Ce qui guide chacune de nos décisions</h2>
            <p class="pre-lead">Des principes forts qui structurent notre croissance et notre impact.</p>
        </div>

        <div class="row g-4">
            <?php
            $pre_valeurs = [
                ['lightbulb', 'Innovation', 'Pionniers dans l\'application des biotechnologies modernes à la pharmacopée africaine.'],
                ['award', 'Qualité', 'Standards de fabrication stricts, produits clean-label exempts de substances nocives.'],
                ['shield-check', 'Intégrité', 'Transparence totale envers nos investisseurs, partenaires, courtiers et clients.'],
                ['recycle', 'Durabilité', 'Agriculture climato-intelligente et régénération des sols pour les générations futures.'],
                ['flask', 'Recherche', 'Essais précliniques et cliniques rigoureux pilotés par notre laboratoire scientifique.'],
                ['people', 'Impact social', 'Emplois durables et autonomisation des jeunes et des femmes au Burundi.'],
            ];
            $pre_delay = 0;
            foreach ($pre_valeurs as $pre_val): $pre_delay += 100;
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $pre_delay ?>">
                <div class="pre-card pre-value-card">
                    <div class="pre-value-icon"><i class="bi bi-<?= $pre_val[0] ?>"></i></div>
                    <h3><?= $pre_val[1] ?></h3>
                    <p><?= $pre_val[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 6 : NOTRE IMPACT -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section pre-impact" id="pre-impact">
    <div class="container pre-container">
        <div class="text-center mx-auto mb-5" style="max-width: 760px;" data-aos="fade-up">
            <span class="pre-badge">Notre impact</span>
            <h2 class="pre-h2">Des résultats mesurables</h2>
            <p class="pre-lead">Un impact concret sur l'économie rurale, la santé naturelle et l'emploi.</p>
        </div>

        <div class="row g-4">
            <?php
            $pre_impact_extra = [
                'Out-growers' => 'Agriculteurs partenaires',
                'Farmland' => 'Surface cultivée',
                'Seed Capital' => 'Investissements',
                'WhatsApp Groups' => 'Groupes WhatsApp',
                'WhatsApp Participants' => 'Patients & communauté',
            ];
            foreach ($pre_impact as $pre_imp):
                $pre_lib = $pre_impact_extra[$pre_imp['etiquette']] ?? $pre_imp['etiquette'];
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="pre-impact-stat">
                    <div class="pre-impact-icon"><i class="<?= $pre_icone($pre_imp['icone']) ?>"></i></div>
                    <div class="pre-impact-value">
                        <?= htmlspecialchars($pre_imp['valeur']) ?>
                        <?php if (!empty($pre_imp['unite'])): ?>
                            <span class="pre-impact-unit"><?= htmlspecialchars($pre_imp['unite']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="pre-impact-label"><?= htmlspecialchars($pre_lib) ?></div>
                    <p class="pre-impact-desc"><?= htmlspecialchars($pre_imp['description']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="pre-impact-stat">
                    <div class="pre-impact-icon"><i class="bi bi-globe-africa"></i></div>
                    <div class="pre-impact-value">EAC<span class="pre-impact-unit"> + Afrique</span></div>
                    <div class="pre-impact-label">Pays ciblés</div>
                    <p class="pre-impact-desc">Pénétration des marchés régionaux et internationaux à forte valeur ajoutée.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 7 : POURQUOI CHOISIR NUFOTEC ? -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section bg-white" id="pre-pourquoi">
    <div class="container pre-container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="pre-why-img">
                    <img src="https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=1200&q=80"
                         alt="Équipe médicale et scientifique NUFOTEC"
                         onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80'">
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="pre-badge">Notre différence</span>
                <h2 class="pre-h2">Pourquoi choisir NUFOTEC ?</h2>

                <div class="pre-why-list mt-4">
                    <div class="pre-why-item">
                        <div class="pre-why-icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <h3>Normes ISO & GMP</h3>
                            <p>Des infrastructures de production aux standards internationaux pour la sécurité et l'efficacité des produits.</p>
                        </div>
                    </div>
                    <div class="pre-why-item">
                        <div class="pre-why-icon"><i class="bi bi-flask"></i></div>
                        <div>
                            <h3>Recherche scientifique intégrée</h3>
                            <p>Profilage de produits piloté par le laboratoire et essais précliniques et cliniques rigoureux.</p>
                        </div>
                    </div>
                    <div class="pre-why-item">
                        <div class="pre-why-icon"><i class="bi bi-leaf"></i></div>
                        <div>
                            <h3>Produits 100 % naturels clean-label</h3>
                            <p>Totalement exempts de sucres et de produits chimiques nocifs ajoutés.</p>
                        </div>
                    </div>
                    <div class="pre-why-item">
                        <div class="pre-why-icon"><i class="bi bi-sunrise"></i></div>
                        <div>
                            <h3>Agriculture climato-intelligente</h3>
                            <p>Des pratiques durables associées à des réseaux structurés d'agriculteurs sous contrat.</p>
                        </div>
                    </div>
                    <div class="pre-why-item">
                        <div class="pre-why-icon"><i class="bi bi-layers"></i></div>
                        <div>
                            <h3>Intégration verticale complète</h3>
                            <p>De la graine à l'exportation : chaque maillon de la chaîne de valeur est maîtrisé en interne.</p>
                        </div>
                    </div>
                    <div class="pre-why-item">
                        <div class="pre-why-icon"><i class="bi bi-people"></i></div>
                        <div>
                            <h3>Impact social et économique</h3>
                            <p>Création d'emplois durables et transformation économique du Burundi et de la région.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- SECTION 8 : APPEL À L'ACTION -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<section class="pre-section bg-white" id="pre-cta">
    <div class="container pre-container">
        <div class="pre-cta" data-aos="fade-up">
            <span class="pre-hero-badge">Rejoignez-nous</span>
            <h2>Construisons ensemble l'avenir de la santé naturelle en Afrique.</h2>
            <p>
                Investissez dans une vision d'envergure internationale, devenez courtier contractuel,
                ou découvrez dès aujourd'hui nos produits de santé naturelle d'exception.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= base_url('Products') ?>" class="pre-btn pre-btn-white">
                    <i class="bi bi-box-seam"></i> Découvrir les produits
                </a>
                <a href="<?= base_url('Investors') ?>" class="pre-btn pre-btn-gold">
                    <i class="bi bi-graph-up-arrow"></i> Investir
                </a>
                <a href="<?= base_url('Home/Contact') ?>" class="pre-btn pre-btn-ghost">
                    <i class="bi bi-envelope"></i> Nous contacter
                </a>
            </div>
        </div>
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

