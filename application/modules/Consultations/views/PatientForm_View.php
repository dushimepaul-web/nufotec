<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
/* ============================================================
   CSS FONCTIONNEL REQUIS (pas un habillage)
   Assuré par le JS de cette page : wizard (étapes), upload,
   autocomplétion, variables CSS utilisées en style inline/JS.
   ============================================================ */
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-muted: #6B9080;
    --accent: #C9A227;
    --accent-bg: #FDF8E8;
    --error: #C53030;
    --error-bg: #FFF5F5;
    --success: #2F855A;
    --success-bg: #F0FFF4;
    --border: #E2E8F0;
    --text-secondary: #4A5568;
    --text-muted: #718096;
    --shadow-md: 0 4px 6px rgba(0,0,0,0.05);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);

    /* Nouvelles variables de design */
    --p-green: #0f4c3a;
    --p-green-light: #1a6b52;
    --p-green-mint: #e8f5f0;
    --p-gold: #c9a227;
    --p-gold-light: #f6e9c3;
    --p-bg: #f4f8f6;
    --p-white: #ffffff;
    --p-text: #16332a;
    --p-muted: #6b7f77;
    --p-border: #e3ece8;
    --p-radius: 18px;
    --p-radius-sm: 12px;
}

.step-content.hidden { display: none !important; }

.progress-step {
    width: 36px;
    height: 36px;
    border: 2px solid var(--border);
    background: #fff;
    color: var(--text-muted);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: all .3s ease;
}
.progress-step.active {
    border-color: var(--primary);
    background: var(--primary);
    color: #fff;
}
.progress-step.completed {
    border-color: var(--success);
    background: var(--success);
    color: #fff;
}

.autocomplete-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: .5rem;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
    box-shadow: var(--shadow-md);
}
.autocomplete-item {
    padding: .625rem .75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.autocomplete-item:hover,
.autocomplete-item:focus {
    background: var(--accent-bg);
}

.upload-box {
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.upload-box:hover {
    border-color: var(--primary-muted) !important;
    background: var(--accent-bg);
}
.upload-box input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* ============================================================
   DESIGN PATIENTFORM — HERO / PROGRESSION / FORMULAIRE
   ============================================================ */
html, body {
    overflow-x: hidden;
}
body {
    background: var(--p-bg);
}
.consultation-hero {
    overflow-x: hidden;
}

.pg-page {
    background: var(--p-bg);
}

/* ---------- HERO ---------- */
.consultation-hero {
    position: relative;
    color: #fff;
    background: linear-gradient(135deg, #0b3d2e 0%, #1a6b52 60%, #2b8a6a 100%);
    overflow: hidden;
    padding: 26px 0 52px;
}
.consultation-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 12% 20%, rgba(255,255,255,.08), transparent 45%),
        radial-gradient(circle at 88% 15%, rgba(201,162,39,.18), transparent 40%),
        radial-gradient(circle at 75% 90%, rgba(255,255,255,.06), transparent 45%);
    pointer-events: none;
}
.consultation-hero .container { position: relative; z-index: 1; width: 100%; }
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem 1.2rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.28);
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    backdrop-filter: blur(4px);
    max-width: 100%;
    text-align: center;
}
.hero-badge i { color: #f6e9c3; }
.hero-title {
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 800;
    letter-spacing: -.01em;
    line-height: 1.12;
    margin: .8rem auto .5rem;
    text-transform: uppercase;
    overflow-wrap: break-word;
    word-break: break-word;
    max-width: 100%;
}
.hero-title .ht-l1,
.hero-title .ht-l2 { display: block; }
.hero-text {
    font-size: clamp(.9rem, 1.4vw, 1.02rem);
    line-height: 1.5;
    color: rgba(255,255,255,.93);
    max-width: 720px;
    margin: 0 auto;
}
.hero-privacy {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    margin-top: 1.3rem;
    padding: .45rem 1rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.22);
    font-size: .84rem;
    color: rgba(255,255,255,.95);
}
.hero-privacy i { color: #f6e9c3; }

/* ---------- Conteneur formulaire ---------- */
.pg-wrap {
    max-width: 1200px;
    padding-top: .5rem;
    padding-bottom: 3.5rem;
}

/* ---------- Progression 4 étapes ---------- */
.p-steps-card {
    max-width: 1150px;
    margin: -58px auto 1.75rem;
    background: var(--p-white);
    border: 1px solid var(--p-border);
    border-radius: 22px;
    box-shadow: 0 12px 32px rgba(15,76,58,.12);
    padding: 2rem 2rem 1.5rem;
    position: relative;
    z-index: 5;
}
.p-steps { display: flex; align-items: flex-start; }
.p-step {
    flex: 1;
    text-align: center;
    position: relative;
    min-width: 0;
}
.p-step::before {
    content: "";
    position: absolute;
    top: 21px;
    left: -50%;
    width: 100%;
    height: 3px;
    background: var(--p-border);
    z-index: 0;
    transition: background .3s ease;
}
.p-step:first-child::before { display: none; }
.p-step.is-active::before,
.p-step.is-completed::before {
    background: linear-gradient(90deg, var(--p-green), var(--p-green-light));
}
.p-step-head {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: center;
}
.p-step-num {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    font-weight: 700;
    background: #fff;
    border: 2px solid var(--p-border);
    color: var(--p-muted);
    transition: all .3s ease;
}
.p-step .progress-step {
    width: 42px;
    height: 42px;
    font-size: 1.05rem;
    background: #fff;
    box-shadow: 0 0 0 4px #fff;
}
.p-step .progress-step.active {
    background: var(--p-green);
    border-color: var(--p-green);
    box-shadow: 0 0 0 4px var(--p-green-mint), 0 4px 12px rgba(15,76,58,.25);
}
.p-step .progress-step.completed {
    background: var(--success);
    border-color: var(--success);
}
.p-step-title {
    display: block;
    margin-top: .6rem;
    font-size: .78rem;
    font-weight: 600;
    color: var(--p-muted);
    line-height: 1.15;
    transition: color .3s ease;
}
.p-step.is-active .p-step-title { color: var(--p-green); font-weight: 700; }
.p-step.is-completed .p-step-title { color: var(--success); }

/* Barre linéaire (complément) */
.p-linear-progress {
    margin-top: 1.35rem;
    height: 7px;
    border-radius: 99px;
    background: var(--p-border);
    overflow: hidden;
}
.p-linear-progress .progress-bar {
    background: linear-gradient(90deg, var(--p-green), var(--p-green-light));
    border-radius: 99px;
    transition: width .4s ease;
}

/* ---------- Carte formulaire ---------- */
.consultation-card {
    background: var(--p-white);
    border: 1px solid var(--p-border);
    border-radius: var(--p-radius);
    box-shadow: 0 10px 30px rgba(15,76,58,.07);
}

/* En-tête d'étape */
.p-step-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 1.25rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--p-border);
}
.p-step-header .p-step-ico {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: var(--p-green-mint);
    color: var(--p-green);
    flex-shrink: 0;
}
.p-step-header h5 {
    font-weight: 700;
    color: var(--p-text);
    margin: 0;
}
.p-step-header .p-step-sub { color: var(--p-muted); font-size: .9rem; margin: 0; }

