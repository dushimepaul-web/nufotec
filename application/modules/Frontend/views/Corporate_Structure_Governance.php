VOIC MA PAGE CORPORATE STRUCTURE & GOVERNANCE <?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================
   VARIABLES GLOBALES
   ============================================ */
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --primary-soft: rgba(15, 76, 58, 0.1);
    --accent: #d4af37;
    --accent-hover: #b8962e;
    --accent-soft: rgba(212, 175, 55, 0.15);
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --gray-light: #dee2e6;
    --gray-soft: #f1f3f5;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --purple: #8b5cf6;
    --pink: #ec4899;
    --shadow-sm: 0 4px 6px rgba(0,0,0,0.05);
    --shadow: 0 10px 20px rgba(0,0,0,0.1);
    --shadow-lg: 0 20px 40px rgba(0,0,0,0.15);
    --shadow-xl: 0 30px 60px rgba(0,0,0,0.2);
    --shadow-hover: 0 30px 50px rgba(15, 76, 58, 0.25);
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    --border-radius-sm: 12px;
    --border-radius-md: 20px;
    --border-radius-lg: 30px;
    --border-radius-xl: 40px;
    --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-secondary: 'Playfair Display', serif;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-primary);
    color: var(--dark);
    overflow-x: hidden;
}

/* ============================================
   PAGE HERO
   ============================================ */
.page-hero {
    position: relative;
    min-height: 60vh;
    background: linear-gradient(135deg, #0a2e24, #1a4a3a);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
}

.page-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><path d="M20 20 L80 20 L80 80 L20 80 Z" fill="none" stroke="%23d4af37" stroke-width="2"/><circle cx="50" cy="50" r="20" fill="none" stroke="%23d4af37" stroke-width="2"/><path d="M30 30 L70 70 M70 30 L30 70" stroke="%23d4af37" stroke-width="2"/></svg>');
    background-size: 100px 100px;
    animation: moveBackground 30s linear infinite;
}

@keyframes moveBackground {
    from { transform: translateY(0) rotate(0); }
    to { transform: translateY(-100px) rotate(10deg); }
}

.page-hero::after {
    content: '⚖️';
    position: absolute;
    right: 5%;
    bottom: 10%;
    font-size: 120px;
    opacity: 0.1;
    transform: rotate(-15deg);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: rotate(-15deg) translateY(0); }
    50% { transform: rotate(-15deg) translateY(-30px); }
}

.page-hero-content {
    position: relative;
    z-index: 2;
    max-width: 1000px;
    padding: 80px 20px;
}

.page-hero-title {
    font-family: var(--font-secondary);
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 700;
    color: white;
    margin-bottom: 25px;
    animation: fadeInDown 1s ease;
    text-shadow: 2px 2px 20px rgba(0,0,0,0.3);
}

.page-hero-title span {
    color: var(--accent);
    position: relative;
    display: inline-block;
}

.page-hero-title span::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--accent);
    animation: expandWidth 1s ease 0.5s forwards;
    transform-origin: left;
    transform: scaleX(0);
    box-shadow: 0 0 20px var(--accent);
}

@keyframes expandWidth {
    to { transform: scaleX(1); }
}

.page-hero-subtitle {
    font-size: clamp(1.1rem, 2vw, 1.4rem);
    color: rgba(255,255,255,0.95);
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
    animation: fadeInUp 1s ease 0.3s forwards;
    opacity: 0;
    animation-fill-mode: forwards;
    text-shadow: 1px 1px 10px rgba(0,0,0,0.3);
}

/* ============================================
   SECTION STYLES
   ============================================ */
.section {
    padding: 90px 0;
    position: relative;
    overflow: hidden;
}

