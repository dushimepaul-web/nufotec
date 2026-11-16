<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
<!-- ===================== NUFOTEC-PHYTOMED FACILITY — STATIC VIEW ===================== -->
<style>
    .flp-wrap{background:#fff}
    .flp-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .flp-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .flp-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .flp-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .flp-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .flp-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .flp-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.4vw,2.5rem);line-height:1.25;margin:0 0 10px}
    .flp-hero h1 .flp-brace{color:#D4A017}
    .flp-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    .flp-body{padding:44px 0 70px}
    .flp-report-head{background:#F5F9F6;border:1px solid #E3EDE6;border-radius:12px;padding:24px 28px;margin:0 0 10px}
    .flp-report-head h2{margin:0 0 6px;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.05rem;color:#083D2A;line-height:1.45}
    .flp-report-head .flp-en{display:block;font-size:.8rem;font-weight:600;letter-spacing:.8px;color:#D4A017;text-transform:uppercase;margin-bottom:16px}
    .flp-legend{display:flex;flex-wrap:wrap;gap:10px}
    .flp-chip{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #DCE9E1;border-radius:50px;padding:6px 14px;font-size:.8rem;color:#3d4f45}
    .flp-chip b{background:#083D2A;color:#D4A017;font-size:.72rem;padding:2px 8px;border-radius:50px;letter-spacing:.5px}
    .flp-note{margin:14px 0 0;font-size:.82rem;font-style:italic;color:#6b7a70}
    .flp-table-wrap{margin-top:26px;background:#fff;border:1px solid #E3EDE6;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(8,61,42,.06)}
    .flp-scroll{overflow-x:auto}
    .flp-table{width:100%;border-collapse:collapse;min-width:860px;font-size:.87rem}
    .flp-table thead th{background:#083D2A;color:#fff;font-weight:600;font-size:.74rem;letter-spacing:.8px;text-transform:uppercase;padding:12px 14px;text-align:left;white-space:nowrap;border-bottom:3px solid #D4A017}
    .flp-table thead th.flp-num{text-align:right}
    .flp-table tbody td{padding:10px 14px;border-bottom:1px solid #EDF3EF;color:#33403A;vertical-align:top}
    .flp-table tbody td.flp-num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
    .flp-table tbody tr.flp-section td{background:#FBF7EA;border-bottom:1px solid #EADFAE;font-weight:700;color:#083D2A;font-size:.82rem;letter-spacing:1px;text-transform:uppercase;padding:11px 14px}
    .flp-table tbody tr.flp-section td .flp-roman{color:#D4A017;margin-right:10px}
    .flp-table tbody tr.flp-total td{background:#083D2A;color:#fff;font-weight:700;font-size:.9rem;padding:13px 14px}
    .flp-table tbody tr.flp-total td .flp-val{color:#D4A017}
    .flp-table tbody tr.flp-total td.flp-num{color:#D4A017}
    .flp-table tbody tr.flp-fence td{background:#F0F7F3;font-weight:600;color:#0B5D3B}
    .flp-tag{display:inline-block;background:#E8F2EC;color:#0B5D3B;border:1px solid #C9DFD1;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:50px;letter-spacing:.4px}
    .flp-sub{color:#6b7a70;font-size:.8rem;font-style:italic}
    .flp-foot-note{margin-top:16px;font-size:.85rem;color:#5b6f62;line-height:1.7}
    @media(max-width:576px){.flp-table{min-width:720px}}
</style>

<section class="flp-wrap">
    <!-- ===== EN-TÊTE ===== -->
    <div class="flp-hero">
        <div class="container">
            <div class="breadcrumb-ribbon">
                <a href="<?= base_url() ?>">Accueil</a>
                <i class="fa-solid fa-angle-right"></i>
                <a href="<?= base_url('corporate') ?>">Corporate</a>
                <i class="fa-solid fa-angle-right"></i>
                <span>Installation NUFOTEC-PHYTOMED INDUSTRIES</span>
            </div>
            <h1>Installation NUFOTEC <span class="flp-brace">{</span> PHYTOMED INDUSTRIES</h1>
            <div class="flp-goldbar"></div>
        </div>
    </div>

    <!-- ===== CORPS ===== -->
    <div class="flp-body">
        <div class="container">

            <!-- En-tête du rapport -->
            <div class="flp-report-head">
                <h2>RAPPORT DU PLAN D'AMÉNAGEMENT DES INSTALLATIONS POUR NUFOTEC-PHYTOMED INDUSTRIES</h2>
                <span class="flp-en">Facility Layout Plan Report</span>
                <div class="flp-legend">
                    <span class="flp-chip"><b>L</b> Longueur (Length)</span>
                    <span class="flp-chip"><b>2F</b> 2 étages (2 floors)</span>
                    <span class="flp-chip"><b>W</b> Largeur (Width)</span>
                    <span class="flp-chip"><b>HS</b> Étagères hautes (high shelves)</span>
                    <span class="flp-chip"><b>A</b> Surface (Area)</span>
                    <span class="flp-chip"><b>H</b> Hauteur sous plafond (Floor-to-ceiling height)</span>
                </div>
                <p class="flp-note">Pour les formes irrégulières, seule la surface (A) est mentionnée — For irregular shapes, only A is mentioned.</p>
            </div>

            <!-- Tableau du plan d'aménagement -->
            <div class="flp-table-wrap">
                <div class="flp-scroll">
                    <table class="flp-table">
                        <thead>
                            <tr>
                                <th>Réf.</th>
                                <th>Local / Usage</th>
                                <th>Note</th>
                                <th class="flp-num">L (m)</th>
                                <th class="flp-num">W (m)</th>
                                <th class="flp-num">A (m²)</th>
                                <th class="flp-num">H (m)</th>
                            </tr>
                        </thead>
                        <tbody>

                            <!-- I. INDUSTRIAL BUILDING -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">I.</span>Industrial Building</td></tr>
                            <tr><td>I.1</td><td>Reception</td><td></td><td class="flp-num">5</td><td class="flp-num">4</td><td class="flp-num">20.0</td><td class="flp-num">4</td></tr>
                            <tr><td>I.2</td><td>Waiting seats</td><td></td><td class="flp-num">5</td><td class="flp-num">2.5</td><td class="flp-num">12.5</td><td class="flp-num">4</td></tr>
                            <tr><td>I.3</td><td>Staff gathering area</td><td></td><td class="flp-num">–</td><td class="flp-num">–</td><td class="flp-num">150.0</td><td class="flp-num">4</td></tr>

                            <!-- II. OFFICES -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">II.</span>Offices</td></tr>
                            <tr><td>II.1</td><td>Self-contained and shared Offices</td><td></td><td class="flp-num">–</td><td class="flp-num">–</td><td class="flp-num">370.0</td><td class="flp-num">4</td></tr>
                            <tr><td>II.2</td><td>Staff Meeting room</td><td></td><td class="flp-num">11</td><td class="flp-num">8</td><td class="flp-num">88.0</td><td class="flp-num">4</td></tr>
                            <tr><td>II.3</td><td>Toilets</td><td></td><td class="flp-num">–</td><td class="flp-num">–</td><td class="flp-num">62.0</td><td class="flp-num">4</td></tr>

                            <!-- III. DOWNLOADING AREA -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">III.</span>Downloading Area</td></tr>
                            <tr><td>III.1</td><td>Downloading / docking area</td><td></td><td class="flp-num">43</td><td class="flp-num">4</td><td class="flp-num">172.0</td><td class="flp-num">6</td></tr>

                            <!-- IV. RAW MATERIALS -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">IV.</span>Raw Materials</td></tr>
                            <tr><td>IV.1</td><td>Sample storage</td><td><span class="flp-tag">HS</span></td><td class="flp-num">9</td><td class="flp-num">5</td><td class="flp-num">45.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.2</td><td>Wraps shelves keeping</td><td><span class="flp-tag">HS</span></td><td class="flp-num">6</td><td class="flp-num">5</td><td class="flp-num">30.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.3</td><td>Reagent chemical storage</td><td><span class="flp-tag">2F</span></td><td class="flp-num">15</td><td class="flp-num">10</td><td class="flp-num">150.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.4</td><td>Raw material storage</td><td><span class="flp-tag">2F</span></td><td class="flp-num">15</td><td class="flp-num">10</td><td class="flp-num">150.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.5</td><td>Dry Raw material storage</td><td><span class="flp-tag">2F</span></td><td class="flp-num">15</td><td class="flp-num">10</td><td class="flp-num">150.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.6</td><td>Quarantine storage</td><td><span class="flp-tag">HS</span></td><td class="flp-num">5</td><td class="flp-num">10</td><td class="flp-num">50.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.7</td><td>Equipment storage</td><td><span class="flp-tag">HS</span></td><td class="flp-num">3</td><td class="flp-num">10</td><td class="flp-num">30.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.8</td><td>Property store</td><td><span class="flp-tag">HS</span></td><td class="flp-num">9</td><td class="flp-num">5</td><td class="flp-num">45.0</td><td class="flp-num">6</td></tr>
                            <tr><td>IV.9</td><td>Gardener store</td><td><span class="flp-tag">HS</span></td><td class="flp-num">5</td><td class="flp-num">5</td><td class="flp-num">25.0</td><td class="flp-num">6</td></tr>

                            <!-- V. POST HARVEST & PREPROCESSING -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">V.</span>Post Harvest &amp; Preprocessing</td></tr>
                            <tr><td>V.1</td><td>Extracts</td><td></td><td class="flp-num">40</td><td class="flp-num">10</td><td class="flp-num">400.0</td><td class="flp-num">8</td></tr>
                            <tr><td>V.2</td><td>Soymilk powder<span class="flp-sub"> — incl. Liquid soy milk, Dried tofu</span></td><td></td><td class="flp-num">40</td><td class="flp-num">30</td><td class="flp-num">1,200.0</td><td class="flp-num">8</td></tr>
                            <tr><td>V.3</td><td>Spirulina algae</td><td></td><td class="flp-num">40</td><td class="flp-num">10</td><td class="flp-num">400.0</td><td class="flp-num">8</td></tr>
                            <tr><td>V.4</td><td>Fruit juices</td><td></td><td class="flp-num">40</td><td class="flp-num">10</td><td class="flp-num">400.0</td><td class="flp-num">8</td></tr>
                            <tr><td>V.5</td><td>Reserved hall</td><td></td><td class="flp-num">40</td><td class="flp-num">10</td><td class="flp-num">400.0</td><td class="flp-num">8</td></tr>

                            <!-- VI. PROCESSING AND PACKAGING -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">VI.</span>Processing and Packaging</td></tr>
                            <tr><td>VI.1</td><td>Extracts</td><td></td><td class="flp-num">70</td><td class="flp-num">10</td><td class="flp-num">700.0</td><td class="flp-num">8</td></tr>
                            <tr><td>VI.2</td><td>Soymilk powder<span class="flp-sub"> — incl. Liquid soy milk, Dried tofu</span></td><td></td><td class="flp-num">70</td><td class="flp-num">30</td><td class="flp-num">2,100.0</td><td class="flp-num">8</td></tr>
                            <tr><td>VI.3</td><td>Spirulina algae</td><td></td><td class="flp-num">70</td><td class="flp-num">10</td><td class="flp-num">700.0</td><td class="flp-num">8</td></tr>
                            <tr><td>VI.4</td><td>Fruit juices</td><td></td><td class="flp-num">70</td><td class="flp-num">10</td><td class="flp-num">700.0</td><td class="flp-num">8</td></tr>
                            <tr><td>VI.5</td><td>Reserved hall</td><td></td><td class="flp-num">70</td><td class="flp-num">10</td><td class="flp-num">700.0</td><td class="flp-num">8</td></tr>

                            <!-- VII. AISLE -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">VII.</span>Aisle</td></tr>
                            <tr><td>VII.1</td><td>All around the processing halls</td><td></td><td class="flp-num">–</td><td class="flp-num">2.5</td><td class="flp-num">–</td><td class="flp-num">8</td></tr>

                            <!-- VIII. FINISHED PRODUCTS -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">VIII.</span>Finished Products</td></tr>
                            <tr><td>VIII.1</td><td>Industrial cold room</td><td><span class="flp-tag">HS</span></td><td class="flp-num">17.5</td><td class="flp-num">10</td><td class="flp-num">175.0</td><td class="flp-num">6</td></tr>
                            <tr><td>VIII.2</td><td>Finished product store</td><td><span class="flp-tag">2F</span></td><td class="flp-num">13</td><td class="flp-num">10</td><td class="flp-num">130.0</td><td class="flp-num">6</td></tr>
                            <tr><td>VIII.3</td><td>Warehouse</td><td><span class="flp-tag">2F</span></td><td class="flp-num">15</td><td class="flp-num">10</td><td class="flp-num">150.0</td><td class="flp-num">6</td></tr>
                            <tr><td>VIII.4</td><td>Quarantine storage</td><td><span class="flp-tag">HS</span></td><td class="flp-num">5</td><td class="flp-num">10</td><td class="flp-num">50.0</td><td class="flp-num">6</td></tr>
                            <tr><td>VIII.5</td><td>Waiting seats</td><td></td><td class="flp-num">5</td><td class="flp-num">5</td><td class="flp-num">25.0</td><td class="flp-num">4</td></tr>

                            <!-- IX. INDUSTRIAL COLD ROOM -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">IX.</span>Industrial Cold Room</td></tr>
                            <tr><td>IX.1</td><td>Industrial cold room</td><td></td><td class="flp-num">17.5</td><td class="flp-num">10</td><td class="flp-num">175.0</td><td class="flp-num">6</td></tr>

                            <!-- X. PACKAGING AND UPLOADING -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">X.</span>Packaging and Uploading</td></tr>
                            <tr><td>X.1</td><td>Packaging and uploading hall</td><td></td><td class="flp-num">53.5</td><td class="flp-num">4</td><td class="flp-num">214.0</td><td class="flp-num">6</td></tr>

                            <!-- XI. CHANGING ROOMS -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XI.</span>Changing Rooms</td></tr>
                            <tr><td>XI.1</td><td>Changing rooms 1</td><td></td><td class="flp-num">17.5</td><td class="flp-num">13</td><td class="flp-num">227.5</td><td class="flp-num">4</td></tr>
                            <tr><td>XI.2</td><td>Changing rooms 2</td><td></td><td class="flp-num">13</td><td class="flp-num">12.5</td><td class="flp-num">162.5</td><td class="flp-num">4</td></tr>

                            <!-- XII. LABORATORIES -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XII.</span>Laboratories</td></tr>
                            <tr><td>XII.1</td><td>Analytical Chemistry</td><td></td><td class="flp-num">10</td><td class="flp-num">5</td><td class="flp-num">50.0</td><td class="flp-num">6</td></tr>
                            <tr><td>XII.2</td><td>Microbiology</td><td></td><td class="flp-num">10</td><td class="flp-num">5</td><td class="flp-num">50.0</td><td class="flp-num">6</td></tr>
                            <tr><td>XII.3</td><td>Formulation / R&amp;D</td><td></td><td class="flp-num">10</td><td class="flp-num">5</td><td class="flp-num">50.0</td><td class="flp-num">6</td></tr>
                            <tr><td>XII.4</td><td>Stability &amp; Shelf-Life</td><td></td><td class="flp-num">10</td><td class="flp-num">5</td><td class="flp-num">50.0</td><td class="flp-num">6</td></tr>
                            <tr><td>XII.5</td><td>Raw materials / Plant identification</td><td></td><td class="flp-num">10</td><td class="flp-num">5</td><td class="flp-num">50.0</td><td class="flp-num">6</td></tr>
                            <tr><td>XII.6</td><td>Labo storeroom</td><td></td><td class="flp-num">–</td><td class="flp-num">–</td><td class="flp-num">–</td><td class="flp-num">–</td></tr>

                            <!-- XIII. LABORATORY ANIMAL BREEDING FACILITY -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XIII.</span>Laboratory Animal Breeding Facility</td></tr>
                            <tr><td>XIII.1</td><td>Laboratory animal breeding facility</td><td></td><td class="flp-num">20</td><td class="flp-num">15</td><td class="flp-num">300.0</td><td class="flp-num">6</td></tr>

                            <!-- XIV. UTILITIES AND TECHNICALS -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XIV.</span>Utilities and Technicals</td></tr>
                            <tr><td>XIV.1</td><td>HVAC</td><td></td><td class="flp-num">7</td><td class="flp-num">6.5</td><td class="flp-num">45.5</td><td class="flp-num">4</td></tr>
                            <tr><td>XIV.2</td><td>Water system</td><td></td><td class="flp-num">7</td><td class="flp-num">5.5</td><td class="flp-num">38.5</td><td class="flp-num">4</td></tr>
                            <tr><td>XIV.3</td><td>Compressors</td><td></td><td class="flp-num">7</td><td class="flp-num">5.5</td><td class="flp-num">38.5</td><td class="flp-num">4</td></tr>
                            <tr><td>XIV.4</td><td>Power back up</td><td></td><td class="flp-num">7</td><td class="flp-num">5.5</td><td class="flp-num">38.5</td><td class="flp-num">4</td></tr>

                            <!-- XV. WASTE AREA -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XV.</span>Waste Area</td></tr>
                            <tr><td>XV.1</td><td>Waste storage</td><td></td><td class="flp-num">10</td><td class="flp-num">10</td><td class="flp-num">100.0</td><td class="flp-num">4</td></tr>
                            <tr><td>XV.2</td><td>Waste management zone<span class="flp-sub"> — incl. Waste segregation bins</span></td><td></td><td class="flp-num">20</td><td class="flp-num">10</td><td class="flp-num">200.0</td><td class="flp-num">–</td></tr>

                            <!-- XVI. PARKING -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XVI.</span>Parking</td></tr>
                            <tr><td>XVI.1</td><td>Staff and visitors parking</td><td></td><td class="flp-num">78</td><td class="flp-num">5</td><td class="flp-num">390.0</td><td class="flp-num">–</td></tr>
                            <tr><td>XVI.2</td><td>Trucks parking 1</td><td></td><td class="flp-num">20</td><td class="flp-num">8</td><td class="flp-num">160.0</td><td class="flp-num">–</td></tr>
                            <tr><td>XVI.3</td><td>Trucks parking 2</td><td></td><td class="flp-num">19</td><td class="flp-num">8</td><td class="flp-num">152.0</td><td class="flp-num">–</td></tr>

                            <!-- TOTAL INDUSTRIAL BUILDING -->
                            <tr class="flp-total"><td colspan="5">TOTAL BUILT AREA (The building)</td><td class="flp-num">13,640.0</td><td class="flp-num">–</td></tr>
                            <tr class="flp-fence"><td colspan="5">FENCING</td><td class="flp-num">30,000.0</td><td class="flp-num">–</td></tr>
                            <tr><td colspan="7" class="flp-sub">PLUS, THE SITE CIRCULATION ROADS — h = 3.2 m (floor 1) &nbsp;•&nbsp; h = 3.5 m (floor 2)</td></tr>

                            <!-- XVII. STAFF RESIDENTIAL UNITS -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XVII.</span>Staff Residential Units</td></tr>
                            <tr><td>XVII.1</td><td>Seating room</td><td></td><td class="flp-num">4</td><td class="flp-num">2.7</td><td class="flp-num">10.8</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.2</td><td>Dining room + kitchen</td><td></td><td class="flp-num">2.7</td><td class="flp-num">2.5</td><td class="flp-num">6.8</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.3</td><td>Toilet</td><td></td><td class="flp-num">2</td><td class="flp-num">1.5</td><td class="flp-num">3.0</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.4</td><td>Bedroom</td><td></td><td class="flp-num">3.75</td><td class="flp-num">2.5</td><td class="flp-num">9.4</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.5</td><td>Veranda</td><td></td><td class="flp-num">5</td><td class="flp-num">1.5</td><td class="flp-num">7.5</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.6</td><td>Restaurant + Kitchen</td><td></td><td class="flp-num">8</td><td class="flp-num">5</td><td class="flp-num">40.0</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.7</td><td>Supermarket</td><td></td><td class="flp-num">6</td><td class="flp-num">3</td><td class="flp-num">18.0</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.8</td><td>Coffee space</td><td></td><td class="flp-num">5.5</td><td class="flp-num">3</td><td class="flp-num">16.5</td><td class="flp-num">–</td></tr>
                            <tr><td>XVII.9</td><td>Parking</td><td></td><td class="flp-num">50</td><td class="flp-num">5</td><td class="flp-num">250.0</td><td class="flp-num">–</td></tr>
                            <tr class="flp-total"><td colspan="5">TOTAL BUILT AREA (The building)</td><td class="flp-num">620.0</td><td class="flp-num">–</td></tr>
                            <tr><td colspan="7" class="flp-sub">PLUS, THE SITE CIRCULATION ROADS</td></tr>
                            <tr class="flp-fence"><td colspan="5">FENCING</td><td class="flp-num">2,800.0</td><td class="flp-num">–</td></tr>

                            <!-- XVIII. GARAGE -->
                            <tr class="flp-section"><td colspan="7"><span class="flp-roman">XVIII.</span>Garage</td></tr>
                            <tr><td>XVIII.1</td><td>Reception</td><td></td><td class="flp-num">5</td><td class="flp-num">5</td><td class="flp-num">25.0</td><td class="flp-num">4</td></tr>
                            <tr><td>XVIII.2</td><td>Spare parts and storage</td><td></td><td class="flp-num">8</td><td class="flp-num">5</td><td class="flp-num">40.0</td><td class="flp-num">4</td></tr>
                            <tr><td>XVIII.3</td><td>Utility and technical room</td><td></td><td class="flp-num">5</td><td class="flp-num">5</td><td class="flp-num">25.0</td><td class="flp-num">4</td></tr>
                            <tr><td>XVIII.4</td><td>Mechanical workshop</td><td></td><td class="flp-num">10</td><td class="flp-num">8.5</td><td class="flp-num">85.0</td><td class="flp-num">4</td></tr>
                            <tr><td>XVIII.5</td><td>Tire and wheel services</td><td></td><td class="flp-num">10</td><td class="flp-num">10</td><td class="flp-num">100.0</td><td class="flp-num">4</td></tr>
                            <tr><td>XVIII.6</td><td>Quick service area</td><td></td><td class="flp-num">10</td><td class="flp-num">10</td><td class="flp-num">100.0</td><td class="flp-num">4</td></tr>
                            <tr><td>XVIII.7</td><td>Car wash service</td><td></td><td class="flp-num">40</td><td class="flp-num">20</td><td class="flp-num">800.0</td><td class="flp-num">–</td></tr>
                            <tr><td>XVIII.8</td><td>Toilets</td><td></td><td class="flp-num">5</td><td class="flp-num">1.5</td><td class="flp-num">7.5</td><td class="flp-num">4</td></tr>
                            <tr class="flp-total"><td colspan="5">TOTAL BUILT AREA</td><td class="flp-num">1,200.0</td><td class="flp-num">–</td></tr>
                            <tr><td colspan="7" class="flp-sub">PLUS, THE SITE ACCESS ROADS</td></tr>
                            <tr class="flp-fence"><td colspan="5">FENCING</td><td class="flp-num">1,200.0</td><td class="flp-num">–</td></tr>

                        </tbody>
                    </table>
                </div>
            </div>

            <p class="flp-foot-note">
                <i class="fa-solid fa-ruler-combined" style="color:#D4A017;margin-right:6px"></i>
                Surface totale du bâtiment industriel : <strong>13 640 m²</strong> — Clôture : <strong>30 000 m²</strong> —
                Unités résidentielles du personnel : <strong>620 m²</strong> — Garage : <strong>1 200 m²</strong>.
            </p>

        </div>
    </div>
</section>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