/* Champs */
.p-field .form-label {
    font-weight: 600;
    color: var(--p-text);
    font-size: .92rem;
}
.p-field .input-group-text {
    background: var(--p-green-mint);
    border: 1px solid var(--p-border);
    border-right: 0;
    color: var(--p-green);
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}
.p-field .form-control,
.p-field .form-select {
    border: 1px solid var(--p-border);
    border-radius: 12px;
    padding: .72rem .9rem;
    min-height: 50px;
    font-size: .98rem;
    background: #fff;
    color: var(--p-text);
    transition: border-color .2s, box-shadow .2s;
}
.p-field .input-group .form-control { border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; }
.p-field .form-control:focus,
.p-field .form-select:focus {
    border-color: var(--p-green);
    box-shadow: 0 0 0 4px rgba(15,76,58,.12);
    outline: none;
}
.p-field .form-control.is-valid,
.p-field .form-select.is-valid { border-color: var(--success); }
.p-field .form-control.is-invalid,
.p-field .form-select.is-invalid { border-color: var(--error); }

.p-lock-note {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .82rem;
    color: var(--p-muted);
}
.p-lock-note i { color: var(--p-green); }

/* Radio carte (première consultation) */
.p-radio-card {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .8rem 1.1rem;
    border: 2px solid var(--p-border);
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
    color: var(--p-text);
    transition: all .2s;
    margin: 0;
}
.p-radio-card input { accent-color: var(--p-green); }
.p-radio-card:hover { border-color: var(--p-green-muted); }
.p-radio-card:has(input:checked) {
    border-color: var(--p-green);
    background: var(--p-green-mint);
    color: var(--p-green);
    box-shadow: 0 0 0 4px rgba(15,76,58,.08);
}

/* Upload design */
.p-upload {
    border: 2px dashed var(--p-green-muted);
    border-radius: 16px;
    padding: 2rem 1rem;
    background: var(--p-bg);
    text-align: center;
    transition: all .25s;
}
.p-upload:hover {
    border-color: var(--p-green);
    background: var(--p-green-mint);
}
.p-upload .p-upload-ico {
    width: 56px;
    height: 56px;
    margin: 0 auto .75rem;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    background: #fff;
    color: var(--p-green);
    border: 1px solid var(--p-border);
}
.p-upload .p-upload-main { font-weight: 600; color: var(--p-text); }
.p-upload .p-upload-formats {
    font-size: .8rem;
    color: var(--p-muted);
    margin-top: .35rem;
}
.p-upload-preview {
    background: var(--p-bg);
    border: 1px solid var(--p-border);
    border-radius: 12px;
    padding: .9rem;
    margin-top: .75rem;
    font-size: .88rem;
    color: var(--p-text);
}

/* Récapitulatif */
.p-summary-grid { display: grid; grid-template-columns: 1fr; gap: .9rem; }
.p-summary-block {
    background: var(--p-bg);
    border: 1px solid var(--p-border);
    border-radius: 14px;
    padding: 1rem 1.1rem;
}
.p-summary-block > .p-summary-title {
    font-weight: 700;
    color: var(--p-green);
    font-size: .9rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin-bottom: .7rem;
    display: flex;
    align-items: center;
    gap: .45rem;
}
.p-summary-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: .7rem; }
.p-summary-item { background: #fff; border: 1px solid var(--p-border); border-radius: 10px; padding: .7rem .85rem; }
.p-summary-item label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: var(--p-muted);
    display: block;
    margin-bottom: .2rem;
}
.p-summary-item .p-summary-val { font-weight: 600; color: var(--p-text); }
.p-edit-btn {
    border: 1px solid var(--p-border);
    background: #fff;
    color: var(--p-green);
    border-radius: 9px;
    padding: .35rem .7rem;
    font-size: .8rem;
    font-weight: 600;
}
.p-edit-btn:hover { background: var(--p-green-mint); border-color: var(--p-green); }