.section:nth-child(even) {
    background: linear-gradient(135deg, #ffffff, var(--gray-soft));
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
    opacity: 0;
    transform: translateY(30px);
    transition: var(--transition);
}

.section-header.visible {
    opacity: 1;
    transform: translateY(0);
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 25px;
    background: var(--accent-soft);
    color: var(--primary);
    font-weight: 700;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 50px;
    margin-bottom: 20px;
    border: 1px solid var(--accent);
    box-shadow: 0 4px 15px rgba(212,175,55,0.2);
}

.section-tag::before {
    content: '';
    width: 8px;
    height: 8px;
    background: var(--accent);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.7; }
}

.section-title {
    font-family: var(--font-secondary);
    font-size: clamp(2.2rem, 4vw, 3.2rem);
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 15px;
    line-height: 1.2;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--gray);
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
}

/* ============================================
   GOVERNANCE OVERVIEW
   ============================================ */
.governance-overview {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--border-radius-xl);
    padding: 60px;
    color: white;
    margin-bottom: 60px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.governance-overview::before {
    content: '🏛️';
    position: absolute;
    right: 20px;
    bottom: 20px;
    font-size: 180px;
    opacity: 0.1;
    transform: rotate(10deg);
}

.governance-overview::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, transparent 70%);
    animation: rotate 40s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.governance-overview-text {
    font-size: 1.2rem;
    line-height: 1.9;
    margin-bottom: 30px;
    position: relative;
    z-index: 2;
    max-width: 900px;
}

/* ============================================
   ORGANIZATIONAL CHART
   ============================================ */
.org-chart {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 60px 0;
}

.org-level {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 30px;
    margin: 20px 0;
    position: relative;
}

.org-level-1 {
    margin-bottom: 40px;
}

.org-level-2 {
    margin-bottom: 40px;
}

.org-node {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 25px 35px;
    box-shadow: var(--shadow-lg);
    text-align: center;
    min-width: 250px;
    transition: var(--transition);
    border: 2px solid transparent;
    position: relative;
}

.org-node:hover {
    transform: translateY(-10px);
    border-color: var(--accent);
    box-shadow: var(--shadow-hover);
}

.org-node.primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.org-node.primary .org-title {
    color: var(--accent);
}

.org-node.primary .org-name {
    color: white;
}

.org-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray);
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.org-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary);
}

.org-connector {
    width: 2px;
    height: 30px;
    background: linear-gradient(to bottom, var(--accent), var(--primary));
    margin: 0 auto;
}

.org-connector.horizontal {
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
    margin: auto 0;
}

/* ============================================
   ADMINISTRATIVE TABLE
   ============================================ */
.admin-table-container {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    margin: 50px 0;
    border: 1px solid var(--gray-light);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 20px 15px;
    font-size: 1rem;
    font-weight: 600;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,0.1);
}

.admin-table th:last-child {
    border-right: none;
}

.admin-table td {
    padding: 18px 15px;
    border-bottom: 1px solid var(--gray-light);
    color: var(--dark);
    font-size: 0.95rem;
    vertical-align: top;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.admin-table tr:hover td {
    background: var(--accent-soft);
}

.admin-table .position {
    font-weight: 700;
    color: var(--primary);
}

.admin-table .qualification {
    color: var(--accent-hover);
    font-weight: 500;
}

/* ============================================
   COMMITTEES GRID
   ============================================ */
.committees-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin: 50px 0;
}

.committee-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 30px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-left: 5px solid transparent;
    height: 100%;
}

.committee-card:hover {
    transform: translateY(-10px);
    border-left-color: var(--accent);
    box-shadow: var(--shadow-hover);
}

.committee-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    font-size: 1.8rem;
    color: var(--primary);
    transition: var(--transition);
}

.committee-card:hover .committee-icon {
    background: var(--accent);
    color: white;
    transform: rotate(360deg);
}

.committee-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 15px;
}

.committee-list {
    list-style: none;
    padding: 0;
}

.committee-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 0.95rem;
    color: var(--gray);
}

.committee-list li i {
    color: var(--accent);
    font-size: 1rem;
}

/* ============================================
   DEPARTMENTS GRID
   ============================================ */
.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin: 50px 0;
}

.department-card {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 25px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-bottom: 4px solid transparent;
    height: 100%;
}

