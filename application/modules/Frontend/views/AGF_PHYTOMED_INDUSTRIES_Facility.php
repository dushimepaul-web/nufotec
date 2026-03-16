VOIC MA PAGE AGF-PHYTOMED INDUSTRIES FACILITY <?php include VIEWPATH.'includes/frontend/Header.php'; ?>

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
    --blue: #2563eb;
    --indigo: #4f46e5;
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
    content: '🏭';
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
   FACILITY OVERVIEW
   ============================================ */
.facility-overview {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--border-radius-xl);
    padding: 60px;
    color: white;
    margin-bottom: 60px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.facility-overview::before {
    content: '📐';
    position: absolute;
    right: 20px;
    bottom: 20px;
    font-size: 180px;
    opacity: 0.1;
    transform: rotate(10deg);
}

.facility-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-top: 40px;
    position: relative;
    z-index: 2;
}

.facility-stat {
    text-align: center;
    padding: 20px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius-md);
    border: 1px solid rgba(255,255,255,0.2);
}

.facility-stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--accent);
    margin-bottom: 5px;
}

.facility-stat-label {
    font-size: 1rem;
    opacity: 0.9;
}

/* ============================================
   FACILITY LAYOUT TABLE
   ============================================ */
.layout-table-container {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    margin: 50px 0;
    border: 1px solid var(--gray-light);
}

.layout-table {
    width: 100%;
    border-collapse: collapse;
}

.layout-table th {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 15px;
    font-size: 0.95rem;
    font-weight: 600;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,0.1);
}

.layout-table th:last-child {
    border-right: none;
}

.layout-table td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--gray-light);
    color: var(--dark);
    font-size: 0.9rem;
    vertical-align: top;
}

.layout-table tr:last-child td {
    border-bottom: none;
}

.layout-table tr:hover td {
    background: var(--accent-soft);
}

.layout-section {
    background: var(--primary-soft);
    font-weight: 700;
    color: var(--primary);
}

.layout-section td {
    background: var(--primary-soft);
    font-weight: 700;
    color: var(--primary);
    font-size: 1rem;
}

.layout-highlight {
    color: var(--accent-hover);
    font-weight: 600;
}

.layout-dim {
    color: var(--gray);
    font-style: italic;
}

/* ============================================
   FACILITY BLOCKS
   ============================================ */
.facility-blocks {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin: 50px 0;
}

.facility-block {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid transparent;
}

.facility-block:hover {
    transform: translateY(-10px);
    border-color: var(--accent);
    box-shadow: var(--shadow-hover);
}

.block-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.block-icon {
    width: 50px;
    height: 50px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary-dark);
}

.block-title {
    font-size: 1.3rem;
    font-weight: 700;
    flex: 1;
}

.block-subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
}

.block-content {
    padding: 25px;
}

.block-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--gray-light);
}

.block-stat {
    flex: 1;
    min-width: 80px;
    text-align: center;
}

.block-stat-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary);
}

.block-stat-label {
    font-size: 0.8rem;
    color: var(--gray);
    text-transform: uppercase;
}

.block-areas {
    list-style: none;
    padding: 0;
}

.block-areas li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed var(--gray-light);
}

.block-areas li:last-child {
    border-bottom: none;
}

.area-name {
    font-size: 0.95rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.area-name i {
    color: var(--accent);
    font-size: 0.9rem;
}

.area-dim {
    font-size: 0.9rem;
    color: var(--primary);
    font-weight: 600;
}

/* ============================================
   SITE STATISTICS
   ============================================ */
.site-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin: 60px 0;
}