/* Total */
.p-total {
    background: var(--p-gold-light);
    border: 1px solid rgba(201,162,39,.3);
    border-radius: 14px;
    padding: 1rem 1.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.p-total .p-total-label { font-weight: 700; color: var(--p-text); display: flex; align-items: center; gap: .5rem; }
.p-total .price-value { font-size: 1.4rem; font-weight: 800; color: var(--p-green); }

/* Paiement */
.p-pay-help { color: var(--p-muted); font-size: .9rem; }
.p-pay-card {
    display: flex;
    align-items: center;
    gap: .9rem;
    border: 2px solid var(--p-border);
    border-radius: 14px;
    padding: 1rem;
    background: #fff;
    cursor: pointer;
    transition: all .2s;
}
.p-pay-card input { accent-color: var(--p-green); flex-shrink: 0; }
.p-pay-card:hover { border-color: var(--p-green-muted); }
.p-pay-card:has(input:checked) {
    border-color: var(--p-green);
    background: var(--p-green-mint);
    box-shadow: 0 0 0 4px rgba(15,76,58,.08);
}
.p-pay-ico {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    background: #fff;
    color: var(--p-green);
    border: 1px solid var(--p-border);
    flex-shrink: 0;
}
.p-pay-name { font-weight: 700; color: var(--p-text); }
.p-pay-details { font-size: .82rem; color: var(--p-muted); }

/* Confidentialité */
.p-confidential {
    background: var(--p-green-mint);
    border: 1px solid rgba(15,76,58,.18);
    border-radius: 14px;
    padding: 1.1rem 1.2rem;
    display: flex;
    align-items: flex-start;
    gap: .9rem;
}
.p-confidential .p-lock-ico {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #fff;
    color: var(--p-green);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.p-confidential .form-check-input {
    width: 20px;
    height: 20px;
    margin-top: .15rem;
    accent-color: var(--p-green);
}
.p-confidential .form-check-label { color: var(--p-text); font-size: .95rem; }
.p-confidential a { color: var(--p-green); font-weight: 600; }

/* Boutons nav */
.p-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-top: 1.75rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--p-border);
}
.p-btn {
    min-height: 50px;
    padding: .7rem 1.4rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    border: 0;
    transition: all .2s;
}
.p-btn-primary {
    background: linear-gradient(135deg, var(--p-green), var(--p-green-light));
    color: #fff;
    box-shadow: 0 6px 16px rgba(15,76,58,.25);
}
.p-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15,76,58,.3); color:#fff; }
.p-btn-primary:disabled { opacity: .6; transform: none; box-shadow: none; }
.p-btn-ghost {
    background: #fff;
    color: var(--p-green);
    border: 1.5px solid var(--p-green);
}
.p-btn-ghost:hover { background: var(--p-green-mint); color: var(--p-green); }
.p-btn-success {
    background: linear-gradient(135deg, var(--p-green), var(--p-green-light));
    color: #fff;
    box-shadow: 0 6px 16px rgba(15,76,58,.28);
}
.p-btn-success:hover { transform: translateY(-1px); color:#fff; }

/* Sidebar info */
.info-panel {
    background: var(--p-white);
    border: 1px solid var(--p-border);
    border-radius: var(--p-radius);
    box-shadow: 0 8px 24px rgba(15,76,58,.06);
    position: sticky;
    top: 1.25rem;
}
.doctor-info-card { border-radius: 14px; }
.how-step-num {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--p-gold-light);
    color: var(--p-gold);
    font-weight: 700;
    font-size: .9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ---------- Responsive ---------- */
@media (max-width: 991px) {
    .p-step-title { font-size: .72rem; }
    .p-step .progress-step { width: 38px; height: 38px; font-size: .95rem; }
    .p-step::before { top: 19px; }
    .info-panel { position: static; }
}
@media (max-width: 767px) {
    .p-summary-grid-2 { grid-template-columns: 1fr; }
    .p-pay-card { padding: .9rem; }
}
@media (max-width: 575px) {
    .consultation-hero { padding: 22px 0 50px; }
    .hero-badge { font-size: .66rem; padding: .45rem .9rem; }
    .hero-title { font-size: 1.25rem; line-height: 1.2; }
    .hero-title .ht-l1,
    .hero-title .ht-l2 { display: inline; }
    .hero-text { font-size: .85rem; }
    .hero-privacy { font-size: .78rem; padding: .4rem .8rem; }
    .pg-wrap { padding-top: 0; }
    .p-steps-card {
        max-width: calc(100% - 24px);
        margin: -42px auto 1.25rem;
        padding: 1.3rem .6rem 1rem;
        border-radius: 16px;
    }
    .p-step-title {
        font-size: .58rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        padding: 0 2px;
    }
    .p-step { min-width: 0; }
    .p-step .progress-step { width: 34px; height: 34px; font-size: .9rem; }
    .p-step::before { top: 17px; height: 2.5px; }
    .p-step-head { padding: 0 2px; }
    .p-linear-progress { margin-top: 1.1rem; }
    .consultation-card .card-body { padding: 1.25rem !important; }
    .p-step-header { gap: .7rem; }
    .p-step-header .p-step-ico { width: 46px; height: 46px; font-size: 1.25rem; }
    .p-nav { flex-direction: column-reverse; }
    .p-nav .p-btn { width: 100%; justify-content: center; min-height: 52px; }
    .p-total { flex-direction: column; align-items: flex-start; gap: .5rem; }
    .p-summary-grid-2 { gap: .6rem; }
    .p-field .form-control, .p-field .form-select { min-height: 48px; font-size: 1rem; }
    .p-radio-card, .p-pay-card { padding: .8rem; }
    .p-confidential { flex-direction: column; }
}
@media (max-width: 380px) {
    .hero-title { font-size: 1.15rem; }
    .p-step-title { font-size: .52rem; }
    .p-step .progress-step { width: 30px; height: 30px; font-size: .82rem; }
    .p-step::before { top: 15px; }
    .p-step-header .p-step-ico { width: 42px; height: 42px; font-size: 1.1rem; }
    .p-steps-card { padding: 1rem .2rem .8rem; }
}
</style>

<a href="#main-content" class="visually-hidden-focusable p-3 text-white text-decoration-none" style="background: var(--primary);"><i class="bi bi-skip-forward me-2"></i> Aller au contenu principal</a>

<!-- ==================== SECTION HERO ==================== -->
<section class="consultation-hero text-center text-white">
    <div class="container hero-inner">
        <h2 class="hero-title">
            <span class="ht-l1">Demande de</span>
            <span class="ht-l2">consultation en ligne</span>
        </h2>
        <p class="hero-text">Remplissez ce formulaire s&eacute;curis&eacute; et recevez l'avis d'un m&eacute;decin NUFOTEC directement sur WhatsApp.</p>
    </div>
</section>

<!-- ==================== FORMULAIRE PRINCIPAL ==================== -->
<div class="container pg-wrap" id="main-content">

    <!-- Barre de progression (4 étapes) -->
    <div class="p-steps-card"
         role="progressbar"
         aria-valuenow="25"
         aria-valuemin="0"
         aria-valuemax="100">
        <div class="p-steps">
            <div class="p-step" id="p-step-1">
                <div class="p-step-head"><span class="progress-step" id="step1-indicator">1</span></div>
                <span class="p-step-title">Informations</span>
            </div>
            <div class="p-step" id="p-step-2">
                <div class="p-step-head"><span class="progress-step" id="step2-indicator">2</span></div>
                <span class="p-step-title">Sympt&ocirc;mes</span>
            </div>
            <div class="p-step" id="p-step-3">
                <div class="p-step-head"><span class="progress-step" id="step3-indicator">3</span></div>
                <span class="p-step-title">Documents</span>
            </div>
            <div class="p-step" id="p-step-4">
                <div class="p-step-head"><span class="progress-step" id="step4-indicator">4</span></div>
                <span class="p-step-title">V&eacute;rification &amp; Paiement</span>
            </div>
        </div>
        <div class="p-linear-progress">
            <div class="progress-bar" id="progressBar" role="progressbar" style="width:25%;"></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Colonne principale -->
        <div class="col-lg-8">

            <!-- Carte formulaire -->
            <div class="consultation-card card border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form id="consultationForm"
                          action="<?= base_url('patient-form/create') ?>"
                          method="POST"
                          enctype="multipart/form-data"
                          novalidate>

                        <!-- Champs cachés pour les données du médecin -->
                        <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doctor['id'] ?? '') ?>">
                        <input type="hidden" name="doctor_uuid" value="<?= htmlspecialchars($doctor['uuid'] ?? '') ?>">
                        <input type="hidden" name="doctor_nom" value="<?= htmlspecialchars($doctor['nom'] ?? '') ?>">
                        <input type="hidden" name="doctor_specialite" value="<?= htmlspecialchars($doctor['specialite'] ?? '') ?>">
                        <input type="hidden" name="consultation_prix" value="<?= htmlspecialchars($doctor['honoraires_consultation'] ?? 50) ?>">
                        <input type="hidden" name="consultation_devise" value="<?= htmlspecialchars($doctor['currency'] ?? 'USD') ?>">

                        <!-- CSRF Token -->
                        <input type="hidden"
                               name="<?= $this->security->get_csrf_token_name() ?>"
                               value="<?= $this->security->get_csrf_hash() ?>">

                        <!-- ========== ÉTAPE 1 : INFORMATIONS ========== -->
                        <div class="step-content" id="step1">
                            <div class="p-step-header">
                                <div class="p-step-ico"><i class="bi bi-person-badge"></i></div>
                                <div>
                                    <h5>Vos informations personnelles</h5>
                                    <p class="p-step-sub">Veuillez remplir vos informations personnelles</p>
                                </div>
                            </div>

                            <div class="p-lock-note mb-4"><i class="bi bi-lock-fill"></i> Vos donn&eacute;es sont confidentielles</div>

                            <div class="row g-3">
                                <!-- Nom complet : pleine largeur -->
                                <div class="col-12">
                                    <div class="p-field">
                                        <label class="form-label" for="full_name">
                                            <i class="bi bi-person me-1"></i> Nom complet <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text"
                                                   class="form-control"
                                                   name="full_name"
                                                   id="full_name"
                                                   placeholder="Jean Dupont"
                                                   required
                                                   minlength="3"
                                                   maxlength="100"
                                                   value="<?= htmlspecialchars($this->session->userdata('fullname') ?: $this->session->userdata('fu') ?: '', ENT_QUOTES, 'UTF-8') ?>"
                                                   <?= ($this->session->userdata('fullname') || $this->session->userdata('fu')) ? 'readonly' : '' ?>>
                                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Veuillez entrer votre nom complet (minimum 3 caractères)</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pays + WhatsApp : 2 colonnes -->
                                <div class="col-md-6">
                                    <div class="p-field">
                                        <label class="form-label">
                                            <i class="bi bi-globe me-1"></i> Pays de r&eacute;sidence <span class="text-danger">*</span>
                                        </label>
                                        <div class="position-relative">
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                                <input type="text"
                                                       class="form-control"
                                                       id="country_search"
                                                       placeholder="Rechercher votre pays"
                                                       autocomplete="off"
                                                       required>
                                                <input type="hidden" name="country" id="selected_country" value="<?= set_value('country'); ?>">
                                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Veuillez s&eacute;lectionner votre pays</div>
                                            </div>
                                            <div id="autocomplete_list" class="autocomplete-results" style="display: none;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-field">
                                        <label class="form-label" for="whatsapppatient">
                                            <i class="bi bi-whatsapp me-1"></i> Num&eacute;ro WhatsApp <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                            <input type="tel"
                                                   class="form-control"
                                                   name="whatsapppatient"
                                                   id="whatsapppatient"
                                                   placeholder="+257 68 86 00 12"
                                                   required
                                                   minlength="8"
                                                   maxlength="20">
                                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Veuillez entrer votre num&eacute;ro WhatsApp</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Âge + Taille + Poids : 3 colonnes -->
                                <div class="col-md-4">
                                    <div class="p-field">
                                        <label class="form-label" for="age">
                                            <i class="bi bi-calendar me-1"></i> &Acirc;ge <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <input type="number"
                                                   class="form-control"
                                                   name="age"
                                                   id="age"
                                                   placeholder="Ex: 30"
                                                   required
                                                   min="1"
                                                   max="120"
                                                   value="<?= set_value('age'); ?>">
                                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> &Acirc;ge invalide (1-120 ans)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-field">
                                        <label class="form-label" for="height">
                                            <i class="bi bi-rulers me-1"></i> Taille (cm) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                                            <input type="number"
                                                   class="form-control"
                                                   name="height"
                                                   id="height"
                                                   placeholder="Ex: 170"
                                                   required
                                                   min="50"
                                                   max="250"
                                                   value="<?= set_value('height'); ?>">
                                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Taille invalide (50-250 cm)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-field">
                                        <label class="form-label" for="weight">
                                            <i class="bi bi-speedometer me-1"></i> Poids (kg) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-speedometer"></i></span>
                                            <input type="number"
                                                   class="form-control"
                                                   name="weight"
                                                   id="weight"
                                                   placeholder="Ex: 70"
                                                   required
                                                   min="1"
                                                   max="300"
                                                   step="0.1"
                                                   value="<?= set_value('weight'); ?>">
                                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Poids invalide (1-300 kg)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-nav">
                                <button type="button" class="p-btn p-btn-ghost" disabled><i class="bi bi-arrow-left me-1"></i> Pr&eacute;c&eacute;dent</button>
                                <button type="button" class="p-btn p-btn-primary" onclick="nextStep(1)">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 2 : SYMPTÔMES ========== -->
                        <div class="step-content hidden" id="step2">
                            <div class="p-step-header">
                                <div class="p-step-ico"><i class="bi bi-activity"></i></div>
                                <div>
                                    <h5>Informations sur vos sympt&ocirc;mes</h5>
                                    <p class="p-step-sub">D&eacute;crivez votre situation actuelle</p>
                                </div>
                            </div>

                            <div class="p-field mb-3">
                                <label class="form-label" for="symptoms">
                                    <i class="bi bi-chat-text me-1"></i> Sympt&ocirc;mes actuels <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control"
                                          name="symptoms"
                                          id="symptoms"
                                          rows="6"
                                          required
                                          minlength="20"
                                          placeholder="Décrivez vos symptômes : depuis quand, leur intensité, les facteurs qui les aggravent ou les soulagent..."><?= set_value('symptoms'); ?></textarea>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Veuillez d&eacute;crire vos sympt&ocirc;mes (minimum 20 caract&egrave;res)</div>
                                <small class="text-muted" id="symptomsCounter"></small>
                            </div>

                            <div class="p-field mb-4">
                                <label class="form-label" for="symptoms_duration">
                                    <i class="bi bi-clock-history me-1"></i> Depuis combien de temps avez-vous ces sympt&ocirc;mes ?
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <select class="form-select" name="symptoms_duration" id="symptoms_duration">
                                        <option value="">S&eacute;lectionnez une dur&eacute;e</option>
                                        <option value="24h" <?= set_select('symptoms_duration', '24h'); ?>>Moins de 24 heures</option>
                                        <option value="2-3j" <?= set_select('symptoms_duration', '2-3j'); ?>>2-3 jours</option>
                                        <option value="1sem" <?= set_select('symptoms_duration', '1sem'); ?>>1 semaine</option>
                                        <option value="2sem" <?= set_select('symptoms_duration', '2sem'); ?>>2 semaines</option>
                                        <option value="1mois" <?= set_select('symptoms_duration', '1mois'); ?>>Plus d'un mois</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold d-block">
                                    <i class="bi bi-question-circle me-1"></i> Est-ce votre premi&egrave;re consultation chez NUFOTEC ?
                                </label>
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <label class="p-radio-card form-check me-2 mb-0">
                                        <input class="form-check-input me-2" type="radio" name="previous_consultation" value="yes" <?= set_radio('previous_consultation', 'yes'); ?>>
                                        <span class="form-check-label"><i class="bi bi-check-lg me-1"></i> Oui</span>
                                    </label>
                                    <label class="p-radio-card form-check mb-0">
                                        <input class="form-check-input me-2" type="radio" name="previous_consultation" value="no" <?= set_radio('previous_consultation', 'no', true); ?>>
                                        <span class="form-check-label"><i class="bi bi-x-lg me-1"></i> Non</span>
                                    </label>
                                </div>
                            </div>

                            <div class="p-nav">
                                <button type="button" class="p-btn p-btn-ghost" onclick="prevStep(2)"><i class="bi bi-arrow-left me-1"></i> Pr&eacute;c&eacute;dent</button>
                                <button type="button" class="p-btn p-btn-primary" onclick="nextStep(2)">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 3 : DOCUMENTS ========== -->
                        <div class="step-content hidden" id="step3">
                            <div class="p-step-header">
                                <div class="p-step-ico"><i class="bi bi-file-earmark-medical"></i></div>
                                <div>
                                    <h5>T&eacute;l&eacute;chargez vos documents</h5>
                                    <p class="p-step-sub">Ajoutez les documents n&eacute;cessaires pour votre consultation</p>
                                </div>
                            </div>

                            <!-- Upload 1 : examens médicaux -->
                            <div class="p-upload-card mb-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="p-step-ico" style="width:42px;height:42px;font-size:1.2rem;"><i class="bi bi-clipboard-data"></i></div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Images des examens m&eacute;dicaux</h6>
                                        <small class="text-muted">R&eacute;sultats d'analyses, radios, &eacute;chographies, etc.</small>
                                    </div>
                                </div>
                                <div class="upload-box p-upload" tabindex="0">
                                    <div class="p-upload-ico"><i class="bi bi-cloud-arrow-up"></i></div>
                                    <div class="p-upload-main">Cliquez pour t&eacute;l&eacute;charger ou glissez-d&eacute;posez</div>
                                    <div class="p-upload-formats"><i class="bi bi-file-earmark me-1"></i> JPG, PNG, PDF &mdash; max 5 Mo</div>
                                    <input type="file" name="medical_docs[]" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewFiles(this, 'medical-preview')">
                                </div>
                                <div id="medical-preview" class="upload-preview p-upload-preview" style="display: none;"></div>
                            </div>

                            <!-- Upload 2 : ordonnance -->
                            <div class="p-upload-card mb-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="p-step-ico" style="width:42px;height:42px;font-size:1.2rem;"><i class="bi bi-prescription"></i></div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Ordonnance m&eacute;dicale</h6>
                                        <small class="text-muted">Votre ordonnance en cours</small>
                                    </div>
                                </div>
                                <div class="upload-box p-upload" tabindex="0">
                                    <div class="p-upload-ico"><i class="bi bi-cloud-arrow-up"></i></div>
                                    <div class="p-upload-main">Cliquez pour t&eacute;l&eacute;charger ou glissez-d&eacute;posez</div>
                                    <div class="p-upload-formats"><i class="bi bi-file-earmark me-1"></i> JPG, PNG, PDF &mdash; max 5 Mo</div>
                                    <input type="file" name="prescriptions[]" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewFiles(this, 'prescription-preview')">
                                </div>
                                <div id="prescription-preview" class="upload-preview p-upload-preview" style="display: none;"></div>
                            </div>

                            <div class="p-nav">
                                <button type="button" class="p-btn p-btn-ghost" onclick="prevStep(3)"><i class="bi bi-arrow-left me-1"></i> Pr&eacute;c&eacute;dent</button>
                                <button type="button" class="p-btn p-btn-primary" onclick="nextStep(3)">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 4 : VÉRIFICATION & PAIEMENT ========== -->
                        <div class="step-content hidden" id="step4">
                            <div class="p-step-header">
                                <div class="p-step-ico"><i class="bi bi-check-circle"></i></div>
                                <div>
                                    <h5>V&eacute;rifiez vos informations</h5>
                                    <p class="p-step-sub">V&eacute;rifiez toutes les informations avant de continuer</p>
                                </div>
                            </div>

                            <!-- Récapitulatif -->
                            <div class="p-summary-grid mb-4">
                                <div class="p-summary-block">
                                    <div class="p-summary-title">
                                        <i class="bi bi-person-lines-fill"></i> Informations personnelles
                                        <button type="button" class="p-edit-btn ms-auto" onclick="goToStep(1)"><i class="bi bi-pencil"></i> Modifier</button>
                                    </div>
                                    <div class="p-summary-grid-2">
                                        <div class="p-summary-item"><label><i class="bi bi-person me-1"></i> Nom complet</label><div class="p-summary-val" id="summary-name">-</div></div>
                                        <div class="p-summary-item"><label><i class="bi bi-whatsapp me-1"></i> WhatsApp</label><div class="p-summary-val" id="summary-whatsapp">-</div></div>
                                        <div class="p-summary-item"><label><i class="bi bi-globe me-1"></i> Pays</label><div class="p-summary-val" id="summary-country">-</div></div>
                                        <div class="p-summary-item"><label><i class="bi bi-calendar me-1"></i> &Acirc;ge</label><div class="p-summary-val" id="summary-age">-</div></div>
                                        <div class="p-summary-item"><label><i class="bi bi-rulers me-1"></i> Taille / Poids</label><div class="p-summary-val" id="summary-size">-</div></div>
                                    </div>
                                </div>

                                <div class="p-summary-block">
                                    <div class="p-summary-title">
                                        <i class="bi bi-activity"></i> Sympt&ocirc;mes
                                        <button type="button" class="p-edit-btn ms-auto" onclick="goToStep(2)"><i class="bi bi-pencil"></i> Modifier</button>
                                    </div>
                                    <div class="p-summary-item"><label><i class="bi bi-chat-text me-1"></i> Description</label><div class="p-summary-val" id="summary-symptoms" style="white-space: pre-wrap;">-</div></div>
                                    <div class="p-summary-item mt-2"><label><i class="bi bi-clock-history me-1"></i> Dur&eacute;e &middot; Premi&egrave;re consultation</label><div class="p-summary-val" id="summary-duration">-</div></div>
                                </div>

                                <div class="p-summary-block">
                                    <div class="p-summary-title"><i class="bi bi-file-earmark-medical"></i> Documents</div>
                                    <div class="p-summary-item"><label><i class="bi bi-upload me-1"></i> Fichiers t&eacute;l&eacute;charg&eacute;s</label><div class="p-summary-val" id="summary-documents">-</div></div>
                                </div>

                                <div class="p-total">
                                    <span class="p-total-label"><i class="bi bi-cash-stack"></i> Montant total &agrave; payer</span>
                                    <span class="price-value">
                                        <?php
                                            $prix_usd = (float)($doctor['honoraires_consultation'] ?? 50);
                                            $devise = $doctor['currency'] ?? 'USD';
                                            $equiv_bif = $doctor['USD_EUR_Equivalent_en_BIF'] ?? null;
                                            $prix_burundi = $doctor['prix_pour_residant_burundi'] ?? null;
                                        ?>
                                        <?= number_format($prix_usd, 2) ?> <?= htmlspecialchars($devise) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- INFO PAIEMENT -->
                            <div class="alert alert-info d-flex align-items-start gap-2 small mb-4" role="alert" aria-live="polite">
                                <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                                <span>
                                    <strong>Comment payer votre consultation :</strong>
                                    <ol class="mb-0 ps-3 mt-1">
                                        <li>Choisissez votre moyen de paiement ci-dessous.</li>
                                        <li>Payez le montant affich&eacute; : <strong><?= number_format($prix_usd, 2) ?> <?= htmlspecialchars($devise) ?> <?= $equiv_bif ? '(Équivalent USD/EUR : ' . htmlspecialchars($equiv_bif) . ')' : '' ?> (Prix Burundi : <?= htmlspecialchars($prix_burundi) ?>)</strong>.</li>
                                        <li>T&eacute;l&eacute;chargez votre preuve de paiement : capture d'&eacute;cran de l'op&eacute;ration ou <strong>PDF</strong> du re&ccedil;u.</li>
                                    </ol>
                                    <span class="d-block mt-2">Votre demande compl&egrave;te est envoy&eacute;e directement au m&eacute;decin <strong>sur WhatsApp</strong>.</span>
                                </span>
                            </div>

                            <!-- MODE DE PAIEMENT -->
                            <div class="p-payment-section mb-4">
                                <div class="p-summary-title" style="font-weight:700;color:var(--p-green);font-size:.95rem;"><i class="bi bi-wallet2 me-1"></i> Mode de paiement</div>
                                <p class="p-pay-help mb-3">
                                    Effectuez le paiement sur votre t&eacute;l&eacute;phone via le moyen que vous choisissez, puis joignez la preuve : capture d'&eacute;cran de l'op&eacute;ration ou re&ccedil;u en <strong>PDF</strong>.
                                </p>
                                <div class="row g-3" id="paymentOptions">
                                    <?php foreach ($mode_payements as $mode): ?>
                                    <div class="col-md-6">
                                        <label class="p-pay-card form-check w-100 mb-0">
                                            <input class="form-check-input mt-0 flex-shrink-0" type="radio" name="payment_method" value="<?= htmlspecialchars($mode['description']) ?>" required>
                                            <span class="p-pay-ico"><i class="bi bi-phone"></i></span>
                                            <span>
                                                <span class="p-pay-name d-block"><?= htmlspecialchars($mode['description']) ?></span>
                                                <?php if (!empty($mode['numero_compte']) || !empty($mode['nom_compte'])): ?>
                                                <span class="p-pay-details d-block">
                                                    <?= htmlspecialchars($mode['numero_compte'] ?? '') ?><?= (!empty($mode['numero_compte']) && !empty($mode['nom_compte'])) ? ' — ' : '' ?><?= htmlspecialchars($mode['nom_compte'] ?? '') ?>
                                                </span>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- PREUVE DE PAIEMENT -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="paymentProof">
                                    <i class="bi bi-camera me-1"></i> Preuve de paiement (capture d'&eacute;cran ou <strong>PDF</strong>) <span class="text-muted">(optionnel)</span>
                                </label>
                                <div class="upload-box p-upload" tabindex="0">
                                    <div class="p-upload-ico"><i class="bi bi-camera"></i></div>
                                    <div class="p-upload-main">Cliquez pour t&eacute;l&eacute;charger la preuve</div>
                                    <div class="p-upload-formats"><i class="bi bi-file-earmark me-1"></i> JPG, PNG, PDF &mdash; max 5 Mo</div>
                                    <input type="file" id="paymentProof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" onchange="previewFiles(this, 'proof-preview')">
                                </div>
                                <div id="proof-preview" class="upload-preview p-upload-preview" style="display: none;"></div>
                                <small class="text-muted d-block mt-2">Si vous payez maintenant, joignez ici la capture ou le <strong>PDF</strong> du re&ccedil;u. Sinon, envoyez-la au m&eacute;decin sur WhatsApp apr&egrave;s le paiement.</small>
                            </div>

                            <!-- CONFIDENTIALITÉ -->
                            <div class="p-confidential mb-3">
                                <div class="p-lock-ico"><i class="bi bi-shield-lock-fill"></i></div>
                                <div class="flex-grow-1">
                                    <div class="mb-2" style="font-size:.9rem;color:var(--p-text);">
                                        Vos informations sont trait&eacute;es de mani&egrave;re confidentielle et utilis&eacute;es uniquement pour le traitement de votre demande de consultation.
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="terms" id="terms" required class="form-check-input">
                                        <label class="form-check-label" for="terms">
                                            J'accepte la confidentialit&eacute; de mes donn&eacute;es et les <a href="<?= base_url('conditions'); ?>" target="_blank">conditions d'utilisation</a>.
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="p-nav">
                                <button type="button" class="p-btn p-btn-ghost" onclick="prevStep(4)"><i class="bi bi-arrow-left me-1"></i> Pr&eacute;c&eacute;dent</button>
                                <button type="submit" class="p-btn p-btn-success" id="submitBtn">
                                    Soumettre ma demande <i class="bi bi-check-lg ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar info -->
        <div class="col-lg-4">
            <div class="info-panel card border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div id="doctor-card-container">
                        <div class="doctor-info-card text-white text-center rounded-4 p-4 mb-3 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);">
                            <div class="doctor-avatar-container d-inline-block position-relative">
                                <img src="<?= base_url('attachments/Users/' . htmlspecialchars($doctor['photo'] ?? '')) ?>"
                                     alt="<?= htmlspecialchars($doctor['nom'] ?? 'Médecin') ?>"
                                     class="doctor-avatar rounded-circle border border-4 border-white shadow"
                                     style="width: 90px; height: 90px; object-fit: cover;"
                                     onerror="this.src='<?= base_url('assets/frontend/img/default-doctor.jpg') ?>'">
                                <span class="doctor-status position-absolute rounded-circle border border-3 border-white" style="width: 20px; height: 20px; background: var(--success); bottom: 5px; right: 5px;"></span>
                            </div>
                            <h5 class="doctor-name fw-bold mt-3 mb-1">
                                <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($doctor['prenom'] ?? '') . ' ' . htmlspecialchars($doctor['nom'] ?? 'Médecin') ?>
                            </h5>
                            <p class="doctor-specialty opacity-75 small mb-3">
                                <i class="bi bi-star-fill me-1" style="color: var(--accent);"></i> <?= htmlspecialchars($doctor['specialite'] ?? 'Médecin généraliste') ?>
                            </p>
                            <div class="doctor-price d-inline-flex flex-column align-items-center gap-2 rounded-3 p-3 w-100" style="background: rgba(255,255,255,0.2);">
                                <div class="price-value fs-3 fw-bold">
                                    <?= number_format($prix_usd ?? 50, 2) ?> <?= htmlspecialchars($doctor['currency'] ?? 'USD') ?>
                                </div>
                                <?php if (!empty($doctor['USD_EUR_Equivalent_en_BIF'])): ?>
                                <div class="equiv-price small" style="opacity:.9;">
                                    <i class="bi bi-currency-exchange me-1" style="color: var(--accent);"></i>
                                    Équivalent USD/EUR : <strong><?= htmlspecialchars($doctor['USD_EUR_Equivalent_en_BIF']) ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="burundi-price mt-3 p-2 rounded-3 text-start" style="background: rgba(255,255,255,0.2); border-left: 4px solid var(--accent); font-size: 0.9rem;">
                                <i class="bi bi-geo-alt-fill me-1" style="color: var(--accent);"></i>
                                <strong>Prix Burundi : <?= htmlspecialchars($doctor['prix_pour_residant_burundi'] ?? '—') ?></strong>
                            </div>
                            <?php if (!empty($doctor_count) && $doctor_count > 1): ?>
                            <a style="text-decoration: none;" href="javascript:void(0)" onclick="confirmChangeDoctor()" class="change-doctor-btn btn btn-sm btn-outline-light mt-3 rounded-pill">
                                <i class="bi bi-arrow-left me-1"></i> Changer de m&eacute;decin
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3"><i class="bi bi-question-circle me-1"></i> Comment &ccedil;a marche ?</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="how-step-num">1</span> <i class="bi bi-pencil-square me-1"></i> Remplissez le formulaire</li>
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="how-step-num">2</span> <i class="bi bi-chat-text me-1"></i> D&eacute;crivez vos sympt&ocirc;mes</li>
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="how-step-num">3</span> <i class="bi bi-upload me-1"></i> T&eacute;l&eacute;chargez vos documents</li>
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="how-step-num">4</span> <i class="bi bi-credit-card me-1"></i> Effectuez le paiement</li>
                        <li class="d-flex align-items-center gap-3 py-2"><span class="how-step-num">5</span> <i class="bi bi-person-check me-1"></i> Recevez la r&eacute;ponse du m&eacute;decin</li>
                    </ul>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">

                    <div class="mb-3">
                        <strong class="d-block mb-1">
                            <i class="bi bi-wallet2 me-1"></i> Moyens de paiement accept&eacute;s :
                        </strong>
                        <?php if(isset($mode_payements) && !empty($mode_payements)): ?>
                            <?php foreach($mode_payements as $mode): ?>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <i style="font-size: 24px;" class="bi <?php
                                    switch($mode['description']) {
                                        case 'Carte bancaire': echo 'bi-credit-card'; break;
                                        case 'PayPal': echo 'bi-paypal'; break;
                                        case 'Mobile Money': echo 'bi-phone'; break;
                                        case 'Virement': echo 'bi-bank'; break;
                                        default: echo 'bi-cash';
                                    }
                                ?>"></i>
                                <span class="fw-medium"><?= htmlspecialchars($mode['description']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <i class="bi bi-credit-card" style="font-size: 24px;"></i>
                                <span>Carte bancaire</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <i class="bi bi-phone" style="font-size: 24px;"></i>
                                <span>Mobile Money</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-shield-check"></i>
                        <small>Paiement s&eacute;curis&eacute; - Vos donn&eacute;es sont prot&eacute;g&eacute;es</small>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted"><i class="bi bi-headset me-1"></i> Besoin d'aide ?</small><br>
                        <a href="tel:+25779666439" class="d-inline-flex align-items-center gap-2 fw-semibold mt-1 text-decoration-none" style="color: var(--primary);">
                            <i class="bi bi-telephone"></i> <?= $this->Model->get_setting('contact_whatsapp', '+257 79 666 439') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal succès -->
<div class="modal fade" id="successModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-4 text-center">
            <div class="fs-1 text-success mb-2"><i class="bi bi-check-circle-fill"></i></div>
            <h5 class="modal-title fw-bold mb-2" id="modalTitle"><i class="bi bi-emoji-smile me-1"></i> Merci !</h5>
            <p class="text-muted mb-3"><i class="bi bi-envelope-check me-1"></i> Votre demande a &eacute;t&eacute; enregistr&eacute;e avec succ&egrave;s</p>

            <div class="tracking-box rounded-3 p-3 my-3 text-start" style="background: var(--accent-bg); border: 1px solid var(--border);">
                <label class="text-muted small text-uppercase d-block mb-1"><i class="bi bi-upc-scan me-1"></i> Votre num&eacute;ro de suivi</label>
                <div class="number fs-5 fw-bold" id="trackingNumber" style="color: var(--primary); font-family: monospace;"><i class="bi bi-hash"></i> -</div>
            </div>

            <button type="button" class="btn btn-success w-100" onclick="window.location.href='<?= base_url() ?>'">
                <i class="bi bi-house me-1"></i> Retour &agrave; l'accueil
            </button>
        </div>
    </div>
</div>

<?php
if (!isset($products)) {
    $products = array();
}
?>
<?php include VIEWPATH.'sections/Products_Section.php'; ?>

<script>
const CONFIG = {
    totalSteps: 4,
    currentStep: 1,
    formData: {}
};

function getStepIcon(step) {
    return String(step);
}

function confirmChangeDoctor() {
    if (confirm('Voulez-vous vraiment changer de médecin ? Toutes vos données non enregistrées seront perdues.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('swap-doctor') ?>';

        var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = csrfName;
        input.value = csrfHash;
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
}

const countries = <?= json_encode($pays ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

document.addEventListener('DOMContentLoaded', function() {
    initializeAutocomplete();
    initializeFormValidation();

    const heroTitle = document.querySelector('.consultation-hero h1');
    if (heroTitle) {
        heroTitle.innerHTML = `<i class="bi bi-person-badge"></i> Consultation <?= htmlspecialchars(($doctor['prenom'] ?? '') . ' ' . ($doctor['nom'] ?? ''), ENT_QUOTES) ?>`;
    }

    // Compteur de caractères symptômes
    const symptoms = document.getElementById('symptoms');
    if (symptoms) {
        const counter = document.getElementById('symptomsCounter');
        const updateCount = function() {
            if (counter) counter.textContent = (symptoms.value || '').length + ' caract&egrave;res (minimum 20)';
        };
        symptoms.addEventListener('input', updateCount);
        updateCount();
    }
});

function goToStep(step) {
    CONFIG.currentStep = step;
    updateProgress();
    document.querySelector('.consultation-card').scrollIntoView({ behavior: 'smooth' });
}

function initializeAutocomplete() {
    const searchInput = document.getElementById('country_search');
    const resultsList = document.getElementById('autocomplete_list');
    const hiddenCountryInput = document.getElementById('selected_country');

    if (!searchInput || !resultsList) return;

    if (hiddenCountryInput.value) {
        const selected = countries.find(c => c.pays === hiddenCountryInput.value);
        if (selected) {
            searchInput.value = selected.pays;
        }
    }

    function renderCountries(query) {
        resultsList.innerHTML = '';
        const val = (query || '').toLowerCase().trim();
        let filtered;
        if (!val) {
            filtered = countries.filter(c => c && c.pays).slice(0, 100);
        } else {
            filtered = countries.filter(c =>
                c.pays && c.pays.toLowerCase().includes(val)
            ).slice(0, 100);
        }
        if (filtered.length > 0) {
            filtered.forEach(c => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.innerHTML = `<i class="bi bi-geo-alt"></i> <strong>${escapeHtml(c.pays)}</strong>`;
                div.onclick = function() {
                    selectCountry(c, searchInput, hiddenCountryInput, resultsList);
                };
                resultsList.appendChild(div);
            });
            resultsList.style.display = 'block';
        } else {
            resultsList.style.display = 'none';
        }
    }

    searchInput.addEventListener('focus', function() {
        renderCountries('');
    });
    searchInput.addEventListener('click', function() {
        renderCountries(searchInput.value);
    });
    searchInput.addEventListener('input', function() {
        renderCountries(searchInput.value);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
            resultsList.style.display = 'none';
        }
    });
}

function selectCountry(country, searchInput, hiddenInput, resultsList) {
    searchInput.value = country.pays;
    hiddenInput.value = country.pays;
    resultsList.style.display = 'none';
    searchInput.classList.remove('is-invalid');
    searchInput.classList.add('is-valid');
    refreshCompletion();
}

function initializeFormValidation() {
    updateProgress();
    loadSavedData();
    refreshCompletion();

    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });

    document.querySelectorAll('.upload-box').forEach(box => {
        box.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.querySelector('input[type="file"]').click();
            }
        });
    });

    const form = document.getElementById('consultationForm');
    if (form) {
        form.addEventListener('input', refreshCompletion);
        form.addEventListener('change', refreshCompletion);
    }
}

