<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== BROKER COMMISSION — STATIC VIEW ===================== -->
<style>
    .bkc-wrap{background:#fff}
    .bkc-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .bkc-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .bkc-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .bkc-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .bkc-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .bkc-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .bkc-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.4vw,2.5rem);line-height:1.25;margin:0 0 10px}
    .bkc-hero h1 .bkc-brace{color:#D4A017}
    .bkc-hero .bkc-lead{position:relative;z-index:2;font-size:.95rem;color:rgba(255,255,255,.85);margin:0;max-width:760px;line-height:1.8}
    .bkc-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    .bkc-body{padding:52px 0 70px}
    .bkc-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:22px;max-width:1000px}
    .bkc-card{position:relative;background:#fff;border:1px solid #E3EDE6;border-radius:14px;padding:30px 28px 26px;box-shadow:0 8px 24px rgba(8,61,42,.06);transition:.25s}
    .bkc-card:hover{transform:translateY(-4px);box-shadow:0 14px 34px rgba(8,61,42,.13);border-color:#c9dfd1}
    .bkc-card .bkc-ico{width:54px;height:54px;display:flex;align-items:center;justify-content:center;background:#083D2A;color:#D4A017;font-size:1.25rem;border-radius:14px;margin-bottom:18px;box-shadow:0 6px 16px rgba(8,61,42,.22)}
    .bkc-card h3{margin:0 0 10px;font-family:'Poppins',sans-serif;font-weight:600;font-size:1.02rem;color:#083D2A;line-height:1.4}
    .bkc-card p{margin:0;color:#3d4f45;font-size:.9rem;line-height:1.75}
    .bkc-card--accent{border-color:#D4A017;background:#FFFDF6}
    .bkc-card--accent .bkc-ico{background:#D4A017;color:#083D2A}
    @media(max-width:768px){.bkc-grid{grid-template-columns:1fr}}
</style>

<section class="bkc-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="bkc-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Paiement des commissions aux courtiers</span>
            </div>
            <h1>Paiement des commissions <span class="bkc-brace">{</span> aux courtiers</h1>
            <p class="bkc-lead">Chez NUFOTEC, les courtiers sont des partenaires stratégiques :</p>
            <div class="bkc-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="bkc-body">
        <div class="container">
            <div class="bkc-grid">

                <!-- 1 -->
                <div class="bkc-card">
                    <div class="bkc-ico"><i class="fa-solid fa-handshake"></i></div>
                    <h3>Partenaires stratégiques</h3>
                    <p>
                        Nous considérons les <strong>Courtiers en Introduction de Capitaux</strong> comme des partenaires
                        stratégiques et protégeons leurs intérêts commerciaux.
                    </p>
                </div>

                <!-- 2 -->
                <div class="bkc-card">
                    <div class="bkc-ico"><i class="fa-solid fa-file-signature"></i></div>
                    <h3>Accord de courtage scellé</h3>
                    <p>
                        Nous honorons et respectons strictement l'<strong>Accord de Courtage portant le sceau du Notaire
                        Public</strong> avec une intégrité et une transparence absolues.
                    </p>
                </div>

                <!-- 3 -->
                <div class="bkc-card bkc-card--accent">
                    <div class="bkc-ico"><i class="fa-solid fa-sack-dollar"></i></div>
                    <h3>Paiement dès décaissement</h3>
                    <p>
                        Les commissions convenues dans notre accord bilatéral sont payées dès que les fonds sont
                        <strong>obtenus / décaissés avec succès</strong>.
                    </p>
                </div>

                <!-- 4 -->
                <div class="bkc-card">
                    <div class="bkc-ico"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <h3>Aucune commission initiale</h3>
                    <p>
                        <strong>Aucune commission initiale n'est acceptée</strong> — la rémunération du courtier est
                        strictement liée à la réussite effective du financement.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