.department-card:hover {
    transform: translateY(-8px);
    border-bottom-color: var(--accent);
    box-shadow: var(--shadow-hover);
}

.department-icon {
    width: 50px;
    height: 50px;
    background: var(--accent-soft);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    font-size: 1.5rem;
    color: var(--accent);
    transition: var(--transition);
}

.department-card:hover .department-icon {
    background: var(--accent);
    color: white;
    transform: rotate(360deg);
}

.department-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 10px;
}

.department-desc {
    font-size: 0.95rem;
    color: var(--gray);
    line-height: 1.6;
    margin-bottom: 15px;
}

.department-list {
    list-style: none;
    padding: 0;
}

.department-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 0.9rem;
    color: var(--dark);
}

.department-list li i {
    color: var(--accent);
    font-size: 0.9rem;
}

/* ============================================
   LICENSING SECTION
   ============================================ */
.licensing-section {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--border-radius-xl);
    padding: 60px 40px;
    color: white;
    margin: 60px 0;
    position: relative;
    overflow: hidden;
}

.licensing-section::before {
    content: '✓✓';
    position: absolute;
    right: -30px;
    bottom: -30px;
    font-size: 200px;
    font-weight: 900;
    opacity: 0.1;
    color: white;
    transform: rotate(15deg);
}

.licensing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 40px;
    position: relative;
    z-index: 2;
}

.license-item {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius-md);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid rgba(255,255,255,0.2);
    transition: var(--transition);
}

.license-item:hover {
    transform: translateX(10px);
    background: rgba(255,255,255,0.15);
    border-color: var(--accent);
}

.license-icon {
    width: 50px;
    height: 50px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary-dark);
    flex-shrink: 0;
}

.license-content {
    flex: 1;
}

.license-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 3px;
    color: white;
}

.license-authority {
    font-size: 0.85rem;
    opacity: 0.8;
}

/* ============================================
   ISO CERTIFICATIONS
   ============================================ */
.iso-showcase {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: var(--border-radius-xl);
    padding: 50px;
    margin: 60px 0;
    box-shadow: var(--shadow-lg);
}

.iso-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 40px;
}

.iso-card {
    background: white;
    border-radius: var(--border-radius-md);
    padding: 25px;
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 2px solid transparent;
}

.iso-card:hover {
    transform: translateY(-10px);
    border-color: var(--accent);
    box-shadow: var(--shadow-hover);
}

.iso-number {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 10px;
}

.iso-name {
    font-size: 1rem;
    color: var(--gray);
    margin-bottom: 10px;
    font-weight: 600;
}