.site-stat-card {
    background: linear-gradient(135deg, #ffffff, var(--gray-soft));
    border-radius: var(--border-radius-lg);
    padding: 35px 25px;
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid transparent;
}

.site-stat-card:hover {
    transform: translateY(-10px);
    border-color: var(--accent);
    box-shadow: var(--shadow-hover);
}

.site-stat-icon {
    width: 80px;
    height: 80px;
    background: var(--primary-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2.5rem;
    color: var(--primary);
    transition: var(--transition);
}

.site-stat-card:hover .site-stat-icon {
    background: var(--accent);
    color: white;
    transform: rotateY(360deg);
}

.site-stat-number {
    font-size: 2.8rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 5px;
    line-height: 1;
}

.site-stat-number small {
    font-size: 1rem;
    color: var(--gray);
}

.site-stat-label {
    font-size: 1.1rem;
    color: var(--gray);
    font-weight: 500;
}

/* ============================================
   FACILITY MAP VISUALIZATION
   ============================================ */
.facility-map {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 40px;
    box-shadow: var(--shadow-lg);
    margin: 60px 0;
    border: 2px solid var(--gray-light);
}

.map-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    justify-content: center;
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid var(--gray-light);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.legend-color {
    width: 25px;
    height: 25px;
    border-radius: 5px;
}

.legend-color.reception { background: #f97316; }
.legend-color.offices { background: #3b82f6; }
.legend-color.raw-materials { background: #8b5cf6; }
.legend-color.processing { background: #10b981; }
.legend-color.packaging { background: #d4af37; }
.legend-color.laboratory { background: #ec4899; }
.legend-color.utilities { background: #6b7280; }
.legend-color.residential { background: #ef4444; }

/* ============================================
   DIMENSIONS TABLE
   ============================================ */
.dimensions-table {
    background: white;
    border-radius: var(--border-radius-md);
    overflow: hidden;
    margin: 30px 0;
    box-shadow: var(--shadow);
}

.dimensions-table table {
    width: 100%;
    border-collapse: collapse;
}

.dimensions-table th {
    background: var(--primary);
    color: white;
    padding: 12px;
    font-size: 0.9rem;
}

.dimensions-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--gray-light);
    font-size: 0.9rem;
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */
@media (max-width: 992px) {
    .section {
        padding: 60px 0;
    }
    
    .facility-overview {
        padding: 40px;
    }
    
    .layout-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}

@media (max-width: 768px) {
    .facility-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .site-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .block-header {
        flex-direction: column;
        text-align: center;
    }
    
    .facility-map {
        padding: 20px;
    }
}

@media (max-width: 576px) {
    .page-hero-title {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .facility-stats {
        grid-template-columns: 1fr;
    }
    
    .site-stats {
        grid-template-columns: 1fr;
    }
    
    .facility-blocks {
        grid-template-columns: 1fr;
    }
    
    .block-stats {
        flex-direction: column;
        gap: 10px;
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
        <h1 class="page-hero-title">AGF-PHYTOMED <span>INDUSTRIES</span></h1>
        <p class="page-hero-subtitle">State-of-the-Art GMP-Compliant Manufacturing Facility - Complete Layout Plan Report</p>
    </div>
</section>

<!-- FACILITY OVERVIEW -->
<section class="section">
    <div class="section-container">
        <div class="facility-overview">
            <h2 style="font-size: 2.2rem; color: var(--accent); margin-bottom: 20px;">Facility Overview</h2>
            <p style="font-size: 1.2rem; line-height: 1.8; margin-bottom: 30px;">Comprehensive industrial complex designed for GMP-compliant phytopharmaceutical manufacturing</p>
            
            <div class="facility-stats">
                <div class="facility-stat">
                    <div class="facility-stat-number">13,640 m²</div>
                    <div class="facility-stat-label">Total Built Area</div>
                </div>
                <div class="facility-stat">
                    <div class="facility-stat-number">30,000 m²</div>
                    <div class="facility-stat-label">Site Fencing</div>
                </div>
                <div class="facility-stat">
                    <div class="facility-stat-number">8 m</div>
                    <div class="facility-stat-label">Max Ceiling Height</div>
                </div>
                <div class="facility-stat">
                    <div class="facility-stat-number">18</div>
                    <div class="facility-stat-label">Production Zones</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FACILITY LAYOUT TABLE -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-tag">Layout Plan</span>
            <h2 class="section-title">Detailed Facility Layout Report</h2>
            <p class="section-subtitle">Complete specifications of all industrial areas with dimensions and capacities</p>
        </div>

        <div class="layout-table-container">
            <table class="layout-table">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Area/Space</th>
                        <th>Type</th>
                        <th>L (m)</th>
                        <th>W (m)</th>
                        <th>A (m²)</th>
                        <th>H (m)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- SECTION I: RECEPTION -->
                    <tr class="layout-section">
                        <td colspan="7">I. RECEPTION</td>
                    </tr>
                    <tr>
                        <td>I</td>
                        <td>Reception</td>
                        <td></td>
                        <td>5</td>
                        <td>4</td>
                        <td>20.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Waiting seats</td>
                        <td></td>
                        <td>5</td>
                        <td>2.5</td>
                        <td>12.5</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Staff gathering area</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>150.0</td>
                        <td>4</td>
                    </tr>

                    <!-- SECTION II: OFFICES -->
                    <tr class="layout-section">
                        <td colspan="7">II. OFFICES</td>
                    </tr>
                    <tr>
                        <td>II</td>
                        <td>Self-contained and shared Offices</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>370.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Staff Meeting room</td>
                        <td></td>
                        <td>11</td>
                        <td>8</td>
                        <td>88.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Toilets</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>62.0</td>
                        <td>4</td>
                    </tr>

                    <!-- SECTION III: DOWNLOADING AREA -->
                    <tr class="layout-section">
                        <td colspan="7">III. DOWNLOADING AREA</td>
                    </tr>
                    <tr>
                        <td>III</td>
                        <td>Downloading Area</td>
                        <td></td>
                        <td>43</td>
                        <td>4</td>
                        <td>172.0</td>
                        <td>6</td>
                    </tr>

                    <!-- SECTION IV: RAW MATERIALS -->
                    <tr class="layout-section">
                        <td colspan="7">IV. RAW MATERIALS</td>
                    </tr>
                    <tr>
                        <td>IV</td>
                        <td>Sample storage</td>
                        <td>HS</td>
                        <td>9</td>
                        <td>5</td>
                        <td>45.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Wraps shelves keeping</td>
                        <td>HS</td>
                        <td>6</td>
                        <td>5</td>
                        <td>30.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Reagent chemical storage</td>
                        <td>2F</td>
                        <td>15</td>
                        <td>10</td>
                        <td>150.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Raw material storage</td>
                        <td>2F</td>
                        <td>15</td>
                        <td>10</td>
                        <td>150.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Dry Raw material storage</td>
                        <td>2F</td>
                        <td>15</td>
                        <td>10</td>
                        <td>150.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Quarantine storage</td>
                        <td>HS</td>
                        <td>5</td>
                        <td>10</td>
                        <td>50.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Equipment storage</td>
                        <td>HS</td>
                        <td>3</td>
                        <td>10</td>
                        <td>30.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Property store</td>
                        <td>HS</td>
                        <td>9</td>
                        <td>5</td>
                        <td>45.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Gardener store</td>
                        <td>HS</td>
                        <td>5</td>
                        <td>5</td>
                        <td>25.0</td>
                        <td>6</td>
                    </tr>

                    <!-- SECTION V: POST HARVEST & PRE-PROCESSING -->
                    <tr class="layout-section">
                        <td colspan="7">V. POST HARVEST & PRE-PROCESSING</td>
                    </tr>
                    <tr>
                        <td>V</td>
                        <td>Extracts</td>
                        <td></td>
                        <td>40</td>
                        <td>10</td>
                        <td>400.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Soymilk powder</td>
                        <td></td>
                        <td>40</td>
                        <td>30</td>
                        <td>1,200.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Spirulina algae</td>
                        <td></td>
                        <td>40</td>
                        <td>10</td>
                        <td>400.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Fruit juices</td>
                        <td></td>
                        <td>40</td>
                        <td>10</td>
                        <td>400.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Reserved hall</td>
                        <td></td>
                        <td>40</td>
                        <td>10</td>
                        <td>400.0</td>
                        <td>8</td>
                    </tr>

                    <!-- SECTION VI: PROCESSING AND PACKAGING -->
                    <tr class="layout-section">
                        <td colspan="7">VI. PROCESSING AND PACKAGING</td>
                    </tr>
                    <tr>
                        <td>VI</td>
                        <td>Extracts</td>
                        <td></td>
                        <td>70</td>
                        <td>10</td>
                        <td>700.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Soymilk powder</td>
                        <td></td>
                        <td>70</td>
                        <td>30</td>
                        <td>2,100.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Spirulina algae</td>
                        <td></td>
                        <td>70</td>
                        <td>10</td>
                        <td>700.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Fruit juices</td>
                        <td></td>
                        <td>70</td>
                        <td>10</td>
                        <td>700.0</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Reserved hall</td>
                        <td></td>
                        <td>70</td>
                        <td>10</td>
                        <td>700.0</td>
                        <td>8</td>
                    </tr>

                    <!-- SECTION VII: AISLE -->
                    <tr class="layout-section">
                        <td colspan="7">VII. AISLE</td>
                    </tr>
                    <tr>
                        <td>VII</td>
                        <td>All around the processing halls</td>
                        <td></td>
                        <td></td>
                        <td>2.5</td>
                        <td></td>
                        <td>8</td>
                    </tr>

                    <!-- SECTION VIII: FINISHED PRODUCTS -->
                    <tr class="layout-section">
                        <td colspan="7">VIII. FINISHED PRODUCTS</td>
                    </tr>
                    <tr>
                        <td>VIII</td>
                        <td>Industrial cold room</td>
                        <td>HS</td>
                        <td>17.5</td>
                        <td>10</td>
                        <td>175.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Finished product store</td>
                        <td>2F</td>
                        <td>13</td>
                        <td>10</td>
                        <td>130.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Warehouse</td>
                        <td>2F</td>
                        <td>15</td>
                        <td>10</td>
                        <td>150.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Quarantine storage</td>
                        <td>HS</td>
                        <td>5</td>
                        <td>10</td>
                        <td>50.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Waiting seats</td>
                        <td></td>
                        <td>5</td>
                        <td>5</td>
                        <td>25.0</td>
                        <td>4</td>
                    </tr>

                    <!-- SECTION IX: INDUSTRIAL COLD ROOM -->
                    <tr class="layout-section">
                        <td colspan="7">IX. INDUSTRIAL COLD ROOM</td>
                    </tr>
                    <tr>
                        <td>IX</td>
                        <td>Industrial cold room</td>
                        <td></td>
                        <td>17.5</td>
                        <td>10</td>
                        <td>175.0</td>
                        <td>6</td>
                    </tr>

                    <!-- SECTION X: PACKAGING AND UPLOADING -->
                    <tr class="layout-section">
                        <td colspan="7">X. PACKAGING AND UPLOADING</td>
                    </tr>
                    <tr>
                        <td>X</td>
                        <td>Packaging and uploading</td>
                        <td></td>
                        <td>53.5</td>
                        <td>4</td>
                        <td>214.0</td>
                        <td>6</td>
                    </tr>

                    <!-- SECTION XI: CHANGING ROOMS -->
                    <tr class="layout-section">
                        <td colspan="7">XI. CHANGING ROOMS</td>
                    </tr>
                    <tr>
                        <td>XI</td>
                        <td>Changing rooms 1</td>
                        <td></td>
                        <td>17.5</td>
                        <td>13</td>
                        <td>227.5</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Changing rooms 2</td>
                        <td></td>
                        <td>13</td>
                        <td>12.5</td>
                        <td>162.5</td>
                        <td>4</td>
                    </tr>

                    <!-- SECTION XII: LABORATORIES -->
                    <tr class="layout-section">
                        <td colspan="7">XII. LABORATORIES</td>
                    </tr>
                    <tr>
                        <td>XII</td>
                        <td>Analytical Chemistry</td>
                        <td></td>
                        <td>10</td>
                        <td>5</td>
                        <td>50.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Microbiology</td>
                        <td></td>
                        <td>10</td>
                        <td>5</td>
                        <td>50.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Formulation / R&D</td>
                        <td></td>
                        <td>10</td>
                        <td>5</td>
                        <td>50.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Stability & Shelf-Life</td>
                        <td></td>
                        <td>10</td>
                        <td>5</td>
                        <td>50.0</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Raw materials/Plant identification</td>
                        <td></td>
                        <td>10</td>
                        <td>5</td>
                        <td>50.0</td>
                        <td>6</td>
                    </tr>

                    <!-- SECTION XIII: LABORATORY ANIMAL BREEDING -->
                    <tr class="layout-section">
                        <td colspan="7">XIII. LABORATORY ANIMAL BREEDING FACILITY</td>
                    </tr>
                    <tr>
                        <td>XIII</td>
                        <td>Animal Breeding Facility</td>
                        <td></td>
                        <td>20</td>
                        <td>15</td>
                        <td>300.0</td>
                        <td>6</td>
                    </tr>

                    <!-- SECTION XIV: UTILITIES AND TECHNICALS -->
                    <tr class="layout-section">
                        <td colspan="7">XIV. UTILITIES AND TECHNICALS</td>
                    </tr>
                    <tr>
                        <td>XIV</td>
                        <td>HVAC</td>
                        <td></td>
                        <td>7</td>
                        <td>6.5</td>
                        <td>45.5</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Water system</td>
                        <td></td>
                        <td>7</td>
                        <td>5.5</td>
                        <td>38.5</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Compressors</td>
                        <td></td>
                        <td>7</td>
                        <td>5.5</td>
                        <td>38.5</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Power back up</td>
                        <td></td>
                        <td>7</td>
                        <td>5.5</td>
                        <td>38.5</td>
                        <td>4</td>
                    </tr>

                    <!-- SECTION XV: WASTE AREA -->
                    <tr class="layout-section">
                        <td colspan="7">XV. WASTE AREA</td>
                    </tr>
                    <tr>
                        <td>XV</td>
                        <td>Waste storage</td>
                        <td></td>
                        <td>10</td>
                        <td>10</td>
                        <td>100.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Waste management zone</td>
                        <td></td>
                        <td>20</td>
                        <td>10</td>
                        <td>200.0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Waste segregation bins</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <!-- SECTION XVI: PARKING -->
                    <tr class="layout-section">
                        <td colspan="7">XVI. PARKING</td>
                    </tr>
                    <tr>
                        <td>XVI</td>
                        <td>Staff and visitors parking</td>
                        <td></td>
                        <td>78</td>
                        <td>5</td>
                        <td>390.0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Trucks parking 1</td>
                        <td></td>
                        <td>20</td>
                        <td>8</td>
                        <td>160.0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Trucks parking 2</td>
                        <td></td>
                        <td>19</td>
                        <td>8</td>
                        <td>152.0</td>
                        <td></td>
                    </tr>

                    <!-- TOTALS -->
                    <tr class="layout-section">
                        <td colspan="7">TOTAL INDUSTRIAL BUILDING</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="5"><strong>Total Built Area (Industrial)</strong></td>
                        <td><strong>13,640.0 m²</strong></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="5">Site Fencing</td>
                        <td>30,000.0 m²</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- STAFF RESIDENTIAL UNITS -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-tag">Accommodation</span>
            <h2 class="section-title">Staff Residential Units</h2>
            <p class="section-subtitle">Complete residential facilities for staff accommodation</p>
        </div>

        <div class="layout-table-container">
            <table class="layout-table">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Area/Space</th>
                        <th>L (m)</th>
                        <th>W (m)</th>
                        <th>A (m²)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="layout-section">
                        <td colspan="5">XVII. STAFF RESIDENTIAL UNITS</td>
                    </tr>
                    <tr>
                        <td>XVII</td>
                        <td>Seating room</td>
                        <td>4</td>
                        <td>2.7</td>
                        <td>10.8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Dining room + kitchen</td>
                        <td>2.7</td>
                        <td>2.5</td>
                        <td>6.8</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Toilet</td>
                        <td>2</td>
                        <td>1.5</td>
                        <td>3.0</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Bedroom</td>
                        <td>3.75</td>
                        <td>2.5</td>
                        <td>9.4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Veranda</td>
                        <td>5</td>
                        <td>1.5</td>
                        <td>7.5</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Restaurant + Kitchen</td>
                        <td>8</td>
                        <td>5</td>
                        <td>40.0</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Supermarket</td>
                        <td>6</td>
                        <td>3</td>
                        <td>18.0</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Coffee space</td>
                        <td>5.5</td>
                        <td>3</td>
                        <td>16.5</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Parking</td>
                        <td>50</td>
                        <td>5</td>
                        <td>250.0</td>
                    </tr>
                    <tr class="layout-section">
                        <td colspan="4"><strong>Total Residential Built Area</strong></td>
                        <td><strong>620.0 m²</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">Residential Fencing</td>
                        <td>2,800 m²</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- GARAGE FACILITY -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-tag">Maintenance</span>
            <h2 class="section-title">Garage & Vehicle Maintenance Facility</h2>
        </div>

        <div class="layout-table-container">
            <table class="layout-table">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Area/Space</th>
                        <th>L (m)</th>
                        <th>W (m)</th>
                        <th>A (m²)</th>
                        <th>H (m)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="layout-section">
                        <td colspan="6">XVIII. GARAGE</td>
                    </tr>
                    <tr>
                        <td>XVIII</td>
                        <td>Reception</td>
                        <td>5</td>
                        <td>5</td>
                        <td>25.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Spare parts and storage</td>
                        <td>8</td>
                        <td>5</td>
                        <td>40.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Utility and technical room</td>
                        <td>5</td>
                        <td>5</td>
                        <td>25.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Mechanical workshop</td>
                        <td>10</td>
                        <td>8.5</td>
                        <td>85.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Tire and wheel services</td>
                        <td>10</td>
                        <td>10</td>
                        <td>100.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Quick service area</td>
                        <td>10</td>
                        <td>10</td>
                        <td>100.0</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Car wash service</td>
                        <td>40</td>
                        <td>20</td>
                        <td>800.0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Toilets</td>
                        <td>5</td>
                        <td>1.5</td>
                        <td>7.5</td>
                        <td>4</td>
                    </tr>
                    <tr class="layout-section">
                        <td colspan="4"><strong>Total Garage Built Area</strong></td>
                        <td><strong>1,200.0 m²</strong></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">Garage Fencing</td>
                        <td>1,200 m²</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- SITE STATISTICS SUMMARY -->
<section class="section">
    <div class="section-container">
        <div class="site-stats">
            <div class="site-stat-card">
                <div class="site-stat-icon"><i class="bi bi-building"></i></div>
                <div class="site-stat-number">13,640</div>
                <div class="site-stat-label">Industrial Built Area (m²)</div>
            </div>
            <div class="site-stat-card">
                <div class="site-stat-icon"><i class="bi bi-house-door"></i></div>
                <div class="site-stat-number">620</div>
                <div class="site-stat-label">Residential Built Area (m²)</div>
            </div>
            <div class="site-stat-card">
                <div class="site-stat-icon"><i class="bi bi-tools"></i></div>
                <div class="site-stat-number">1,200</div>
                <div class="site-stat-label">Garage Built Area (m²)</div>
            </div>
            <div class="site-stat-card">
                <div class="site-stat-icon"><i class="bi bi-grid-3x3"></i></div>
                <div class="site-stat-number">34,000</div>
                <div class="site-stat-label">Total Fencing (m²)</div>
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
    document.querySelectorAll('.section-header, .facility-block, .site-stat-card').forEach(el => {
        observer.observe(el);
    });
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>