function validateField() {
    if (this.checkValidity && this.checkValidity() && this.value.trim()) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else if (this.value.trim()) {
        this.classList.remove('is-valid');
    }
}

function isStepFilled(step) {
    const panel = document.getElementById(`step${step}`);
    if (!panel) return false;
    const req = panel.querySelectorAll('[required]');
    let ok = true;
    req.forEach(f => {
        if (f.type === 'checkbox') { if (!f.checked) ok = false; }
        else if (f.type === 'file') { /* optionnel */ }
        else { if (!(f.value || '').trim()) ok = false; }
    });
    if (step === 1) {
        const c = document.getElementById('selected_country');
        if (!c || !c.value) ok = false;
    }
    if (step === 2) {
        const s = document.getElementById('symptoms');
        if (!s || (s.value || '').length < 20) ok = false;
    }
    return ok;
}

function refreshCompletion() {
    let filled = 0;
    for (let i = 1; i <= CONFIG.totalSteps; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        const wrap = document.getElementById(`p-step-${i}`);
        const done = isStepFilled(i);
        if (done) filled++;
        if (i < CONFIG.currentStep) {
            if (indicator) { indicator.className = 'progress-step completed'; indicator.innerHTML = '<i class="bi bi-check-lg"></i>'; }
            if (wrap) wrap.className = 'p-step is-completed';
        } else if (i === CONFIG.currentStep) {
            if (done) {
                if (indicator) { indicator.className = 'progress-step completed'; indicator.innerHTML = '<i class="bi bi-check-lg"></i>'; }
                if (wrap) wrap.className = 'p-step is-completed';
            } else {
                if (indicator) { indicator.className = 'progress-step active'; indicator.innerHTML = getStepIcon(i); }
                if (wrap) wrap.className = 'p-step is-active';
            }
        } else {
            if (indicator) { indicator.className = 'progress-step'; indicator.innerHTML = getStepIcon(i); }
            if (wrap) wrap.className = 'p-step';
        }
    }
    const bar = document.getElementById('progressBar');
    if (bar) bar.style.width = Math.round((filled / CONFIG.totalSteps) * 100) + '%';
}

