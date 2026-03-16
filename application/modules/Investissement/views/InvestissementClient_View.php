<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
    <style>
        :root {
            --primary: #0f4c3a;
            --primary-light: #1a6b52;
            --primary-dark: #0a3326;
            --accent: #d4af37;
            --accent-hover: #b8962e;
            --accent-light: #f4e4a6;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #dee2e6;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius: 16px;
            --radius-sm: 12px;
            --radius-lg: 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f1;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }

        /* ============================================
           HERO SECTION AMÉLIORÉ
           ============================================ */
        .projects-hero {
            background: linear-gradient(135deg, rgba(10, 51, 38, 0.85) 0%, rgba(15, 76, 58, 0.9) 50%, rgba(10, 51, 38, 0.95) 100%), 
                        url('https://images.unsplash.com/photo-1507413245164-6160d8298b31?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 180px 0 120px;
            position: relative;
            overflow: hidden;
        }

        .projects-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4af37' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.4;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .badge-invest {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: var(--primary-dark);
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            animation: pulse-badge 2s infinite;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @keyframes pulse-badge {
            0%, 100% { box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3); }
            50% { box-shadow: 0 4px 25px rgba(212, 175, 55, 0.5); }
        }

        .badge-invest i {
            font-size: 16px;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            line-height: 1.1;
            margin-bottom: 25px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .text-accent {
            color: var(--accent);
            position: relative;
            display: inline-block;
        }

        .text-accent::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), transparent);
            border-radius: 2px;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.8);
            max-width: 600px;
            margin-bottom: 40px;
            line-height: 1.8;
        }

        .hero-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 50px;
        }

        .stat-item {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 25px;
            border-radius: var(--radius);
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-hover));
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .stat-item:hover::before {
            transform: scaleX(1);
        }

        .stat-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-5px);
        }

        .stat-item h3 {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 5px;
            font-weight: 800;
            font-family: 'Inter', sans-serif;
        }

        .stat-item span {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-cta {
            margin-top: 40px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: var(--primary-dark);
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            border: none;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
            color: var(--primary-dark);
        }

        .btn-hero-secondary {
            background: transparent;
            color: white;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            border: 2px solid rgba(255,255,255,0.3);
        }

        .btn-hero-secondary:hover {
            background: white;
            color: var(--primary-dark);
            border-color: white;
        }

        /* ============================================
           SECTION PROJETS AMÉLIORÉE
           ============================================ */
        .projects-grid-section {
            padding: 100px 0;
            position: relative;
        }

        .section-intro {
            margin-bottom: 60px;
        }

        .section-intro h2 {
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-intro h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        .section-intro p {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 30px auto 0;
        }

        /* Filtres de projets */
        .project-filters {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 12px 24px;
            background: white;
            border: 2px solid var(--gray-light);
            border-radius: 50px;
            font-weight: 600;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Cartes Projets Invest */
        .invest-project-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            transition: var(--transition);
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .invest-project-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .invest-project-card.featured {
            border: 2px solid var(--accent);
            position: relative;
        }

        .invest-project-card.featured::before {
            content: 'POPULAIRE';
            position: absolute;
            top: 20px;
            right: -35px;
            background: var(--accent);
            color: var(--primary-dark);
            padding: 5px 40px;
            font-size: 11px;
            font-weight: 800;
            transform: rotate(45deg);
            z-index: 10;
            letter-spacing: 1px;
        }

        .card-img {
            height: 240px;
            position: relative;
            overflow: hidden;
        }

        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .invest-project-card:hover .card-img img {
            transform: scale(1.1);
        }

        .category-tag {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.95);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            color: var(--primary);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 5;
        }

        .category-tag i {
            font-size: 14px;
        }

        .card-content {
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-content h4 {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 12px;
            font-size: 1.3rem;
            line-height: 1.3;
        }

        .card-content p {
            color: var(--gray);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
            flex: 1;
        }

        .project-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-light);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--gray);
        }

        .meta-item i {
            color: var(--accent);
        }

        /* Barre de progression améliorée */
        .funding-bar {
            background: #f0f0f0;
            padding: 20px;
            border-radius: var(--radius);
            margin: 20px 0;
        }

        .funding-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .funding-percentage {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
        }

        .funding-label {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .progress {
            height: 10px;
            border-radius: 10px;
            background: #e0e0e0;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 50%, var(--accent) 100%);
            border-radius: 10px;
            position: relative;
            transition: width 1s ease;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .funding-details {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            font-size: 13px;
        }

        .funding-raised {
            color: var(--primary);
            font-weight: 700;
        }

        .funding-goal {
            color: var(--gray);
        }

        .investors-count {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--gray);
        }

        .investors-avatars {
            display: flex;
        }

        .investors-avatars img {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid white;
            margin-left: -8px;
            object-fit: cover;
        }

        .investors-avatars img:first-child {
            margin-left: 0;
        }

        .btn-invest-now {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 15px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-invest-now::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-invest-now:hover::before {
            left: 100%;
        }

        .btn-invest-now:hover {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
        }

        .btn-invest-now i {
            transition: transform 0.3s;
        }

        .btn-invest-now:hover i {
            transform: rotate(90deg);
        }

        /* Modal d'investissement */
        .invest-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .invest-modal-overlay.active {
            display: flex;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .invest-modal {
            background: white;
            border-radius: var(--radius-lg);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.4s;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 30px;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .investment-tiers {
            display: grid;
            gap: 15px;
            margin: 25px 0;
        }

        .tier-option {
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            padding: 20px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .tier-option:hover, .tier-option.selected {
            border-color: var(--accent);
            background: rgba(212, 175, 55, 0.05);
        }

        .tier-option.selected {
            border-width: 2px;
            background: rgba(212, 175, 55, 0.1);
        }

        .tier-radio {
            width: 24px;
            height: 24px;
            border: 2px solid var(--gray-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .tier-option.selected .tier-radio {
            border-color: var(--accent);
            background: var(--accent);
        }

        .tier-radio i {
            color: white;
            font-size: 12px;
            display: none;
        }

        .tier-option.selected .tier-radio i {
            display: block;
        }

        .tier-info h5 {
            margin: 0 0 5px 0;
            color: var(--primary-dark);
        }

        .tier-info p {
            margin: 0;
            font-size: 13px;
            color: var(--gray);
        }

        .tier-amount {
            margin-left: auto;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
        }

        .custom-amount {
            margin-top: 20px;
        }

        .custom-amount label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom span {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-weight: 600;
        }

        .input-group-custom input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-size: 16px;
            transition: var(--transition);
        }

        .input-group-custom input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn-submit-invest {
            width: 100%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: var(--primary-dark);
            border: none;
            padding: 18px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
        }

        .btn-submit-invest:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
        }

        /* Section Témoignages */
        .testimonials-section {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 100px 0;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .testimonial-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius);
            padding: 40px;
            text-align: center;
        }

        .testimonial-text {
            font-size: 1.2rem;
            font-style: italic;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .testimonial-author img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--accent);
        }

        .testimonial-author-info h5 {
            margin: 0;
            color: var(--accent);
        }

        .testimonial-author-info span {
            font-size: 14px;
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .stat-item h3 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .projects-hero {
                padding: 120px 0 80px;
            }
            
            .hero-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-cta {
                flex-direction: column;
            }
            
            .btn-hero-primary, .btn-hero-secondary {
                width: 100%;
                justify-content: center;
            }
            
            .project-filters {
                flex-direction: column;
                align-items: center;
            }
            
            .filter-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        /* Animations au scroll */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

    <!-- HERO SECTION COMPACT -->
<section class="projects-hero" style="max-height: 300px; padding: 40px 0; display: flex; align-items: center;">
    <div class="container hero-content" style="width: 100%;">
        <div class="row align-items-center">
            <div class="col-lg-12 text-center">
                <span class="badge-invest" style="padding: 6px 14px; font-size: 12px; margin-bottom: 15px;">
                    <i class="bi bi-graph-up-arrow"></i> 
                    Opportunités 2026
                </span>
                <h1 class="hero-title fw-bold text-white" style="font-size: clamp(1.5rem, 4vw, 2.5rem); margin-bottom: 10px;">
                    Investissez dans l'excellence <span class="text-accent">Phytomédicale</span>
                </h1>
                <p class="hero-subtitle" style="font-size: 14px; margin-bottom: 15px; max-width: 700px; margin-left: auto; margin-right: auto;">
                    AGF Phytomed Industries transforme la biodiversité burundaise en solutions médicales de classe mondiale.
                </p>
                
            </div>
        </div>
    </div>
</section>

    <!-- SECTION PROJETS -->
    <section class="projects-grid-section" id="projets">
        <div class="container">
            <div class="section-intro text-center animate-on-scroll">
                <h2>Projets Ouverts au Financement</h2>
                <p>
                    Sélectionnez un projet pour consulter les détails techniques, les paliers d'investissement 
                    et les projections financières détaillées.
                </p>
            </div>

            <!-- Filtres -->
            <div class="project-filters animate-on-scroll">
                <button class="filter-btn active" onclick="filterProjects('all')">
                    <i class="bi bi-grid-3x3-gap"></i> Tous les projets
                </button>
                <button class="filter-btn" onclick="filterProjects('industrie')">
                    <i class="bi bi-gear"></i> Industrie
                </button>
                <button class="filter-btn" onclick="filterProjects('agriculture')">
                    <i class="bi bi-tree"></i> Agriculture
                </button>
                <button class="filter-btn" onclick="filterProjects('digital')">
                    <i class="bi bi-laptop"></i> Digital
                </button>
            </div>

            <div class="row g-4">
                <!-- Projet 1 -->
                <div class="col-lg-4 project-item" data-category="industrie">
                    <div class="invest-project-card featured animate-on-scroll">
                        <div class="card-img">
                            <img src="https://images.unsplash.com/photo-1579165466541-71e2247fb5c5?q=80&w=2070&auto=format&fit=crop" alt="Laboratoire extraction">
                            <span class="category-tag">
                                <i class="bi bi-gear"></i> Industrie
                            </span>
                        </div>
                        <div class="card-content">
                            <h4>Unité d'Extraction Haute Précision</h4>
                            <p>
                                Acquisition de machines d'extraction au CO2 supercritique pour une pureté 
                                absolue des principes actifs. Technologie allemande de dernière génération.
                            </p>
                            
                            <div class="project-meta">
                                <div class="meta-item">
                                    <i class="bi bi-calendar"></i>
                                    <span>Durée: 24 mois</span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Rendement: 22%</span>
                                </div>
                            </div>

                            <div class="funding-bar">
                                <div class="funding-header">
                                    <span class="funding-label">Financement</span>
                                    <span class="funding-percentage">65%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 65%"></div>
                                </div>
                                <div class="funding-details">
                                    <span class="funding-raised">$162,500 collectés</span>
                                    <span class="funding-goal">Objectif: $250,000</span>
                                </div>
                            </div>

                            <div class="investors-count">
                                <div class="investors-avatars">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop" alt="Investisseur">
                                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop" alt="Investisseur">
                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop" alt="Investisseur">
                                </div>
                                <span>+42 investisseurs</span>
                            </div>

                            <button class="btn-invest-now" onclick="openInvestModal('Unité d\'Extraction Haute Précision', 250000, 162500, 22)">
                                Choisir ce Projet <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Projet 2 -->
                <div class="col-lg-4 project-item" data-category="agriculture">
                    <div class="invest-project-card animate-on-scroll">
                        <div class="card-img">
                            <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?q=80&w=2070&auto=format&fit=crop" alt="Plantation Artemisia">
                            <span class="category-tag">
                                <i class="bi bi-tree"></i> Agriculture
                            </span>
                        </div>
                        <div class="card-content">
                            <h4>Extension Vergers Artemisia Annua</h4>
                            <p>
                                Expansion de 25 hectares de plantations d'Artemisia annua pour répondre 
                                à la demande croissante du marché régional de lutte contre le paludisme.
                            </p>
                            
                            <div class="project-meta">
                                <div class="meta-item">
                                    <i class="bi bi-calendar"></i>
                                    <span>Durée: 18 mois</span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Rendement: 16%</span>
                                </div>
                            </div>

                            <div class="funding-bar">
                                <div class="funding-header">
                                    <span class="funding-label">Financement</span>
                                    <span class="funding-percentage">30%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 30%"></div>
                                </div>
                                <div class="funding-details">
                                    <span class="funding-raised">$36,000 collectés</span>
                                    <span class="funding-goal">Objectif: $120,000</span>
                                </div>
                            </div>

                            <div class="investors-count">
                                <div class="investors-avatars">
                                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop" alt="Investisseur">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop" alt="Investisseur">
                                </div>
                                <span>+18 investisseurs</span>
                            </div>

                            <button class="btn-invest-now" onclick="openInvestModal('Extension Vergers Artemisia Annua', 120000, 36000, 16)">
                                Choisir ce Projet <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Projet 3 -->
                <div class="col-lg-4 project-item" data-category="digital">
                    <div class="invest-project-card animate-on-scroll">
                        <div class="card-img">
                            <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?q=80&w=2070&auto=format&fit=crop" alt="Plateforme digitale">
                            <span class="category-tag">
                                <i class="bi bi-laptop"></i> Digital Health
                            </span>
                        </div>
                        <div class="card-content">
                            <h4>Plateforme Télé-Phyto & IA</h4>
                            <p>
                                Développement de l'intelligence artificielle de diagnostic pour notre 
                                service de téléconsultation intégré aux pharmacies locales partenaires.
                            </p>
                            
                            <div class="project-meta">
                                <div class="meta-item">
                                    <i class="bi bi-calendar"></i>
                                    <span>Durée: 12 mois</span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Rendement: 25%</span>
                                </div>
                            </div>

                            <div class="funding-bar">
                                <div class="funding-header">
                                    <span class="funding-label">Financement</span>
                                    <span class="funding-percentage">85%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 85%"></div>
                                </div>
                                <div class="funding-details">
                                    <span class="funding-raised">$38,250 collectés</span>
                                    <span class="funding-goal">Objectif: $45,000</span>
                                </div>
                            </div>

                            <div class="investors-count">
                                <div class="investors-avatars">
                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop" alt="Investisseur">
                                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=100&h=100&fit=crop" alt="Investisseur">
                                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&h=100&fit=crop" alt="Investisseur">
                                </div>
                                <span>+28 investisseurs</span>
                            </div>

                            <button class="btn-invest-now" onclick="openInvestModal('Plateforme Télé-Phyto & IA', 45000, 38250, 25)">
                                Choisir ce Projet <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projet 4 & 5 (Nouveaux) -->
            <div class="row g-4 mt-2">
                <div class="col-lg-6 project-item" data-category="industrie">
                    <div class="invest-project-card animate-on-scroll">
                        <div class="card-img" style="height: 200px;">
                            <img src="https://images.unsplash.com/photo-1581093458791-9f3c3900df4b?q=80&w=2070&auto=format&fit=crop" alt="Laboratoire">
                            <span class="category-tag">
                                <i class="bi bi-gear"></i> R&D
                            </span>
                        </div>
                        <div class="card-content">
                            <h4>Centre de Recherche Biopharmacie</h4>
                            <p>Construction d'un laboratoire de recherche dédié à l'étude des molécules actives de la flore burundaise.</p>
                            <div class="funding-bar">
                                <div class="progress">
                                    <div class="progress-bar" style="width: 45%"></div>
                                </div>
                                <div class="funding-details">
                                    <span class="funding-raised">$180,000 / $400,000</span>
                                    <span style="color: var(--success); font-weight: 600;">20% ROI</span>
                                </div>
                            </div>
                            <button class="btn-invest-now" onclick="openInvestModal('Centre de Recherche Biopharmacie', 400000, 180000, 20)">
                                Choisir ce Projet <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 project-item" data-category="agriculture">
                    <div class="invest-project-card animate-on-scroll">
                        <div class="card-img" style="height: 200px;">
                            <img src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=2070&auto=format&fit=crop" alt="Serres">
                            <span class="category-tag">
                                <i class="bi bi-tree"></i> Agritech
                            </span>
                        </div>
                        <div class="card-content">
                            <h4>Serres Hydroponiques Intelligentes</h4>
                            <p>Installation de serres climatisées avec système hydroponique pour production hors-sol optimisée.</p>
                            <div class="funding-bar">
                                <div class="progress">
                                    <div class="progress-bar" style="width: 55%"></div>
                                </div>
                                <div class="funding-details">
                                    <span class="funding-raised">$82,500 / $150,000</span>
                                    <span style="color: var(--success); font-weight: 600;">18% ROI</span>
                                </div>
                            </div>
                            <button class="btn-invest-now" onclick="openInvestModal('Serres Hydroponiques Intelligentes', 150000, 82500, 18)">
                                Choisir ce Projet <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION TÉMOIGNAGES -->
    <section class="testimonials-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="testimonial-card animate-on-scroll">
                        <div class="testimonial-text">
                            "Investir dans AGF Phytomed a été l'une de mes meilleures décisions. Non seulement 
                            le rendement est excellent, mais je participe activement au développement de solutions 
                            médicales naturelles pour l'Afrique. La transparence et le professionnalisme de 
                            l'équipe sont remarquables."
                        </div>
                        <div class="testimonial-author">
                            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop" alt="Jean-Pierre M.">
                            <div class="testimonial-author-info">
                                <h5>Jean-Pierre Mutombo</h5>
                                <span>Investisseur depuis 2023 • Kinshasa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MODAL D'INVESTISSEMENT -->
    <div class="invest-modal-overlay" id="investModal">
        <div class="invest-modal">
            <div class="modal-header">
                <h3 style="margin: 0; font-family: 'Playfair Display', serif;">
                    <i class="bi bi-graph-up-arrow"></i> 
                    Investir dans le projet
                </h3>
                <p style="margin: 10px 0 0 0; opacity: 0.9;" id="modalProjectName">Nom du projet</p>
                <button class="modal-close" onclick="closeInvestModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="background: rgba(15, 76, 58, 0.05); padding: 20px; border-radius: var(--radius); margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--gray);">Objectif de financement:</span>
                        <strong id="modalGoal" style="color: var(--primary-dark);">$0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--gray);">Déjà collecté:</span>
                        <strong id="modalRaised" style="color: var(--success);">$0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--gray);">Rendement estimé:</span>
                        <strong id="modalROI" style="color: var(--accent);">0%</strong>
                    </div>
                </div>

                <h5 style="margin-bottom: 15px; color: var(--primary-dark);">Choisissez votre palier d'investissement:</h5>
                
                <div class="investment-tiers">
                    <div class="tier-option" onclick="selectTier(this, 5000)">
                        <div class="tier-radio">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="tier-info">
                            <h5>Starter</h5>
                            <p>Accès au tableau de bord • Rapport trimestriel</p>
                        </div>
                        <div class="tier-amount">$5,000</div>
                    </div>

                    <div class="tier-option" onclick="selectTier(this, 15000)">
                        <div class="tier-radio">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="tier-info">
                            <h5>Business</h5>
                            <p>+ Visite du site • Réunion annuelle avec la direction</p>
                        </div>
                        <div class="tier-amount">$15,000</div>
                    </div>

                    <div class="tier-option" onclick="selectTier(this, 50000)">
                        <div class="tier-radio">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="tier-info">
                            <h5>Premium</h5>
                            <p>+ Conseil d'administration • Dividendes prioritaires</p>
                        </div>
                        <div class="tier-amount">$50,000</div>
                    </div>
                </div>

                <div class="custom-amount">
                    <label>Montant personnalisé:</label>
                    <div class="input-group-custom">
                        <span>$</span>
                        <input type="number" id="customAmount" placeholder="Entrez votre montant" min="1000" step="1000">
                    </div>
                </div>

                <button class="btn-submit-invest" onclick="submitInvestment()">
                    <i class="bi bi-lock-fill"></i> Procéder au Versement Sécurisé
                </button>
                
                <p style="text-align: center; margin-top: 15px; font-size: 12px; color: var(--gray);">
                    <i class="bi bi-shield-check"></i> Paiement sécurisé SSL • Contrat généré automatiquement
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px"
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach((el) => observer.observe(el));

        // Filtrage des projets
        function filterProjects(category) {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.filter-btn').classList.add('active');

            // Filter items
            const items = document.querySelectorAll('.project-item');
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        }

        // Modal d'investissement
        let selectedAmount = 0;

        function openInvestModal(projectName, goal, raised, roi) {
            document.getElementById('modalProjectName').textContent = projectName;
            document.getElementById('modalGoal').textContent = '$' + goal.toLocaleString();
            document.getElementById('modalRaised').textContent = '$' + raised.toLocaleString();
            document.getElementById('modalROI').textContent = roi + '%';
            document.getElementById('investModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeInvestModal() {
            document.getElementById('investModal').classList.remove('active');
            document.body.style.overflow = '';
            selectedAmount = 0;
            document.querySelectorAll('.tier-option').forEach(tier => {
                tier.classList.remove('selected');
            });
            document.getElementById('customAmount').value = '';
        }

        function selectTier(element, amount) {
            document.querySelectorAll('.tier-option').forEach(tier => {
                tier.classList.remove('selected');
            });
            element.classList.add('selected');
            selectedAmount = amount;
            document.getElementById('customAmount').value = amount;
        }

        function submitInvestment() {
            const customAmount = document.getElementById('customAmount').value;
            const finalAmount = customAmount || selectedAmount;
            
            if (!finalAmount || finalAmount < 1000) {
                alert('Veuillez sélectionner ou saisir un montant d\'investissement (minimum $1,000)');
                return;
            }

            // Simulation de soumission
            const btn = document.querySelector('.btn-submit-invest');
            btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Traitement en cours...';
            btn.disabled = true;

            setTimeout(() => {
                alert('Merci pour votre intérêt ! Un conseiller AGF Phytomed vous contactera dans les 24h pour finaliser votre investissement de $' + parseInt(finalAmount).toLocaleString() + '.');
                closeInvestModal();
                btn.innerHTML = '<i class="bi bi-lock-fill"></i> Procéder au Versement Sécurisé';
                btn.disabled = false;
            }, 2000);
        }

        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('investModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInvestModal();
            }
        });

        // Animation flottante pour l'élément hero
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>