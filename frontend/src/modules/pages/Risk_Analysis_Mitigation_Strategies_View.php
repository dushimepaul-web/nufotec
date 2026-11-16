<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================================
   PAGE : ANALYSE DES RISQUES ET STRATÉGIES D'ATTÉNUATION (statique)
   NUFOTEC-PHYTOMED INDUSTRIES
   ============================================================ */
.rsk-page {
    font-family: 'Poppins', sans-serif;
    background: #fff;
    color: #33403A;
    overflow-x: hidden;
}
.rsk-page * { font-family: 'Poppins', sans-serif; }

/* En-tête de page */
.rsk-page-head {
    position: relative;
    background: linear-gradient(135deg, #083D2A, #0B5D3B);
    padding: 84px 0 64px;
    text-align: center;
    overflow: hidden;
}
.rsk-page-head::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -60px;
    width: 340px; height: 340px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212,160,23,.18) 0%, transparent 70%);
}
.rsk-page-head .rsk-crumb {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.85); font-size: .8rem; font-weight: 500;
    padding: 7px 16px; border-radius: 50px; margin-bottom: 22px;
}
.rsk-page-head .rsk-crumb a { color: rgba(255,255,255,.85); text-decoration: none; }
.rsk-page-head .rsk-crumb a:hover { color: #D4A017; }
.rsk-page-title {
    color: #fff;
    font-size: clamp(1.8rem, 4vw, 2.9rem);
    font-weight: 800;
    line-height: 1.25;
    margin: 0;
    position: relative;
    z-index: 1;
}
.rsk-page-title span { color: #D4A017; }

/* Contenu */
.rsk-content { max-width: 1180px; margin: 0 auto; padding: 72px 24px 96px; }
.rsk-content p { font-size: 1.05rem; line-height: 1.95; color: #33403A; margin-bottom: 26px; text-align: justify; }
.rsk-content p strong { color: #0B5D3B; }

/* Callout intro */
.rsk-intro {
    display: flex; align-items: flex-start; gap: 18px;
    background: #EAF6EF; border-left: 5px solid #0B5D3B;
    border-radius: 18px; padding: 24px 30px; margin-bottom: 36px;
}
.rsk-intro i { color: #0B5D3B; font-size: 1.8rem; margin-top: 3px; }
.rsk-intro p { margin: 0; font-weight: 600; color: #083D2A; line-height: 1.8; }

/* Tableau */
.rsk-table-wrap {
    background: #fff;
    border: 1px solid #E3EBE6;
    border-radius: 18px;
    box-shadow: 0 12px 32px rgba(8,61,42,.07);
    overflow: hidden;
}
.rsk-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .95rem;
}
.rsk-table th {
    background: #F1F7F3;
    color: #083D2A;
    text-align: left;
    font-size: .82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 16px 20px;
    border-bottom: 2px solid #DCE8E0;
}
.rsk-table td {
    padding: 18px 20px;
    vertical-align: top;
    line-height: 1.7;
    color: #33403A;
    border-bottom: 1px solid #EDF2EE;
}
.rsk-table tr:last-child td { border-bottom: none; }
.rsk-table tbody tr { transition: background .25s ease; }
.rsk-table tbody tr:hover { background: #F7FAF8; }
.rsk-table .rsk-num {
    width: 44px; height: 44px; border-radius: 12px;
    background: #EAF6EF; color: #0B5D3B; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.rsk-table .rsk-risk { font-weight: 600; color: #083D2A; }
.rsk-table .rsk-mit {
    display: flex; align-items: flex-start; gap: 8px;
}
.rsk-table .rsk-mit i { color: #D4A017; margin-top: 4px; font-size: .9rem; }
.rsk-table td ul { list-style: none; margin: 0; padding: 0; }
.rsk-table td ul li {
    position: relative;
    padding: 3px 0 3px 22px;
    line-height: 1.65;
}
.rsk-table td ul li::before {
    content: '\F26E';
    font-family: 'bootstrap-icons';
    position: absolute; left: 0; top: 5px;
    color: #D4A017; font-size: .85rem;
}

@media (max-width: 767.98px) {
    .rsk-page-head { padding: 64px 0 48px; }
    .rsk-content { padding: 52px 18px 72px; }
    .rsk-table-wrap { overflow-x: auto; }
    .rsk-table { min-width: 700px; }
}
</style>

<main class="rsk-page">

    <!-- En-tête de page -->
    <div class="rsk-page-head">
        <div class="rsk-crumb">
            <i class="bi bi-house-door"></i>
            <a href="<?= base_url() ?>">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Corporate</span>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Gestion des risques</span>
        </div>
        <h1 class="rsk-page-title">Analyse des risques et stratégies <span>d'atténuation</span></h1>
    </div>

    <div class="rsk-content">

        <div class="rsk-intro">
            <i class="bi bi-shield-shaded"></i>
            <p>
                NUFOTEC applique un cadre complet de gestion des risques d'entreprise (ERM) adapté aux réalités des
                entreprises africaines ci-dessous :
            </p>
        </div>

        <div class="rsk-table-wrap">
            <table class="rsk-table">
                <thead>
                    <tr>
                        <th style="width:80px;">No</th>
                        <th style="width:38%;">Risks</th>
                        <th>Mitigation Strategies</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="rsk-num">1</span></td>
                        <td class="rsk-risk">Drought, erratic rainfall, water scarcity, and logistics challenges → reduced yields</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Drip/sprinkler irrigation, drought-tolerant varieties, integrated pest management, climate monitoring and geographic diversification</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">2</span></td>
                        <td class="rsk-risk">ESG and Community Risks: Land disputes or reputational issues</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Land title deeds, community inclusion programs, outgrower contracts, ESG reporting</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">3</span></td>
                        <td class="rsk-risk">Load-shedding affecting negatively the processing and the cold chain</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Solar/diesel hybrid power systems, backup industrial generator, energy-efficient processing</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">4</span></td>
                        <td class="rsk-risk">Fluctuating prices, foreign exchange</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Diversified products (Tablets, capsules, powders…), contract farming &amp; forward sales agreements</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">5</span></td>
                        <td class="rsk-risk">Soil depletion, biodiversity impact</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Agroforestry, organic fertilizers, soil testing, sustainable land management</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">6</span></td>
                        <td class="rsk-risk">Quality loss, spoilage, inefficiencies</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> GMP-compliant processing, cold storage, optimized logistics, HACCP protocols</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">7</span></td>
                        <td class="rsk-risk">Worker illness, labor shortage</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Occupational health programs, sanitation, training, insurance coverage</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">8</span></td>
                        <td class="rsk-risk">Crop, machinery, or input theft and vandalism</td>
                        <td>
                            <ul>
                                <li>Security wall fence</li>
                                <li>Security wire fence surrounding the whole farmland</li>
                                <li>Security guards</li>
                                <li>Perimeter security, CCTV cameras, community engagement, access controls</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">9</span></td>
                        <td class="rsk-risk">Unexpected losses (climate, market, fire)</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Crop and asset insurance, contingency funds, prudent financial planning</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">10</span></td>
                        <td class="rsk-risk">Macroeconomic and Foreign Exchange Risks: Currency fluctuations, inflation, and import constraints</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> USD-denominated exports, multi-currency accounts, and natural hedging</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">11</span></td>
                        <td class="rsk-risk">Political and Regulatory Risks: Policy shifts, licensing delays, and taxation changes</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Proactive engagement with regulators, legal advisory, and diversification across markets</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">12</span></td>
                        <td class="rsk-risk">Supply Chain Risks: Variability in raw materials and potential cooperative defaults</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Contractual suppliers, local cooperatives, quality pre-testing, and multiple sourcing options</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">13</span></td>
                        <td class="rsk-risk">Governance and Capital Risks: Misallocation or weak controls</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Milestone-based disbursement, segregated accounts, IFRS-aligned audits, and investor oversight</div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="rsk-num">14</span></td>
                        <td class="rsk-risk">Market Access Risks: Payment delays and counterfeit products</td>
                        <td>
                            <div class="rsk-mit"><i class="bi bi-check-circle-fill"></i> Pre-offtake agreements, digital traceability, and diversified distribution channels</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</main>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