function updateProgress() {
    for (let i = 1; i <= CONFIG.totalSteps; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        const panel = document.getElementById(`step${i}`);
        const wrap = document.getElementById(`p-step-${i}`);

        if (!indicator || !panel) continue;

        if (i < CONFIG.currentStep) {
            indicator.className = 'progress-step completed';
            indicator.innerHTML = '<i class="bi bi-check-lg"></i>';
            if (wrap) wrap.className = 'p-step is-completed';
        } else if (i === CONFIG.currentStep) {
            indicator.className = 'progress-step active';
            indicator.innerHTML = getStepIcon(i);
            if (wrap) wrap.className = 'p-step is-active';
        } else {
            indicator.className = 'progress-step';
            indicator.innerHTML = getStepIcon(i);
            if (wrap) wrap.className = 'p-step';
        }

        panel.classList.toggle('hidden', i !== CONFIG.currentStep);
    }

    const progress = (CONFIG.currentStep / CONFIG.totalSteps) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
}

function nextStep(step) {
    if (!validateStep(step)) {
        showNotification('Veuillez remplir correctement tous les champs obligatoires', 'error');
        return;
    }

    saveStepData(step);

    if (CONFIG.currentStep < CONFIG.totalSteps) {
        CONFIG.currentStep++;
        updateProgress();

        if (CONFIG.currentStep === CONFIG.totalSteps) {
            updateSummary();
        }

        document.querySelector('.consultation-card').scrollIntoView({ behavior: 'smooth' });
    }
}

