<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== MARKET & INDUSTRY OUTLOOK — STATIC VIEW ===================== -->
<style>
    .mko-wrap{background:#fff}
    .mko-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .mko-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .mko-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .mko-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .mko-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .mko-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .mko-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.4vw,2.5rem);line-height:1.25;margin:0 0 10px}
    .mko-hero h1 .mko-brace{color:#D4A017}
    .mko-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    .mko-body{padding:52px 0 70px}
    .mko-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    .mko-card{position:relative;background:#fff;border:1px solid #E3EDE6;border-radius:14px;padding:32px 26px 26px;box-shadow:0 8px 24px rgba(8,61,42,.06);transition:.25s}
    .mko-card:hover{transform:translateY(-4px);box-shadow:0 14px 34px rgba(8,61,42,.13);border-color:#c9dfd1}
    .mko-card .mko-ico{width:56px;height:56px;display:flex;align-items:center;justify-content:center;background:#083D2A;color:#D4A017;font-size:1.35rem;border-radius:14px;margin-bottom:18px;box-shadow:0 6px 16px rgba(8,61,42,.22)}
    .mko-card h3{margin:0 0 12px;font-family:'Poppins',sans-serif;font-weight:600;font-size:1.1rem;color:#083D2A;line-height:1.4}
    .mko-card p{margin:0;color:#3d4f45;font-size:.92rem;line-height:1.8}
    .mko-card p + p{margin-top:10px;padding-top:10px;border-top:1px dashed #E3EDE6}
    .mko-tag{display:inline-block;background:#FBF7EA;color:#9a7a10;border:1px solid #EADFae;font-size:.72rem;font-weight:600;letter-spacing:.6px;padding:4px 12px;border-radius:50px;margin-bottom:14px}
    @media(max-width:992px){.mko-grid{grid-template-columns:1fr}}
</style>

<section class="mko-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="mko-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Aperçu du marché</span>
            </div>
            <h1>Aperçu du marché <span class="mko-brace">{</span> du secteur</h1>
            <div class="mko-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="mko-body">
        <div class="container">
            <div class="mko-grid">

                <!-- Viabilité du marché domestique -->
                <div class="mko-card">
                    <div class="mko-ico"><i class="fa-solid fa-house-chimney-medical"></i></div>
                    <div class="mko-tag">MARCHÉ DOMESTIQUE</div>
                    <h3>Viabilité du marché domestique</h3>
                    <p>
                        La prévalence croissante des maladies non transmissibles et la sensibilisation accrue des
                        consommateurs à la santé préventive stimulent la demande de nutraceutiques, d'aliments
                        fonctionnels fortifiés et de phytomédicaments.
                    </p>
                    <p>
                        La production localisée soutient l'accessibilité financière et la substitution des importations.
                    </p>
                </div>

                <!-- Opportunité du marché régional -->
                <div class="mko-card">
                    <div class="mko-ico"><i class="fa-solid fa-globe"></i></div>
                    <div class="mko-tag">MARCHÉ RÉGIONAL</div>
                    <h3>Opportunité du marché régional</h3>
                    <p>
                        Les marchés du COMESA, de l'EAC et de la SADC présentent une demande évolutive pour les extraits
                        botaniques standardisés et les produits de santé naturels fondés sur des preuves.
                    </p>
                    <p>
                        Un nombre limité d'installations de production alignées sur les BPF fonctionne actuellement à
                        grande échelle.
                    </p>
                </div>

                <!-- Potentiel d'exportation international -->
                <div class="mko-card">
                    <div class="mko-ico"><i class="fa-solid fa-ship"></i></div>
                    <div class="mko-tag">EXPORTATION INTERNATIONALE</div>
                    <h3>Potentiel d'exportation international</h3>
                    <p>
                        La demande mondiale pour les extraits botaniques clean-label et les phytomédicaments
                        cliniquement validés ne cesse de croître.
                    </p>
                    <p>
                        La feuille de route structurée de certification de NUFOTEC (par ex. ISO 9001, ISO 22000,
                        conformité BPF) soutient l'accès à long terme aux marchés d'exportation réglementés.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