.iso-type {
    font-size: 0.9rem;
    color: var(--accent);
    font-weight: 500;
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */
@media (max-width: 992px) {
    .section {
        padding: 60px 0;
    }
    
    .governance-overview {
        padding: 40px;
    }
    
    .admin-table {
        font-size: 0.9rem;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 15px 10px;
    }
}

@media (max-width: 768px) {
    .org-level {
        flex-direction: column;
        align-items: center;
    }
    
    .org-node {
        width: 100%;
        max-width: 350px;
    }
    
    .org-connector.horizontal {
        display: none;
    }
    
    .admin-table {
        display: block;
        overflow-x: auto;
    }
    
    .committees-grid {
        grid-template-columns: 1fr;
    }
    
    .departments-grid {
        grid-template-columns: 1fr;
    }
    
    .licensing-grid {
        grid-template-columns: 1fr;
    }
    
    .iso-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .page-hero-title {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .iso-grid {
        grid-template-columns: 1fr;
    }
    
    .governance-overview {
        padding: 30px 20px;
    }
    
    .governance-overview-text {
        font-size: 1rem;
    }
}

/* ============================================
   ANIMATIONS
   ============================================ */
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Scroll Progress */
.scroll-progress {
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    height: 4px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
    z-index: 9999;
    transition: width 0.1s;
}

/* Back to Top */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    box-shadow: var(--shadow);
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 100;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: var(--accent);
    transform: translateY(-5px);
}
</style>

<!-- Scroll Progress -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- PAGE HERO -->
<section class="page-hero">
    <div class="page-hero-content">
        <h1 class="page-hero-title">Corporate Structure & <span>Governance</span></h1>
        <p class="page-hero-subtitle">Competency-Based Governance Framework Ensuring Transparency, Compliance, and Sustainable Industrial Growth</p>
    </div>
</section>

<!-- GOVERNANCE OVERVIEW -->
<section class="section">
    <div class="section-container">
        <div class="governance-overview">
            <p class="governance-overview-text">
                AGF-PHYTOMED Industries operates under a competency-based corporate governance structure and Science-Driven Executive Leadership designed to ensure transparency, regulatory compliance, and sustainable industrial growth. The company is overseen by a professionally constituted Board of Directors providing strategic oversight, risk supervision, and investor protection. Executive management is structured across specialized functions including operations, scientific research, finance, quality assurance, and commercial development. Operational activities are organized into distinct divisions covering commercial organic farming, advanced processing and manufacturing, research and innovation, regulatory compliance, and corporate services. Independent audit, compliance, and ESG oversight mechanisms are embedded within the governance framework to ensure accountability, institutional integrity, and long-term enterprise resilience.
            </p>
        </div>
    </div>
</section>

<!-- ORGANIZATIONAL CHART -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-tag">Leadership</span>
            <h2 class="section-title">Organizational Structure</h2>
            <p class="section-subtitle">Competency-based leadership framework with clear reporting lines</p>
        </div>

        <div class="org-chart">
            <!-- Board Level -->
            <div class="org-level org-level-1">
                <div class="org-node primary">
                    <div class="org-title">Oversight</div>
                    <div class="org-name">Board of Directors</div>
                </div>
            </div>
            
            <div class="org-connector"></div>
            
            <!-- CEO Level -->
            <div class="org-level org-level-2">
                <div class="org-node">
                    <div class="org-title">Executive</div>
                    <div class="org-name">Chief Executive Officer (CEO)</div>
                </div>
            </div>
            
            <div class="org-connector"></div>
            
            <!-- Executive Management -->
            <div class="org-level" style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center;">
                <div class="org-node">
                    <div class="org-title">Operations</div>
                    <div class="org-name">Chief Operations Officer (COO)</div>
                    <div style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">PhD Clinical Pharmacology</div>
                </div>
                <div class="org-node">
                    <div class="org-title">Research</div>
                    <div class="org-name">Head of Laboratory</div>
                    <div style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">PhD Molecular Biology</div>
                </div>
                <div class="org-node">
                    <div class="org-title">Quality</div>
                    <div class="org-name">Director of Quality Control</div>
                    <div style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Master's Chemistry</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ADMINISTRATIVE STRUCTURE -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-tag">Management Team</span>
            <h2 class="section-title">Administrative Structure</h2>
            <p class="section-subtitle">Qualified professionals in key leadership roles</p>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>POSITION</th>
                        <th>QUALIFICATION</th>
                        <th>MAJOR ROLE(S)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="position">Chief Executive Officer (CEO)</td>
                        <td class="qualification">Executive Leadership</td>
                        <td>Overall management and decision-making</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="position">Board of Directors</td>
                        <td class="qualification">Governance</td>
                        <td>Provision of strategic direction</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td class="position">Chief Operations Officer (COO)</td>
                        <td class="qualification">PhD in Clinical pharmacology, pharmacokinetics or drug development</td>
                        <td>Overseeing production, quality control, R&D coordination, regulatory compliance, technical staff, supply chain, facility operations, and safety</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td class="position">Finance & Administration Manager</td>
                        <td class="qualification">Master's degree in Business Administration or related field</td>
                        <td>Management of accounting and human resources</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td class="position">Director of Quality Control</td>
                        <td class="qualification">Master's degree in Chemistry/Biotechnology/Food Science and Technology</td>
                        <td>Overseeing of correct and efficient manufacturing-related operations</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td class="position">Director of Industrial Maintenance</td>
                        <td class="qualification">Master's degree in industrial maintenance, skilled in industrial automation, GMP and SOPs</td>
                        <td>Overseeing of installation, repair and maintenance of all facility's processing and research laboratory equipment</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td class="position">Head of (research) Laboratory</td>
                        <td class="qualification">PhD degree in molecular biology/Biochemistry</td>
                        <td>Overseeing lab research direction, staff supervision and lab operations compliance</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td class="position">Sales and Marketing Manager</td>
                        <td class="qualification">Bachelor degree in Marketing, Business Administration</td>
                        <td>Management of sales team, marketing staff and distributors</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td class="position">Digital Marketing and Creative Design Manager</td>
                        <td class="qualification">Bachelor degree in Marketing, communications and graphic design</td>
                        <td>Graphic design and mass media advertising</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td class="position">Supply and Logistic Officer</td>
                        <td class="qualification">Bachelor degree in Logistics, Supply Chain Management</td>
                        <td>Overseeing procurement control, inventory and distribution</td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td class="position">Corporate Lawyer</td>
                        <td class="qualification">Holding Law degree/Corporate Business Law</td>
                        <td>Overseeing all company's legal matters</td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td class="position">Director of Agricultural Operations</td>
                        <td class="qualification">Master's degree in agricultural engineering</td>
                        <td>Overseeing the crop/medicinal plant organic industrial farming</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- GOVERNANCE COMMITTEES -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-tag">Oversight</span>
            <h2 class="section-title">Governance Committees</h2>
            <p class="section-subtitle">Specialized committees ensuring comprehensive oversight and compliance</p>
        </div>

        <div class="committees-grid">
            <div class="committee-card">
                <div class="committee-icon"><i class="bi bi-diagram-3"></i></div>
                <h3 class="committee-title">Executive Management Committee (EMC)</h3>
                <ul class="committee-list">
                    <li><i class="bi bi-check-circle-fill"></i> Strategic planning & policy approval</li>
                    <li><i class="bi bi-check-circle-fill"></i> Oversees departmental coordination</li>
                    <li><i class="bi bi-check-circle-fill"></i> Budget & investment decisions</li>
                </ul>
            </div>

            <div class="committee-card">
                <div class="committee-icon"><i class="bi bi-shield"></i></div>
                <h3 class="committee-title">Governance & Compliance</h3>
                <ul class="committee-list">
                    <li><i class="bi bi-check-circle-fill"></i> Ensures regulatory compliance (ZAMRA, ZEMA, GMP, ISO)</li>
                    <li><i class="bi bi-check-circle-fill"></i> Oversees ethical, legal and risk management</li>
                    <li><i class="bi bi-check-circle-fill"></i> Monitors internal policies and transparency</li>
                </ul>
            </div>

            <div class="committee-card">
                <div class="committee-icon"><i class="bi bi-check2-circle"></i></div>
                <h3 class="committee-title">Quality Management Committee (QMC)</h3>
                <ul class="committee-list">
                    <li><i class="bi bi-check-circle-fill"></i> Ensures GMP/ISO compliance</li>
                    <li><i class="bi bi-check-circle-fill"></i> Reviews internal audits, CAPA, deviations</li>
                    <li><i class="bi bi-check-circle-fill"></i> Approves changes to SOPs/QA systems</li>
                </ul>
            </div>

            <div class="committee-card">
                <div class="committee-icon"><i class="bi bi-bank"></i></div>
                <h3 class="committee-title">Ethics & Compliance Committee</h3>
                <ul class="committee-list">
                    <li><i class="bi bi-check-circle-fill"></i> Monitors regulatory, legal, environmental compliance</li>
                    <li><i class="bi bi-check-circle-fill"></i> Prevents conflicts of interest</li>
                    <li><i class="bi bi-check-circle-fill"></i> Promotes ethical business conduct</li>
                </ul>
            </div>

            <div class="committee-card">
                <div class="committee-icon"><i class="bi bi-flask"></i></div>
                <h3 class="committee-title">Product Development/R&D Committee</h3>
                <ul class="committee-list">
                    <li><i class="bi bi-check-circle-fill"></i> Approves new formulations</li>
                    <li><i class="bi bi-check-circle-fill"></i> Oversees clinical validation</li>
                    <li><i class="bi bi-check-circle-fill"></i> Guides innovation strategy</li>
                </ul>
            </div>

            <div class="committee-card">
                <div class="committee-icon"><i class="bi bi-heart-pulse"></i></div>
                <h3 class="committee-title">Health & Safety Committee</h3>
                <ul class="committee-list">
                    <li><i class="bi bi-check-circle-fill"></i> Implements occupational health/safety policies</li>
                    <li><i class="bi bi-check-circle-fill"></i> Risk assessment & emergency preparedness</li>
                    <li><i class="bi bi-check-circle-fill"></i> Coordinates safety training</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- DEPARTMENTS & RESPONSIBILITIES -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-tag">Operations</span>
            <h2 class="section-title">Departments & Responsibilities</h2>
            <p class="section-subtitle">Specialized departments driving operational excellence</p>
        </div>

        <div class="departments-grid">
            <div class="department-card">
                <div class="department-icon"><i class="bi bi-gear"></i></div>
                <h3 class="department-title">Production Department</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Executes manufacturing</li>
                    <li><i class="bi bi-caret-right-fill"></i> Maintains batch records</li>
                    <li><i class="bi bi-caret-right-fill"></i> Manages equipment operations</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-check-circle"></i></div>
                <h3 class="department-title">Quality Assurance (QA)</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> GMP implementation & document control</li>
                    <li><i class="bi bi-caret-right-fill"></i> In-process & post-production checks</li>
                    <li><i class="bi bi-caret-right-fill"></i> Batch release authorization</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-laboratory"></i></div>
                <h3 class="department-title">Quality Control (QC)</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Lab testing (raw materials, finished products)</li>
                    <li><i class="bi bi-caret-right-fill"></i> Microbiology & chemical testing</li>
                    <li><i class="bi bi-caret-right-fill"></i> Stability testing and sampling</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-flask"></i></div>
                <h3 class="department-title">Research & Development (R&D)</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Product formulation & standardization</li>
                    <li><i class="bi bi-caret-right-fill"></i> Pilot production and validation</li>
                    <li><i class="bi bi-caret-right-fill"></i> Ingredient functionality research</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-file-text"></i></div>
                <h3 class="department-title">Regulatory Affairs</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Product registration (ZAMRA, MOH)</li>
                    <li><i class="bi bi-caret-right-fill"></i> Prepares dossiers & compliance docs</li>
                    <li><i class="bi bi-caret-right-fill"></i> Communicates with authorities</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-truck"></i></div>
                <h3 class="department-title">Supply Chain & Logistics</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Procurement of raw materials</li>
                    <li><i class="bi bi-caret-right-fill"></i> Warehouse & stock management</li>
                    <li><i class="bi bi-caret-right-fill"></i> Delivery/distribution planning</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-calculator"></i></div>
                <h3 class="department-title">Finance & Accounting</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Budgeting, salaries, audits</li>
                    <li><i class="bi bi-caret-right-fill"></i> Financial statements preparation</li>
                    <li><i class="bi bi-caret-right-fill"></i> L/Cs, T/Ts, bank relations</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-megaphone"></i></div>
                <h3 class="department-title">Marketing and Sales</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Branding & promotion strategy</li>
                    <li><i class="bi bi-caret-right-fill"></i> Manages media & partnerships</li>
                    <li><i class="bi bi-caret-right-fill"></i> Supports product launch</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-people"></i></div>
                <h3 class="department-title">Human Resources (HR)</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Staff recruitment & training</li>
                    <li><i class="bi bi-caret-right-fill"></i> Labor law compliance</li>
                    <li><i class="bi bi-caret-right-fill"></i> Performance evaluation</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-tools"></i></div>
                <h3 class="department-title">Facility Engineering & Maintenance</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Maintains cleanroom, HVAC, utilities</li>
                    <li><i class="bi bi-caret-right-fill"></i> Ensures GMP-compliant infrastructure</li>
                    <li><i class="bi bi-caret-right-fill"></i> Equipment calibration/repairs</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-shield"></i></div>
                <h3 class="department-title">Risk Management</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Identifies & monitors operational risks</li>
                    <li><i class="bi bi-caret-right-fill"></i> Develops mitigation strategies</li>
                    <li><i class="bi bi-caret-right-fill"></i> Ensures risk controls integration</li>
                </ul>
            </div>

            <div class="department-card">
                <div class="department-icon"><i class="bi bi-cpu"></i></div>
                <h3 class="department-title">IT & Data Systems</h3>
                <ul class="department-list">
                    <li><i class="bi bi-caret-right-fill"></i> Data integrity & cybersecurity</li>
                    <li><i class="bi bi-caret-right-fill"></i> LIMS/ERP systems management</li>
                    <li><i class="bi bi-caret-right-fill"></i> Digital compliance (21 CFR Part 11)</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- LICENSING & REGULATORY COMPLIANCE -->
<section class="section">
    <div class="section-container">
        <div class="licensing-section">
            <h2 style="font-size: 2.2rem; color: var(--accent); margin-bottom: 20px;">Licensing & Regulatory Compliance</h2>
            <p style="font-size: 1.1rem; margin-bottom: 40px; max-width: 800px;">AGF-PHYTOMED INDUSTRIES maintains comprehensive regulatory licensing across all applicable authorities</p>

            <div class="licensing-grid">
                <!-- PACRA -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-building"></i></div>
                    <div class="license-content">
                        <div class="license-title">Business Application & Registration</div>
                        <div class="license-authority">PACRA</div>
                    </div>
                </div>

                <!-- ZDA -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-award"></i></div>
                    <div class="license-content">
                        <div class="license-title">Investment Licence</div>
                        <div class="license-authority">ZDA</div>
                    </div>
                </div>

                <!-- ZEMA - EIA -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-tree"></i></div>
                    <div class="license-content">
                        <div class="license-title">Environmental Impact Assessment Certificate</div>
                        <div class="license-authority">ZEMA</div>
                    </div>
                </div>

                <!-- ZEMA - EPB -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-file-check"></i></div>
                    <div class="license-content">
                        <div class="license-title">Screening EPB Report Certificate</div>
                        <div class="license-authority">ZEMA</div>
                    </div>
                </div>

                <!-- ZEMA - Waste -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-trash"></i></div>
                    <div class="license-content">
                        <div class="license-title">Waste Management Licence</div>
                        <div class="license-authority">ZEMA</div>
                    </div>
                </div>

                <!-- ZMRA - Site -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="license-content">
                        <div class="license-title">Site Approval Certificate</div>
                        <div class="license-authority">ZMRA</div>
                    </div>
                </div>

                <!-- ZMRA - Product -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-box"></i></div>
                    <div class="license-content">
                        <div class="license-title">Product Registration</div>
                        <div class="license-authority">ZMRA</div>
                    </div>
                </div>

                <!-- ZAMRA - GMP -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-shield"></i></div>
                    <div class="license-content">
                        <div class="license-title">GMP Inspection</div>
                        <div class="license-authority">ZAMRA</div>
                    </div>
                </div>

                <!-- ZAMRA - Manufacturing -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-gear"></i></div>
                    <div class="license-content">
                        <div class="license-title">Manufacturing Licence</div>
                        <div class="license-authority">ZAMRA</div>
                    </div>
                </div>

                <!-- IMMIGRATION -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-passport"></i></div>
                    <div class="license-content">
                        <div class="license-title">Investor Permit</div>
                        <div class="license-authority">IMMIGRATION</div>
                    </div>
                </div>

                <!-- ZABS - Testing -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-flask"></i></div>
                    <div class="license-content">
                        <div class="license-title">Product Testing & Application</div>
                        <div class="license-authority">ZABS</div>
                    </div>
                </div>

                <!-- ZABS - Factory -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-building"></i></div>
                    <div class="license-content">
                        <div class="license-title">Factory Inspection</div>
                        <div class="license-authority">ZABS</div>
                    </div>
                </div>

                <!-- ZABS - Mark -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-patch-check"></i></div>
                    <div class="license-content">
                        <div class="license-title">Certification Mark</div>
                        <div class="license-authority">ZABS</div>
                    </div>
                </div>

                <!-- Local Health - Hygiene -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-droplet"></i></div>
                    <div class="license-content">
                        <div class="license-title">Premises Hygiene Clearance Certificate</div>
                        <div class="license-authority">Local Health Department</div>
                    </div>
                </div>

                <!-- Local Health - Food Safety -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-shield-check"></i></div>
                    <div class="license-content">
                        <div class="license-title">Food Safety Inspection/Compliance Certificate</div>
                        <div class="license-authority">Local Health Department</div>
                    </div>
                </div>

                <!-- Local Health - Handling -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-hand-index"></i></div>
                    <div class="license-content">
                        <div class="license-title">Food Handling/Safety Certificate</div>
                        <div class="license-authority">Local Health Department</div>
                    </div>
                </div>

                <!-- Local Health - Occupational -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-heart-pulse"></i></div>
                    <div class="license-content">
                        <div class="license-title">Occupational Health Risk Assessment Certificate</div>
                        <div class="license-authority">Local Health Department</div>
                    </div>
                </div>

                <!-- Local Health - Annual Verification -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="license-content">
                        <div class="license-title">Annual Health Compliance Verification</div>
                        <div class="license-authority">Local Health Department</div>
                    </div>
                </div>

                <!-- Land Title -->
                <div class="license-item">
                    <div class="license-icon"><i class="bi bi-file-earmark"></i></div>
                    <div class="license-content">
                        <div class="license-title">Land Title Deeds</div>
                        <div class="license-authority">Headmen, Chief, Ministry of Land</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ISO CERTIFICATIONS -->
<section class="section">
    <div class="section-container">
        <div class="iso-showcase">
            <h2 style="text-align: center; color: var(--primary); margin-bottom: 20px;">ISO Certification Roadmap</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 40px;">Comprehensive ISO certification program ensuring international quality standards</p>

            <div class="iso-grid">
                <div class="iso-card">
                    <div class="iso-number">ISO 9001</div>
                    <div class="iso-name">Quality Management System</div>
                    <div class="iso-type">Initial Certification & Annual Surveillance</div>
                </div>

                <div class="iso-card">
                    <div class="iso-number">ISO 22000</div>
                    <div class="iso-name">Food Safety Management</div>
                    <div class="iso-type">Food Safety Certification</div>
                </div>

                <div class="iso-card">
                    <div class="iso-number">ISO 14001</div>
                    <div class="iso-name">Environmental Governance</div>
                    <div class="iso-type">Initial Certification & Annual Surveillance</div>
                </div>

                <div class="iso-card">
                    <div class="iso-number">ISO 45001</div>
                    <div class="iso-name">Occupational Health & Safety</div>
                    <div class="iso-type">Initial Certification & Annual Surveillance</div>
                </div>

                <div class="iso-card">
                    <div class="iso-number">ISO 14644</div>
                    <div class="iso-name">Cleanroom Qualification</div>
                    <div class="iso-type">ISO 5/Room x 10 & Annual Re-certification</div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Scroll Progress
window.addEventListener('scroll', () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.getElementById('scrollProgress').style.width = scrolled + '%';
    
    // Back to top button
    const backToTop = document.getElementById('backToTop');
    if (winScroll > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

// Back to top
document.getElementById('backToTop').addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Intersection Observer for animations
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    
    // Observe elements
    document.querySelectorAll('.section-header, .org-node, .committee-card, .department-card, .license-item, .iso-card').forEach(el => {
        observer.observe(el);
    });
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>