function prevStep(step) {
    if (CONFIG.currentStep > 1) {
        CONFIG.currentStep--;
        updateProgress();
        document.querySelector('.consultation-card').scrollIntoView({ behavior: 'smooth' });
    }
}

function validateStep(step) {
    let isValid = true;
    const currentPanel = document.getElementById(`step${step}`);

    if (!currentPanel) return false;

    const requiredFields = currentPanel.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (field.type === 'checkbox') {
            if (!field.checked) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        } else if (field.type === 'file') {
            // Champs optionnels, pas de validation stricte
        } else {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        }
    });

    switch(step) {
        case 1:
            const age = document.getElementById('age');
            if (age && age.value && (age.value < 1 || age.value > 120)) {
                age.classList.add('is-invalid');
                isValid = false;
            }

            const weight = document.getElementById('weight');
            if (weight && weight.value && (weight.value < 1 || weight.value > 300)) {
                weight.classList.add('is-invalid');
                isValid = false;
            }

            const height = document.getElementById('height');
            if (height && height.value && (height.value < 50 || height.value > 250)) {
                height.classList.add('is-invalid');
                isValid = false;
            }

            const country = document.getElementById('selected_country');
            if (!country || !country.value) {
                document.getElementById('country_search').classList.add('is-invalid');
                isValid = false;
            }
            break;

        case 2:
            const symptoms = document.getElementById('symptoms');
            if (symptoms && symptoms.value.length < 20) {
                symptoms.classList.add('is-invalid');
                isValid = false;
            }
            break;

        case 3:
            // Documents optionnels
            break;
    }

    return isValid;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'error' ? 'var(--error-bg)' : 'var(--accent-bg)'};
        color: ${type === 'error' ? 'var(--error)' : 'var(--primary)'};
        border: 1px solid ${type === 'error' ? 'var(--error)' : 'var(--primary)'};
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;

    notification.innerHTML = `
        <i class="bi ${type === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'}"></i>
        <span>${escapeHtml(message)}</span>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function saveStepData(step) {
    switch(step) {
        case 1:
            CONFIG.formData.full_name = document.getElementById('full_name')?.value || '';
            CONFIG.formData.age = document.getElementById('age')?.value || '';
            CONFIG.formData.country = document.getElementById('selected_country')?.value || '';
            CONFIG.formData.weight = document.getElementById('weight')?.value || '';
            CONFIG.formData.height = document.getElementById('height')?.value || '';
            CONFIG.formData.whatsapppatient = document.getElementById('whatsapppatient')?.value || '';
            break;
        case 2:
            CONFIG.formData.symptoms = document.getElementById('symptoms')?.value || '';
            CONFIG.formData.symptoms_duration = document.getElementById('symptoms_duration')?.value || '';
            CONFIG.formData.previous_consultation = document.querySelector('input[name="previous_consultation"]:checked')?.value || '';
            break;
        case 3:
            break;
    }

    sessionStorage.setItem('consultation_form', JSON.stringify(CONFIG.formData));
}

function loadSavedData() {
    const saved = sessionStorage.getItem('consultation_form');
    if (saved) {
        try {
            CONFIG.formData = JSON.parse(saved);

            if (CONFIG.formData.full_name) document.getElementById('full_name').value = CONFIG.formData.full_name;
            if (CONFIG.formData.age) document.getElementById('age').value = CONFIG.formData.age;
            if (CONFIG.formData.symptoms) document.getElementById('symptoms').value = CONFIG.formData.symptoms;
            if (CONFIG.formData.weight) document.getElementById('weight').value = CONFIG.formData.weight;
            if (CONFIG.formData.height) document.getElementById('height').value = CONFIG.formData.height;
            if (CONFIG.formData.whatsapppatient) document.getElementById('whatsapppatient').value = CONFIG.formData.whatsapppatient;
        } catch(e) {
            console.error('Erreur chargement données sauvegardées:', e);
        }
    }
}

function updateSummary() {
    document.getElementById('summary-name').textContent = CONFIG.formData.full_name || '-';
    document.getElementById('summary-age').textContent = CONFIG.formData.age ? CONFIG.formData.age + ' ans' : '-';
    document.getElementById('summary-country').textContent = CONFIG.formData.country || '-';
    document.getElementById('summary-whatsapp').textContent = CONFIG.formData.whatsapppatient || '-';
    document.getElementById('summary-size').textContent =
        (CONFIG.formData.weight || '?') + ' kg / ' + (CONFIG.formData.height || '?') + ' cm';
    document.getElementById('summary-symptoms').textContent = CONFIG.formData.symptoms || '-';

    const durationVal = CONFIG.formData.symptoms_duration || 'Non pr&eacute;cis&eacute;e';
    const prevVal = CONFIG.formData.previous_consultation === 'yes' ? 'Oui' : 'Non';
    document.getElementById('summary-duration').textContent = durationVal + ' &middot; Premi&egrave;re consultation : ' + prevVal;

    const medCount = (document.querySelector('input[name="medical_docs[]"]')?.files?.length || 0);
    const prescCount = (document.querySelector('input[name="prescriptions[]"]')?.files?.length || 0);
    const total = medCount + prescCount;
    document.getElementById('summary-documents').textContent = total > 0 ? total + ' fichier(s) t&eacute;l&eacute;charg&eacute;(s)' : 'Aucun document';
}

function previewFiles(input, previewId) {
    const preview = document.getElementById(previewId);
    const files = Array.from(input.files);

    if (!preview) return;

    if (files.length === 0) {
        preview.style.display = 'none';
        return;
    }

    preview.style.display = 'block';
    preview.innerHTML = '<div style="display: flex; flex-direction: column; gap: 8px;">' +
        '<small style="font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 5px;"><i class="bi bi-check-circle"></i> ' + files.length + ' fichier(s) s&eacute;lectionn&eacute;(s) :</small>' +
        files.map(f => '<div style="font-size: 0.875rem; padding: 4px 0; display: flex; align-items: center; gap: 8px;"><i class="bi bi-file-earmark"></i> ' + escapeHtml(f.name) + ' (' + (f.size/1024).toFixed(1) + ' Ko)</div>').join('') +
        '</div>';

    input.parentElement.style.borderColor = '';
    if (typeof updateSummary === 'function') updateSummary();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('consultationForm').addEventListener('submit', function(e) {
    const terms = document.getElementById('terms');

    if (!terms.checked) {
        e.preventDefault();
        showNotification('Veuillez accepter la confidentialit&eacute; et les conditions', 'error');
        terms.focus();
        return false;
    }

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethod) {
        e.preventDefault();
        showNotification('Veuillez s&eacute;lectionner votre mode de paiement', 'error');
        return false;
    }

    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Envoi en cours...';
    }

    sessionStorage.removeItem('consultation_form');
});

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('successModal');
    if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
        window.location.href = '<?= base_url(); ?>';
    }
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
