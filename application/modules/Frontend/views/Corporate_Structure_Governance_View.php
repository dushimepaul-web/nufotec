<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================================
   PAGE : STRUCTURE ET GOUVERNANCE D'ENTREPRISE (statique)
   NUFOTEC-PHYTOMED INDUSTRIES
   ============================================================ */
.csg-page {
    font-family: 'Poppins', sans-serif;
    background: #fff;
    color: #33403A;
    overflow-x: hidden;
}
.csg-page * { font-family: 'Poppins', sans-serif; }

/* En-tête de page */
.csg-page-head {
    position: relative;
    background: linear-gradient(135deg, #083D2A, #0B5D3B);
    padding: 84px 0 64px;
    text-align: center;
    overflow: hidden;
}
.csg-page-head::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -60px;
    width: 340px; height: 340px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212,160,23,.18) 0%, transparent 70%);
}
.csg-page-head .csg-crumb {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.85); font-size: .8rem; font-weight: 500;
    padding: 7px 16px; border-radius: 50px; margin-bottom: 22px;
}
.csg-page-head .csg-crumb a { color: rgba(255,255,255,.85); text-decoration: none; }
.csg-page-head .csg-crumb a:hover { color: #D4A017; }
.csg-page-title {
    color: #fff;
    font-size: clamp(1.8rem, 4vw, 2.9rem);
    font-weight: 800;
    line-height: 1.25;
    margin: 0;
    position: relative;
    z-index: 1;
}
.csg-page-title span { color: #D4A017; }

/* Contenu */
.csg-content {
    max-width: 1180px;
    margin: 0 auto;
    padding: 72px 24px 96px;
}
.csg-content p {
    font-size: 1.05rem;
    line-height: 1.95;
    color: #33403A;
    margin-bottom: 26px;
    text-align: justify;
}
.csg-content p strong { color: #0B5D3B; }

/* Titres de section */
.csg-section-title {
    font-size: clamp(1.5rem, 2.6vw, 2.1rem);
    font-weight: 800;
    color: #083D2A;
    margin: 0 0 8px;
}
.csg-badge {
    display: inline-flex; align-items: center; gap: 10px;
    background: #EAF6EF; border: 1px solid #BBD8C6; color: #0B5D3B;
    font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 8px 22px; border-radius: 50px; margin-bottom: 18px;
}
.csg-block { margin-bottom: 64px; }
.csg-note {
    background: #EAF6EF; border-left: 5px solid #D4A017;
    border-radius: 16px; padding: 22px 28px; margin-bottom: 30px;
    font-size: 1.02rem; line-height: 1.85; color: #083D2A;
}

/* Tableaux */
.csg-table-wrap {
    background: #fff;
    border: 1px solid #E3EBE6;
    border-radius: 18px;
    box-shadow: 0 12px 32px rgba(8,61,42,.07);
    overflow: hidden;
    margin-top: 26px;
}
.csg-table-title {
    background: linear-gradient(135deg, #0B5D3B, #083D2A);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    padding: 18px 26px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.csg-table-title i { color: #D4A017; }
.csg-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .95rem;
}
.csg-table th {
    background: #F1F7F3;
    color: #083D2A;
    text-align: left;
    font-size: .82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 14px 18px;
    border-bottom: 2px solid #DCE8E0;
}
.csg-table td {
    padding: 15px 18px;
    vertical-align: top;
    line-height: 1.6;
    color: #33403A;
    border-bottom: 1px solid #EDF2EE;
}
.csg-table tr:last-child td { border-bottom: none; }
.csg-table tbody tr { transition: background .25s ease; }
.csg-table tbody tr:hover { background: #F7FAF8; }
.csg-table td:first-child { font-weight: 700; color: #0B5D3B; }
.csg-table .csg-num {
    width: 42px; height: 42px; border-radius: 12px;
    background: #EAF6EF; color: #0B5D3B; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.csg-table td ul { list-style: none; margin: 0; padding: 0; }
.csg-table td ul li {
    position: relative;
    padding: 4px 0 4px 22px;
    line-height: 1.65;
}
.csg-table td ul li::before {
    content: '\F26E';
    font-family: 'bootstrap-icons';
    position: absolute; left: 0; top: 6px;
    color: #D4A017; font-size: .85rem;
}

/* Liste des licences */
.csg-licenses {
    list-style: none;
    margin: 26px 0 0;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.csg-licenses li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #F7FAF8;
    border: 1px solid #E3EBE6;
    border-radius: 14px;
    padding: 14px 18px;
    font-size: .95rem;
    line-height: 1.55;
    color: #33403A;
    transition: all .25s ease;
}
.csg-licenses li:hover { border-color: #BBD8C6; background: #fff; box-shadow: 0 8px 20px rgba(11,93,59,.08); }
.csg-licenses li i { color: #0B5D3B; font-size: 1.1rem; margin-top: 2px; }
.csg-licenses li strong { color: #083D2A; }

@media (max-width: 767.98px) {
    .csg-page-head { padding: 64px 0 48px; }
    .csg-content { padding: 52px 18px 72px; }
    .csg-licenses { grid-template-columns: 1fr; }
    .csg-table-wrap { overflow-x: auto; }
    .csg-table { min-width: 640px; }
}
</style>

<main class="csg-page">

    <!-- En-tête de page -->
    <div class="csg-page-head">
        <div class="csg-crumb">
            <i class="bi bi-house-door"></i>
            <a href="<?= base_url() ?>">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Corporate</span>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Gouvernance</span>
        </div>
        <h1 class="csg-page-title">Structure et gouvernance <span>d'entreprise</span></h1>
    </div>

    <div class="csg-content">

        <!-- ══════════════ STRUCTURE ADMINISTRATIVE ══════════════ -->
        <div class="csg-block">
            <span class="csg-badge">Gouvernance</span>
            <h2 class="csg-section-title">Structure administrative</h2>
            <p style="margin-top:20px;">
                NUFOTEC adoptera un cadre de gestion structuré avec des professionnels qualifiés pour les rôles clés :
            </p>
            <p>
                NUFOTEC-PHYTOMED Industries opère sous une structure de gouvernance d'entreprise basée sur les
                compétences et un leadership exécutif axé sur la science, conçue pour garantir la transparence, la
                conformité réglementaire et une croissance industrielle durable. L'entreprise est supervisée par un
                Conseil d'Administration professionnellement constitué qui assure une supervision stratégique, la
                supervision des risques et la protection des investisseurs. La direction exécutive est structurée en
                fonctions spécialisées couvrant les opérations, la recherche scientifique, les finances, l'assurance
                qualité et le développement commercial. Les activités opérationnelles sont organisées en divisions
                distinctes couvrant l'agriculture biologique commerciale, la transformation et la fabrication
                avancées, la recherche et l'innovation, la conformité réglementaire et les services corporatifs. Des
                mécanismes indépendants d'audit, de conformité et de supervision ESG sont intégrés au cadre de
                gouvernance pour garantir la responsabilité, l'intégrité institutionnelle et la résilience à long
                terme de l'entreprise.
            </p>

            <div class="csg-table-wrap">
                <div class="csg-table-title">
                    <i class="bi bi-people"></i> Administrative Structure
                </div>
                <table class="csg-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Position</th>
                            <th style="width:34%;">Qualification</th>
                            <th style="width:36%;">Major Role(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="csg-num">1</span></td>
                            <td>Chief Executive Officer (CEO)</td>
                            <td>—</td>
                            <td>Overall management and decision making</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">2</span></td>
                            <td>Board of Directors</td>
                            <td>—</td>
                            <td>Provision of strategic direction</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">3</span></td>
                            <td>Chief Operations Officer (COO)</td>
                            <td>PhD in Clinical Pharmacology, Pharmacokinetics or Drug Development</td>
                            <td>Overseeing production, quality control, R&amp;D coordination, regulatory compliance, technical stuff, supply chain, facility operations, and safety</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">4</span></td>
                            <td>Finance &amp; Administration Manager</td>
                            <td>Master's degree in Business Administration or related field</td>
                            <td>Management of accounting and human resources</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">5</span></td>
                            <td>Director of Quality Control</td>
                            <td>Master's degree in Chemistry / Biotechnology / Food Science and Technology</td>
                            <td>Overseeing of correct and efficient manufacturing-related operations</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">6</span></td>
                            <td>Director of Industrial Maintenance</td>
                            <td>Master's degree in industrial maintenance, skilled in industrial automation, Good Manufacturing Practice (GMP) and Standard Operating Procedures (SOPs)</td>
                            <td>Overseeing of installation, repair and maintenance of all facility's processing and research laboratory equipment</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">7</span></td>
                            <td>Head of (Research) Laboratory</td>
                            <td>PhD degree in Molecular Biology / Biochemistry</td>
                            <td>Overseeing lab research direction, staff supervision and lab operations compliance</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">8</span></td>
                            <td>Sales and Marketing Manager</td>
                            <td>Bachelor degree in Marketing, Business Administration</td>
                            <td>Management of sales team, marketing staff and distributors</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">9</span></td>
                            <td>Digital Marketing and Creative Design Manager</td>
                            <td>Bachelor degree in Marketing, Communications and Graphic Design</td>
                            <td>Graphic design and mass media advertising</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">10</span></td>
                            <td>Supply and Logistic Officer</td>
                            <td>Bachelor degree in Logistics, Supply Chain Management</td>
                            <td>Overseeing procurement control, inventory and distribution</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">11</span></td>
                            <td>Corporate Lawyer</td>
                            <td>Holding Law Degree / Corporate Business Law</td>
                            <td>Overseeing all company's legal matters</td>
                        </tr>
                        <tr>
                            <td><span class="csg-num">12</span></td>
                            <td>Director of Agricultural Operations</td>
                            <td>Master's degree in Agricultural Engineering</td>
                            <td>Overseeing the crop / medicinal plant organic industrial farming</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══════════════ COMITÉS DE GOUVERNANCE ══════════════ -->
        <div class="csg-block">
            <span class="csg-badge">Comités &amp; départements</span>
            <h2 class="csg-section-title">Comités de gouvernance et personnalités</h2>

            <div class="csg-table-wrap">
                <div class="csg-table-title">
                    <i class="bi bi-diagram-3"></i> Governance Committees &amp; Personalities
                </div>
                <table class="csg-table">
                    <thead>
                        <tr>
                            <th style="width:32%;">Committee</th>
                            <th>Responsibilities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Executive Management Committee (EMC)</td>
                            <td>
                                <ul>
                                    <li>Strategic planning &amp; policy approval</li>
                                    <li>Oversees departmental coordination</li>
                                    <li>Budget &amp; investment decisions</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Governance &amp; Compliance</td>
                            <td>
                                <ul>
                                    <li>Ensures regulatory compliance with national and international standards (e.g. ZAMRA, ZEMA, GMP, ISO)</li>
                                    <li>Oversees ethical, legal and risk management frameworks across departments</li>
                                    <li>Monitors internal policies and guides organizational accountabilities and transparency</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Quality Management Committee (QMC)</td>
                            <td>
                                <ul>
                                    <li>Ensures GMP/ISO compliance</li>
                                    <li>Reviews internal audits, CAPA, deviations</li>
                                    <li>Approves changes to SOPs/QA systems</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Ethics &amp; Compliance Committee</td>
                            <td>
                                <ul>
                                    <li>Monitors regulatory, legal, and environmental compliance</li>
                                    <li>Prevents conflicts of interest</li>
                                    <li>Promotes ethical business conduct</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Product Development / R&amp;D Committee</td>
                            <td>
                                <ul>
                                    <li>Approves new formulations</li>
                                    <li>Oversees clinical validation (if any)</li>
                                    <li>Guides innovation strategy</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Health &amp; Safety Committee</td>
                            <td>
                                <ul>
                                    <li>Implements occupational health/safety policies</li>
                                    <li>Risk assessment &amp; emergency preparedness</li>
                                    <li>Coordinates safety training</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="csg-table-wrap">
                <div class="csg-table-title">
                    <i class="bi bi-grid-3x3-gap"></i> Departments &amp; Responsibilities
                </div>
                <table class="csg-table">
                    <thead>
                        <tr>
                            <th style="width:32%;">Department</th>
                            <th>Responsibilities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Production Department</td>
                            <td>
                                <ul>
                                    <li>Executes manufacturing (extraction, formulation, packaging)</li>
                                    <li>Maintains batch records, GMP hygiene</li>
                                    <li>Manages equipment operations</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Quality Assurance (QA)</td>
                            <td>
                                <ul>
                                    <li>GMP implementation &amp; document control (SOPs, CAPA)</li>
                                    <li>In-process and post-production checks</li>
                                    <li>Batch release authorization</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Quality Control (QC)</td>
                            <td>
                                <ul>
                                    <li>Lab testing (raw materials, in-process, finished products)</li>
                                    <li>Microbiology, chemical, and instrumental testing</li>
                                    <li>Stability testing and sampling</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Research &amp; Development (R&amp;D)</td>
                            <td>
                                <ul>
                                    <li>Product formulation &amp; standardization</li>
                                    <li>Pilot production and validation</li>
                                    <li>Ingredient functionality research</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Regulatory Affairs</td>
                            <td>
                                <ul>
                                    <li>Handles product registration (ZAMRA, MOH)</li>
                                    <li>Prepares dossiers, compliance documentation</li>
                                    <li>Communicates with relevant authorities</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Supply Chain &amp; Logistics</td>
                            <td>
                                <ul>
                                    <li>Procurement of raw materials, capsules, packaging</li>
                                    <li>Warehouse &amp; stock management</li>
                                    <li>Delivery/distribution planning</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Finance &amp; Accounting</td>
                            <td>
                                <ul>
                                    <li>Manages budgeting, salaries, audits</li>
                                    <li>Prepares financial statements</li>
                                    <li>Handles L/Cs, T/Ts, and bank relations</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Marketing and Sales</td>
                            <td>
                                <ul>
                                    <li>Develops branding &amp; promotion strategy</li>
                                    <li>Manages media, partnerships, and advertising</li>
                                    <li>Supports product launch</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Human Resources (HR)</td>
                            <td>
                                <ul>
                                    <li>Staff recruitment, onboarding, and training</li>
                                    <li>Oversees compliance with labor</li>
                                    <li>Evaluates performance &amp; HR policies</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Facility Engineering &amp; Maintenance</td>
                            <td>
                                <ul>
                                    <li>Maintains cleanroom, HVAC, utilities</li>
                                    <li>Ensures GMP-compliant infrastructure</li>
                                    <li>Coordinates equipment calibration/repairs</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>Risk Management</td>
                            <td>
                                <ul>
                                    <li>Identifies, assesses and monitors risks that could impact operations, finance or compliance</li>
                                    <li>Develops mitigation strategies and contingency plans to minimize potential losses</li>
                                    <li>Ensures integration of risk controls into daily operations and strategic planning</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>IT &amp; Data Systems</td>
                            <td>
                                <ul>
                                    <li>Manages data integrity, cyber security, LIMS/ERP systems</li>
                                    <li>Supports automation and traceability</li>
                                    <li>Digital compliance (e.g., 21 CFR Part 11)</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══════════════ LICENCES ══════════════ -->
        <div class="csg-block" style="margin-bottom:0;">
            <span class="csg-badge">Conformité réglementaire</span>
            <h2 class="csg-section-title">Licensing and Regulatory Compliance</h2>
            <p style="margin-top:20px;">
                <strong>AGF-PHYTOMED INDUSTRIES</strong> is subjected to the following regulatory licensing:
            </p>

            <ul class="csg-licenses">
                <li><i class="bi bi-patch-check"></i> Business Application &amp; Registration – <strong>PACRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Investment Licence – <strong>ZDA</strong></li>
                <li><i class="bi bi-patch-check"></i> Environmental Impact Assessment (EIA) Certificate – <strong>ZEMA</strong></li>
                <li><i class="bi bi-patch-check"></i> Screening EPB Report Certificate – <strong>ZEMA</strong></li>
                <li><i class="bi bi-patch-check"></i> Waste Management Licence – <strong>ZEMA</strong></li>
                <li><i class="bi bi-patch-check"></i> Site Approval Certificate – <strong>ZMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Product Registration – <strong>ZMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> GMP Inspection – <strong>ZAMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Manufacturing Licence – <strong>ZAMRA</strong></li>
                <li><i class="bi bi-patch-check"></i> Investor Permit – <strong>Immigration</strong></li>
                <li><i class="bi bi-patch-check"></i> Product Testing &amp; Application – <strong>ZABS</strong></li>
                <li><i class="bi bi-patch-check"></i> Factory Inspection – <strong>ZABS</strong></li>
                <li><i class="bi bi-patch-check"></i> Certification Mark – <strong>ZABS</strong></li>
                <li><i class="bi bi-patch-check"></i> Premises Hygiene Clearance Certificate – <strong>Local Health Department (Municipal/District Health Office)</strong></li>
                <li><i class="bi bi-patch-check"></i> Food Safety Inspection/Compliance Certificate – <strong>Local Health Department (Municipal/District Health Office)</strong></li>
                <li><i class="bi bi-patch-check"></i> Food Handling/Safety Certificate – <strong>Local Health Department (Municipal/District Health Office)</strong></li>
                <li><i class="bi bi-patch-check"></i> Occupational Health Risk Assessment Certificate – <strong>Local Health Department (Municipal/District)</strong></li>
                <li><i class="bi bi-patch-check"></i> Annual Health Compliance Verification – <strong>Local Health Department (Municipal/District Health Office)</strong></li>
                <li><i class="bi bi-patch-check"></i> Land Title Deeds – <strong>Headmen, Chief, District, Province &amp; Ministry of Land</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 9001 – Quality Management System – <strong>Initial Certification</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 9001 – Quality Management System – <strong>Annual Surveillance</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 22000 – <strong>Food Safety</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 14001 – Environmental Governance and Sustainability – <strong>Initial Certification</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 14001 – Environmental Governance and Sustainability – <strong>Annual Surveillance</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 45001 – Occupational Health &amp; Safety – <strong>Initial Certification</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 45001 – Occupational Health &amp; Safety – <strong>Annual Surveillance Audits</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 14644 – Cleanroom Qualification <strong>ISO 5 / Room x 10</strong></li>
                <li><i class="bi bi-patch-check"></i> ISO 14644 – Cleanroom Annual Re-certification Audits <strong>ISO 5 / Room x 10</strong></li>
            </ul>
        </div>

    </div>

</main>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
