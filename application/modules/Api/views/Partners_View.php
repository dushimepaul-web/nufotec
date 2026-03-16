<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Devenir Intermédiaire Partenaire' ?> | AGF</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Google Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        :root {
            --primary: #0B4F2E;
            --primary-light: #1B7B4B;
            --primary-lighter: #e8f5e9;
            --accent: #27ae60;
            --warning: #FF6B35;
            --error: #E74C3C;
            --info: #3498DB;
            --text-dark: #1a2e3f;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --bg-light: #f8fafc;
            --bg-warm: #faf9f7;
            --border: #e2e8f0;
            --shadow-sm: 0 4px 15px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 30px -10px rgba(0,0,0,0.1);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --radius-sm: 12px;
            --radius-md: 16px;
            --radius-lg: 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--text-dark);
            line-height: 1.5;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary) !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-brand i {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 3rem 0;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .page-header p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
            max-width: 600px;
            position: relative;
        }

        /* Layout principal */
        .main-container {
            max-width: 1400px;
            margin: 0 auto 3rem;
            padding: 0 1.5rem;
        }

        .form-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-warm);
            border-bottom: 1px solid var(--border);
            gap: 1rem;
            flex-wrap: wrap;
        }

        .step {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background: white;
            border: 2px solid var(--border);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .step.active {
            border-color: var(--primary);
            background: linear-gradient(135deg, var(--primary-lighter) 0%, white 100%);
            box-shadow: var(--shadow-sm);
        }

        .step.completed {
            border-color: var(--accent);
            background: #dcfce7;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.75rem;
            color: var(--text-muted);
        }

        .step.active .step-number {
            background: var(--primary);
            color: white;
        }

        .step.completed .step-number {
            background: var(--accent);
            color: white;
        }

        .step.completed .step-number i {
            font-size: 0.8rem;
        }

        .step-text {
            font-weight: 500;
            color: var(--text-muted);
        }

        .step.active .step-text {
            color: var(--primary);
        }

        .step.completed .step-text {
            color: var(--accent);
        }

        /* Form Sections */
        .form-section {
            padding: 2.5rem;
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-subtitle {
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            border-left: 3px solid var(--primary-light);
            padding-left: 1rem;
        }

        /* Form Controls */
        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: var(--bg-light);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(11, 79, 46, 0.1);
            background: white;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: var(--error);
            background-image: none;
        }

        .form-control.is-valid, .form-select.is-valid {
            border-color: var(--accent);
        }

        .invalid-feedback {
            color: var(--error);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }

        .input-group-text {
            background: var(--bg-light);
            border: 2px solid var(--border);
            border-right: none;
            color: var(--text-muted);
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--primary);
        }

        /* Character Counter */
        .char-counter {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
            text-align: right;
        }

        .char-counter.warning {
            color: var(--warning);
        }

        .char-counter.danger {
            color: var(--error);
        }

        /* Checkbox Cards */
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .checkbox-card {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            background: white;
        }

        .checkbox-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .checkbox-card.selected {
            border-color: var(--primary);
            background: var(--primary-lighter);
            box-shadow: 0 5px 15px rgba(11, 79, 46, 0.1);
        }

        .checkbox-card input[type="checkbox"] {
            display: none;
        }

        .checkbox-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .checkbox-card.selected .checkbox-icon {
            transform: scale(1.1);
        }

        /* Country Search */
        .country-search-container {
            position: relative;
        }

        .country-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-md);
            z-index: 1050;
            display: none;
        }

        .country-dropdown.show {
            display: block;
        }

        .country-option {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border);
        }

        .country-option:last-child {
            border-bottom: none;
        }

        .country-option:hover,
        .country-option.active {
            background: var(--primary-lighter);
        }

        .selected-country-badge {
            background: var(--primary-lighter);
            border: 1px solid var(--primary);
            border-radius: 50px;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.05) 100%);
            border-left: 4px solid var(--info);
            border-radius: var(--radius-sm);
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .info-box i {
            color: var(--info);
            font-size: 2rem;
        }

        /* Summary Section */
        .summary-section {
            background: var(--bg-warm);
            border-radius: var(--radius-sm);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .summary-section h6 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .summary-value {
            font-weight: 600;
            color: var(--text-dark);
        }

        .badge-custom {
            background: var(--primary-lighter);
            color: var(--primary);
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin: 0.25rem;
        }

        /* Buttons */
        .btn-nav {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-next {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn-next:hover:not(:disabled) {
            transform: translateX(5px);
            box-shadow: 0 10px 25px rgba(11, 79, 46, 0.3);
            color: white;
        }

        .btn-prev {
            background: white;
            color: var(--text-muted);
            border: 2px solid var(--border);
        }

        .btn-prev:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            padding: 1.25rem 3rem;
            font-size: 1.1rem;
            width: 100%;
            border-radius: 50px;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(11, 79, 46, 0.3);
        }

        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-submit.loading .btn-text {
            visibility: hidden;
        }

        .btn-submit.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Map Container */
        .map-container {
            height: 400px;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin: 2rem 0;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .toast-custom {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.25rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 400px;
            transform: translateX(150%);
            animation: slideIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            border-left: 4px solid;
        }

        .toast-custom.success {
            border-left-color: var(--accent);
        }

        .toast-custom.error {
            border-left-color: var(--error);
        }

        .toast-custom.warning {
            border-left-color: var(--warning);
        }

        @keyframes slideIn {
            to { transform: translateX(0); }
        }

        @keyframes slideOut {
            to { transform: translateX(150%); opacity: 0; }
        }

        .toast-custom.hiding {
            animation: slideOut 0.5s ease forwards;
        }

        .toast-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .toast-custom.success .toast-icon {
            background: #dcfce7;
            color: var(--accent);
        }

        .toast-custom.error .toast-icon {
            background: #fee2e2;
            color: var(--error);
        }

        .toast-custom.warning .toast-icon {
            background: #ffedd5;
            color: var(--warning);
        }

        .toast-content h4 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--text-dark);
        }

        .toast-content p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .toast-close {
            margin-left: auto;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            transition: all 0.3s;
        }

        .toast-close:hover {
            background: #f1f5f9;
            color: var(--text-dark);
        }

        /* Success Modal */
        .modal-success .modal-content {
            border: none;
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .modal-success .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 2rem;
        }

        .modal-success .modal-body {
            padding: 3rem 2rem;
            text-align: center;
        }

        .success-animation {
            width: 100px;
            height: 100px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .success-animation::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #dcfce7;
            animation: ripple 1s ease-out infinite;
        }

        @keyframes ripple {
            to { transform: scale(1.5); opacity: 0; }
        }

        .success-animation i {
            color: var(--accent);
            font-size: 3rem;
            position: relative;
            z-index: 1;
        }

        /* Loading Skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .form-section {
                padding: 1.5rem;
            }
            
            .progress-steps {
                padding: 1rem;
            }
            
            .step-text {
                display: none;
            }
            
            .toast-custom {
                min-width: auto;
                max-width: 90vw;
            }
            
            .checkbox-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .checkbox-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-nav {
                padding: 0.75rem 1.5rem;
            }
        }

        /* Tooltip */
        .tooltip-icon {
            color: var(--info);
            cursor: help;
            font-size: 0.9rem;
            margin-left: 0.25rem;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-muted);
            margin: 2rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border);
        }

        .divider span {
            padding: 0 1rem;
        }

        /* Status Link */
        .status-link {
            text-align: center;
            margin-top: 2rem;
        }

        .status-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .status-link a:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-handshake"></i>
                <span>African Green Farmers</span>
            </a>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1><i class="fas fa-handshake me-3"></i>Devenir Intermédiaire Partenaire</h1>
            <p>Rejoignez notre réseau de professionnels de l'investissement et accédez à des opportunités uniques en Afrique</p>
        </div>
    </header>

    <!-- Container pour les notifications -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Main Container -->
    <div class="main-container">
        <div class="form-card">
            
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active" data-step="1" onclick="goToStep(1)">
                    <div class="step-number">1</div>
                    <div class="step-text">Identité</div>
                </div>
                <div class="step" data-step="2" onclick="goToStep(2)">
                    <div class="step-number">2</div>
                    <div class="step-text">Localisation</div>
                </div>
                <div class="step" data-step="3" onclick="goToStep(3)">
                    <div class="step-number">3</div>
                    <div class="step-text">Régulation</div>
                </div>
                <div class="step" data-step="4" onclick="goToStep(4)">
                    <div class="step-number">4</div>
                    <div class="step-text">Capacités</div>
                </div>
                <div class="step" data-step="5" onclick="goToStep(5)">
                    <div class="step-number">5</div>
                    <div class="step-text">Finalisation</div>
                </div>
            </div>

            <!-- Formulaire -->
            <form id="eoiForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $this->security->get_csrf_hash() ?>" id="csrfToken">

                <!-- Étape 1: Identité -->
                <div class="form-section active" id="step-1">
                    <h2 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Informations d'identité
                    </h2>
                    <p class="section-subtitle">Commençons par les informations de base</p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user"></i>
                                Nom complet <span class="text-danger">*</span>
                                <i class="fas fa-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Votre nom tel qu'il apparaît sur vos documents officiels"></i>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       name="full_name" 
                                       id="full_name" 
                                       placeholder="Jean Dupont" 
                                       required
                                       maxlength="255">
                            </div>
                            <div class="invalid-feedback" id="full_name-error"></div>
                            <div class="char-counter" id="full_name-count">0/255</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-building"></i>
                                Nom de la société
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       name="firm_name" 
                                       id="firm_name" 
                                       placeholder="Raison sociale"
                                       maxlength="200">
                            </div>
                            <div class="invalid-feedback" id="firm_name-error"></div>
                            <div class="char-counter" id="firm_name-count">0/200</div>
                        </div>



                        <!-- À ajouter après le champ "Nom de la société" ou avant "Email" -->
<div class="col-md-6">
    <label class="form-label">
        <i class="fas fa-briefcase"></i>
        Titre / Poste
    </label>
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
        <input type="text" 
               class="form-control" 
               name="position_title" 
               id="position_title" 
               placeholder="Directeur, CEO, Conseiller..."
               maxlength="150">
    </div>
    <div class="invalid-feedback" id="position_title-error"></div>
    <div class="char-counter" id="position_title-count">0/150</div>
</div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email professionnel <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" 
                                       class="form-control" 
                                       name="email" 
                                       id="email" 
                                       placeholder="exemple@domaine.com" 
                                       required
                                       maxlength="191">
                            </div>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-phone"></i>
                                Téléphone
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" 
                                       class="form-control" 
                                       name="mobile" 
                                       id="mobile" 
                                       placeholder="+225 01 23 45 67 89"
                                       maxlength="15">
                            </div>
                            <div class="invalid-feedback" id="mobile-error"></div>
                            <div class="char-counter" id="mobile-count">0/15</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-whatsapp" style="color: #25D366;"></i></span>
                                <input type="tel" 
                                       class="form-control" 
                                       name="whatsapp" 
                                       id="whatsapp" 
                                       placeholder="+225 01 23 45 67 89"
                                       maxlength="15">
                            </div>
                            <div class="invalid-feedback" id="whatsapp-error"></div>
                            <div class="char-counter" id="whatsapp-count">0/15</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-globe"></i>
                                Site web
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                <input type="url" 
                                       class="form-control" 
                                       name="website" 
                                       id="website" 
                                       placeholder="https://www.votresite.com"
                                       maxlength="255">
                            </div>
                            <div class="invalid-feedback" id="website-error"></div>
                            <div class="char-counter" id="website-count">0/255</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-nav btn-next" onclick="validateStep(1, 2)">
                            Continuer <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 2: Localisation -->
                <div class="form-section" id="step-2">
                    <h2 class="section-title">
                        <i class="fas fa-map-marked-alt" style="color: var(--info);"></i>
                        Localisation
                    </h2>
                    <p class="section-subtitle">Où exercez-vous vos activités ?</p>

                    <div class="row g-4">
                        <!-- Pays de juridiction -->
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-gavel"></i>
                                Pays de juridiction <span class="text-danger">*</span>
                                <i class="fas fa-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Pays où votre entreprise est enregistrée légalement"></i>
                            </label>
                            <div class="country-search-container">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-gavel"></i></span>
                                    <input type="text" 
                                           class="form-control country-search" 
                                           id="juridiction_search" 
                                           placeholder="Rechercher un pays..."
                                           data-target="juridiction"
                                           autocomplete="off"
                                           required>
                                </div>
                                <div class="country-dropdown" id="juridiction_dropdown"></div>
                                <input type="hidden" name="id_pays_jurisdiction" id="juridiction_id">
                            </div>
                            <div class="invalid-feedback" id="juridiction-error"></div>
                            <div id="juridiction_selected" class="selected-country-badge" style="display: none;">
                                <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                                <span id="juridiction_name"></span>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="clearCountry('juridiction')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Pays d'opération -->
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-briefcase"></i>
                                Pays d'opération <span class="text-danger">*</span>
                            </label>
                            <div class="country-search-container">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <input type="text" 
                                           class="form-control country-search" 
                                           id="operation_search" 
                                           placeholder="Rechercher un pays..."
                                           data-target="operation"
                                           autocomplete="off"
                                           required>
                                </div>
                                <div class="country-dropdown" id="operation_dropdown"></div>
                                <input type="hidden" name="id_pays_operation" id="operation_id">
                            </div>
                            <div class="invalid-feedback" id="operation-error"></div>
                            <div id="operation_selected" class="selected-country-badge" style="display: none;">
                                <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                                <span id="operation_name"></span>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="clearCountry('operation')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    






                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left"></i> Retour
                        </button>
                        <button type="button" class="btn btn-nav btn-next" onclick="validateStep(2, 3)">
                            Continuer <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 3: Régulation -->
                <div class="form-section" id="step-3">
                    <h2 class="section-title">
                        <i class="fas fa-shield-alt" style="color: var(--violet);"></i>
                        Régulation & Conformité
                    </h2>
                    <p class="section-subtitle">Informations réglementaires et conformité</p>

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Important :</strong> Ces informations nous permettent de vérifier votre statut professionnel et sont essentielles pour le processus de validation.
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Statut régulatoire</label>
                            <select name="regulatory_status" class="form-select" id="regulatory_status">
                                <option value="">Sélectionner...</option>
                                <option value="Licensed">Licensed (Agréé)</option>
                                <option value="Exempt">Exempt (Exempté)</option>
                                <option value="Unlicensed">Unlicensed (Non agréé)</option>
                            </select>
                            <div class="invalid-feedback" id="regulatory_status-error"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Numéro d'enregistrement</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="registration_number" 
                                   id="registration_number" 
                                   placeholder="Numéro d'immatriculation"
                                   maxlength="100">
                            <div class="invalid-feedback" id="registration_number-error"></div>
                            <div class="char-counter" id="registration_number-count">0/100</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Autorité de régulation</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="regulatory_authority" 
                                   id="regulatory_authority" 
                                   placeholder="Ex: SEC, FCA, AMF, BRVM..."
                                   maxlength="255">
                            <div class="invalid-feedback" id="regulatory_authority-error"></div>
                            <div class="char-counter" id="regulatory_authority-count">0/255</div>
                        </div>

                        <div class="col-12">
                            <div class="form-check p-3 border rounded-3">
                                <input class="form-check-input" type="checkbox" name="aml_kyc_compliant" value="1" id="amlCheck">
                                <label class="form-check-label ms-2" for="amlCheck">
                                    <strong>Conformité AML/KYC</strong><br>
                                    <small class="text-muted">Je confirme que ma structure respecte les normes anti-blanchiment et KYC</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(2)">
                            <i class="fas fa-arrow-left"></i> Retour
                        </button>
                        <button type="button" class="btn btn-nav btn-next" onclick="validateStep(3, 4)">
                            Continuer <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 4: Capacités -->
                <div class="form-section" id="step-4">
                    <h2 class="section-title">
                        <i class="fas fa-briefcase" style="color: var(--warning);"></i>
                        Capacités & Profil
                    </h2>
                    <p class="section-subtitle">Décrivez votre expertise et vos préférences</p>

                    <div class="mb-4">
                        <label class="form-label d-block mb-3">
                            <i class="fas fa-check-circle"></i>
                            Votre capacité principale <span class="text-danger">*</span>
                        </label>
                        <div class="checkbox-grid" id="capacityGroup">
                            <?php 
                            $capacities = [
                                'Investment Broker' => ['icon' => 'fa-chart-line', 'color' => '#0B4F2E', 'desc' => 'Courtage en investissement'],
                                'Placement Agent' => ['icon' => 'fa-handshake', 'color' => '#FF6B35', 'desc' => 'Agent de placement'],
                                'Finance Advisor' => ['icon' => 'fa-user-tie', 'color' => '#3498DB', 'desc' => 'Conseiller financier'],
                                'Fund Manager' => ['icon' => 'fa-university', 'color' => '#9B59B6', 'desc' => 'Gestionnaire de fonds'],
                                'Family Office' => ['icon' => 'fa-home', 'color' => '#FFD700', 'desc' => 'Family Office'],
                                'ESG Advisor' => ['icon' => 'fa-leaf', 'color' => '#27ae60', 'desc' => 'Conseiller ESG'],
                                'Independent' => ['icon' => 'fa-user', 'color' => '#94a3b8', 'desc' => 'Indépendant']
                            ];
                            foreach ($capacities as $key => $cap): 
                            ?>
                            <div class="checkbox-card" onclick="toggleCheckbox(this, 'capacity')">
                                <input type="checkbox" name="capacity[]" value="<?= htmlspecialchars($key) ?>">
                                <div class="checkbox-icon" style="background: <?= $cap['color'] ?>20; color: <?= $cap['color'] ?>;">
                                    <i class="fas <?= $cap['icon'] ?>"></i>
                                </div>
                                <div class="fw-semibold"><?= htmlspecialchars($key) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($cap['desc']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="invalid-feedback capacity-error" style="display: none;">Veuillez sélectionner au moins une capacité</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-3">
                            <i class="fas fa-users"></i>
                            Types d'investisseurs que vous représentez
                        </label>
                        <div class="checkbox-grid">
                            <?php 
                            $investors = [
                                'Private Equity' => ['icon' => 'fa-building', 'color' => '#e74c3c'],
                                'Venture Capital' => ['icon' => 'fa-rocket', 'color' => '#9b59b6'],
                                'ESG Funds' => ['icon' => 'fa-leaf', 'color' => '#27ae60'],
                                'DFIs' => ['icon' => 'fa-globe', 'color' => '#3498db'],
                                'Institutional' => ['icon' => 'fa-landmark', 'color' => '#f39c12'],
                                'HNWI' => ['icon' => 'fa-gem', 'color' => '#1abc9c'],
                                'Sovereign' => ['icon' => 'fa-crown', 'color' => '#e67e22']
                            ];
                            foreach ($investors as $key => $inv): 
                            ?>
                            <div class="checkbox-card" onclick="toggleCheckbox(this, 'investor')">
                                <input type="checkbox" name="investor_types[]" value="<?= htmlspecialchars($key) ?>">
                                <div class="checkbox-icon mx-auto" style="background: <?= $inv['color'] ?>20; color: <?= $inv['color'] ?>;">
                                    <i class="fas <?= $inv['icon'] ?>"></i>
                                </div>
                                <div class="small fw-semibold"><?= htmlspecialchars($key) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>





<!-- À ajouter après le champ "Modèle d'engagement" ou avant -->
<div class="mb-4">
    <label class="form-label">
        <i class="fas fa-chart-bar"></i>
        Fourchette d'engagement
    </label>
    <select name="commitment_range" class="form-select" id="commitment_range">
        <option value="">Sélectionner...</option>
        <option value="Below USD 250k">Below USD 250k</option>
        <option value="USD 250k - 1M">USD 250k - 1M</option>
        <option value="USD 1M - 5M">USD 1M - 5M</option>
        <option value="USD 5M+">USD 5M+</option>
        <option value="To be discussed">To be discussed</option>
    </select>
    <div class="invalid-feedback" id="commitment_range-error"></div>
</div>


                    <!-- REMPLACER l'actuel champ engagement_model par celui-ci -->
<div class="mb-4">
    <label class="form-label">
        <i class="fas fa-clock"></i>
        Calendrier de déploiement
    </label>
    <select name="timeline" class="form-select" id="timeline">
        <option value="">Sélectionner...</option>
        <option value="Immediate">Immédiat</option>
        <option value="3-6 months">3-6 mois</option>
        <option value="6-12 months">6-12 mois</option>
        <option value="Exploratory">Exploratoire</option>
    </select>
    <div class="invalid-feedback" id="timeline-error"></div>
</div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(3)">
                            <i class="fas fa-arrow-left"></i> Retour
                        </button>
                        <button type="button" class="btn btn-nav btn-next" onclick="validateStep(4, 5)">
                            Continuer <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 5: Finalisation -->
                <div class="form-section" id="step-5">
                    <h2 class="section-title">
                        <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                        Finalisation
                    </h2>
                    <p class="section-subtitle">Vérifiez vos informations avant soumission</p>

                    <!-- Résumé -->
                    <div id="summary" class="mb-4"></div>
                     
                     <!-- À ajouter AVANT les conditions -->
<div class="mb-4">
    <label class="form-label">
        <i class="fas fa-comment-dots"></i>
        Message stratégique (optionnel)
    </label>
    <textarea class="form-control" 
              name="strategic_message" 
              id="strategic_message" 
              rows="4"
              placeholder="Décrivez brièvement votre intérêt stratégique pour ce partenariat..."></textarea>
    <div class="char-counter" id="strategic_message-count">0/65535</div>
</div>
                    <!-- Conditions -->
                    <div class="form-check mb-4 p-3 border rounded-3 bg-white">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                        <label class="form-check-label ms-2" for="terms">
                            <strong>J'accepte les conditions d'utilisation <span class="text-danger">*</span></strong><br>
                            <small class="text-muted">
                                Je confirme que toutes les informations fournies sont exactes et j'accepte que AGF vérifie mon profil 
                                avant toute autorisation. <a href="#" style="color: var(--primary);" data-bs-toggle="modal" data-bs-target="#termsModal">Lire les conditions</a>
                            </small>
                        </label>
                        <div class="invalid-feedback" id="terms-error">Vous devez accepter les conditions pour continuer</div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(4)">
                            <i class="fas fa-arrow-left"></i> Retour
                        </button>
                        <button type="submit" class="btn btn-nav btn-submit" id="submitBtn">
                            <span class="btn-text">
                                <i class="fas fa-paper-plane me-2"></i>Soumettre ma candidature
                            </span>
                        </button>
                    </div>
                </div>

            </form>
        </div>




        <!-- Lien statut -->
        <div class="status-link">
            <p class="text-muted">
                Déjà inscrit ? 
                <a href="<?= base_url('eoi_partners/status') ?>">
                    Vérifier mon statut <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </p>
        </div>
    </div>

    <!-- Modal Conditions -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conditions d'utilisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptation des conditions</h6>
                    <p>En soumettant ce formulaire, vous acceptez que vos données personnelles soient collectées et traitées dans le cadre de votre demande de partenariat avec African Green Farmers.</p>
                    
                    <h6>2. Exactitude des informations</h6>
                    <p>Vous certifiez que toutes les informations fournies sont exactes, complètes et à jour. Toute fausse déclaration peut entraîner le rejet de votre candidature.</p>
                    
                    <h6>3. Utilisation des données</h6>
                    <p>Vos données seront utilisées uniquement pour évaluer votre profil de partenaire potentiel et pour communiquer avec vous concernant cette demande.</p>
                    
                    <h6>4. Confidentialité</h6>
                    <p>African Green Farmers s'engage à traiter vos informations de manière confidentielle et sécurisée, conformément à notre politique de confidentialité.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">J'ai compris</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Success -->
    <div class="modal fade modal-success" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Candidature envoyée !
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="success-animation">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Merci <span id="successName">!</span></h3>
                    <p class="text-muted">Votre expression d'intérêt a bien été enregistrée. Notre équipe vous répondra dans les 5 jours ouvrés.</p>
                    <button type="button" class="btn btn-primary mt-3" onclick="closeSuccessModal()" style="background: var(--primary); border: none; padding: 0.75rem 2rem; border-radius: 12px;">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // ==================== VARIABLES GLOBALES ====================
        let currentStep = 1;
        const totalSteps = 5;
        let map, marker;
        const countries = <?= json_encode($pays) ?>;
        
        // Coordonnées du siège (Abidjan, Cocody)
        const headquarters = { lat: 5.345317, lng: -4.008429 };

        // ==================== INITIALISATION ====================
        document.addEventListener('DOMContentLoaded', function() {
            initTooltips();
            initCountrySearch();
            initMap();
            initValidation();
            initCharCounters();
        });

        function initTooltips() {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(el => new bootstrap.Tooltip(el));
        }

        // ==================== INITIALISATION DE LA CARTE ====================
        function initMap() {
            map = L.map('map').setView([headquarters.lat, headquarters.lng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Marqueur personnalisé
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: '<i class="fas fa-map-marker-alt" style="color: #0B4F2E; font-size: 30px;"></i>',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            marker = L.marker([headquarters.lat, headquarters.lng], { icon: customIcon })
                .addTo(map)
                .bindPopup(`
                    <b>African Green Farmers</b><br>
                    Siège social<br>
                    Rue des Jardins, Cocody<br>
                    Abidjan, Côte d'Ivoire
                `)
                .openPopup();

            // Cercle autour du siège
            L.circle([headquarters.lat, headquarters.lng], {
                color: '#0B4F2E',
                fillColor: '#0B4F2E',
                fillOpacity: 0.1,
                radius: 500
            }).addTo(map);
        }

        // ==================== RECHERCHE DE PAYS ====================
        function initCountrySearch() {
            const searches = document.querySelectorAll('.country-search');
            
            searches.forEach(search => {
                const target = search.dataset.target;
                const dropdown = document.getElementById(`${target}_dropdown`);
                
                // Remplir le dropdown
                dropdown.innerHTML = countries.map(country => `
                    <div class="country-option" 
                         data-id="${country.id}" 
                         data-name="${country.pays || country.name}"
                         onclick="selectCountry('${target}', ${country.id}, '${country.pays || country.name}')">
                        <i class="fas fa-map-marker-alt me-2" style="color: var(--info);"></i>
                        ${country.pays || country.name}
                    </div>
                `).join('');

                // Recherche en temps réel
                search.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const options = dropdown.querySelectorAll('.country-option');
                    let hasResults = false;

                    options.forEach(opt => {
                        const name = opt.dataset.name.toLowerCase();
                        if (name.includes(query) || query === '') {
                            opt.style.display = 'block';
                            hasResults = true;
                        } else {
                            opt.style.display = 'none';
                        }
                    });

                    dropdown.classList.toggle('show', hasResults && this.value.length > 0);
                });

                // Focus
                search.addEventListener('focus', function() {
                    if (this.value.length > 0) {
                        dropdown.classList.add('show');
                    }
                });

                // Clic extérieur
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.country-search-container')) {
                        dropdown.classList.remove('show');
                    }
                });
            });
        }

        window.selectCountry = function(type, id, name) {
            document.getElementById(`${type}_search`).value = name;
            document.getElementById(`${type}_id`).value = id;
            
            document.getElementById(`${type}_name`).textContent = name;
            document.getElementById(`${type}_selected`).style.display = 'inline-flex';
            
            document.getElementById(`${type}_dropdown`).classList.remove('show');
            
            // Valider le champ
            document.getElementById(`${type}_search`).classList.remove('is-invalid');
            document.getElementById(`${type}-error`).style.display = 'none';
            
            // Si c'est le pays d'opération, centrer la carte
            if (type === 'operation') {
                // Ici on pourrait géocoder le pays et centrer la carte
            }
        };

        window.clearCountry = function(type) {
            document.getElementById(`${type}_search`).value = '';
            document.getElementById(`${type}_id`).value = '';
            document.getElementById(`${type}_selected`).style.display = 'none';
            document.getElementById(`${type}_search`).focus();
        };

        // ==================== COMPTEURS DE CARACTÈRES ====================

function initCharCounters() {
    const fields = ['full_name', 'firm_name', 'position_title', 'mobile', 'whatsapp', 'website', 
                   'registration_number', 'regulatory_authority', 'strategic_message'];
    
    fields.forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            const counter = document.getElementById(id + '-count');
            if (counter) {
                updateCharCount(field, counter);
                field.addEventListener('input', () => updateCharCount(field, counter));
            }
        }
    });
}

        function updateCharCount(field, counter) {
            const count = field.value.length;
            const max = field.maxLength;
            counter.textContent = `${count}/${max}`;
            
            counter.classList.toggle('warning', count > max * 0.8);
            counter.classList.toggle('danger', count >= max);
        }

        // ==================== CHECKBOX CARDS ====================
        window.toggleCheckbox = function(card, group) {
            const checkbox = card.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            card.classList.toggle('selected', checkbox.checked);
            
            if (group === 'capacity') {
                const capacities = document.querySelectorAll('input[name="capacity[]"]:checked');
                if (capacities.length > 0) {
                    document.querySelector('.capacity-error').style.display = 'none';
                }
            }
        };

        // ==================== VALIDATION ====================
        
const validationRules = {
    full_name: { required: true, min: 3, max: 255 },
    position_title: { max: 150 }, // Nouveau champ
    email: { required: true, type: 'email', max: 191 },
    mobile: { pattern: /^[0-9+\-\s]+$/, max: 15 },
    whatsapp: { pattern: /^[0-9+\-\s]+$/, max: 15 },
    website: { type: 'url', max: 255 },
    registration_number: { max: 100 },
    regulatory_authority: { max: 255 }
    // strategic_message n'a pas besoin de validation spécifique
};







        function validateField(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return true;
            
            const value = field.value.trim();
            const rules = validationRules[fieldId];
            
            field.classList.remove('is-valid', 'is-invalid');
            
            if (!rules) return true;

            // Required
            if (rules.required && !value) {
                showFieldError(field, fieldId + '-error', 'Ce champ est requis');
                return false;
            }

            if (value) {
                // Min length
                if (rules.min && value.length < rules.min) {
                    showFieldError(field, fieldId + '-error', `Minimum ${rules.min} caractères`);
                    return false;
                }

                // Max length
                if (rules.max && value.length > rules.max) {
                    showFieldError(field, fieldId + '-error', `Maximum ${rules.max} caractères`);
                    return false;
                }

                // Pattern
                if (rules.pattern && !rules.pattern.test(value)) {
                    showFieldError(field, fieldId + '-error', 'Format invalide');
                    return false;
                }

                // Email
                if (rules.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        showFieldError(field, fieldId + '-error', 'Email invalide');
                        return false;
                    }
                }

                // URL
                if (rules.type === 'url') {
                    try {
                        new URL(value);
                    } catch {
                        showFieldError(field, fieldId + '-error', 'URL invalide');
                        return false;
                    }
                }
            }

            field.classList.add('is-valid');
            hideFieldError(field, fieldId + '-error');
            return true;
        }

        function showFieldError(field, errorId, message) {
            field.classList.add('is-invalid');
            const errorEl = document.getElementById(errorId);
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }
        }

        function hideFieldError(field, errorId) {
            const errorEl = document.getElementById(errorId);
            if (errorEl) errorEl.style.display = 'none';
        }

        function initValidation() {
            Object.keys(validationRules).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('blur', () => validateField(fieldId));
                    field.addEventListener('input', () => {
                        if (field.classList.contains('is-invalid')) {
                            validateField(fieldId);
                        }
                    });
                }
            });
        }

        // ==================== VALIDATION DES ÉTAPES ====================
        window.validateStep = function(step, nextStep) {
            let isValid = true;

            switch(step) {
                case 1:
                    isValid = validateStep1();
                    break;
                case 2:
                    isValid = validateStep2();
                    break;
                case 3:
                    isValid = validateStep3();
                    break;
                case 4:
                    isValid = validateStep4();
                    break;
                case 5:
                    isValid = validateStep5();
                    break;
            }

            if (isValid) {
                goToStep(nextStep);
            } else {
                document.getElementById(`step-${step}`).classList.add('shake');
                setTimeout(() => document.getElementById(`step-${step}`).classList.remove('shake'), 500);
                showToast('error', 'Erreur de validation', 'Veuillez corriger les erreurs avant de continuer');
            }
        };

        function validateStep1() {
            let isValid = true;
            if (!validateField('full_name')) isValid = false;
            if (!validateField('email')) isValid = false;
            if (!validateField('mobile')) isValid = false;
            if (!validateField('whatsapp')) isValid = false;
            if (!validateField('website')) isValid = false;
            return isValid;
        }

        function validateStep2() {
            let isValid = true;
            
            if (!document.getElementById('juridiction_id').value) {
                document.getElementById('juridiction_search').classList.add('is-invalid');
                document.getElementById('juridiction-error').textContent = 'Veuillez sélectionner un pays';
                document.getElementById('juridiction-error').style.display = 'block';
                isValid = false;
            }
            
            if (!document.getElementById('operation_id').value) {
                document.getElementById('operation_search').classList.add('is-invalid');
                document.getElementById('operation-error').textContent = 'Veuillez sélectionner un pays';
                document.getElementById('operation-error').style.display = 'block';
                isValid = false;
            }
            
            return isValid;
        }

        function validateStep3() {
            return true; // Pas de champs requis
        }

        function validateStep4() {
            let isValid = true;
            
            const capacities = document.querySelectorAll('input[name="capacity[]"]:checked');
            if (capacities.length === 0) {
                document.querySelector('.capacity-error').style.display = 'block';
                isValid = false;
            }
            
            return isValid;
        }

        function validateStep5() {
            let isValid = true;
            
            const terms = document.getElementById('terms');
            if (!terms.checked) {
                terms.classList.add('is-invalid');
                document.getElementById('terms-error').style.display = 'block';
                isValid = false;
            }
            
            return isValid;
        }

        // ==================== NAVIGATION ====================
        window.goToStep = function(step) {
            if (step < 1 || step > totalSteps) return;

            // Mettre à jour les steps
            document.querySelectorAll('.step').forEach(el => {
                el.classList.remove('active', 'completed');
            });

            for (let i = 1; i < step; i++) {
                document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
            }

            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');

            // Mettre à jour les sections
            document.querySelectorAll('.form-section').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(`step-${step}`).classList.add('active');

            currentStep = step;

            // Générer le résumé si on est à l'étape 5
            if (step === 5) {
                generateSummary();
            }

            // Scroll en haut
            document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth' });
        };

        // ==================== GÉNÉRATION DU RÉSUMÉ ====================
        function generateSummary() {
    const summary = document.getElementById('summary');
    
    const fullName = document.getElementById('full_name').value || 'Non renseigné';
    const email = document.getElementById('email').value || 'Non renseigné';
    const firmName = document.getElementById('firm_name').value || 'Non renseigné';
    const positionTitle = document.getElementById('position_title').value || 'Non renseigné'; // Nouveau
    
    const jurName = document.getElementById('juridiction_name').textContent || 'Non sélectionné';
    const opName = document.getElementById('operation_name').textContent || 'Non sélectionné';
    
    const capacities = Array.from(document.querySelectorAll('input[name="capacity[]"]:checked')).map(cb => cb.value);
    const investors = Array.from(document.querySelectorAll('input[name="investor_types[]"]:checked')).map(cb => cb.value);
    
    const regulatoryStatus = document.getElementById('regulatory_status').value || 'Non spécifié';
    const commitmentRange = document.getElementById('commitment_range').value || 'Non spécifié'; // Nouveau
    const timeline = document.getElementById('timeline').value || 'Non spécifié'; // Nouveau (remplace engagement_model)
    const strategicMessage = document.getElementById('strategic_message').value || 'Non renseigné'; // Nouveau

    let html = `
        <div class="summary-section">
            <h6><i class="fas fa-user-circle"></i> Identité</h6>
            <div class="summary-item">
                <span class="summary-label">Nom complet</span>
                <span class="summary-value">${escapeHtml(fullName)}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Poste</span>
                <span class="summary-value">${escapeHtml(positionTitle)}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Email</span>
                <span class="summary-value">${escapeHtml(email)}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Société</span>
                <span class="summary-value">${escapeHtml(firmName)}</span>
            </div>
        </div>

        <div class="summary-section">
            <h6><i class="fas fa-map-marked-alt"></i> Localisation</h6>
            <div class="summary-item">
                <span class="summary-label">Juridiction</span>
                <span class="summary-value">${escapeHtml(jurName)}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Opération</span>
                <span class="summary-value">${escapeHtml(opName)}</span>
            </div>
        </div>

        <div class="summary-section">
            <h6><i class="fas fa-shield-alt"></i> Régulation</h6>
            <div class="summary-item">
                <span class="summary-label">Statut</span>
                <span class="summary-value">${escapeHtml(regulatoryStatus)}</span>
            </div>
        </div>
    `;

    if (capacities.length > 0) {
        html += `
            <div class="summary-section">
                <h6><i class="fas fa-briefcase"></i> Capacités</h6>
                <div>
                    ${capacities.map(cap => `<span class="badge-custom">${escapeHtml(cap)}</span>`).join('')}
                </div>
            </div>
        `;
    }

    if (investors.length > 0) {
        html += `
            <div class="summary-section">
                <h6><i class="fas fa-users"></i> Investisseurs</h6>
                <div>
                    ${investors.map(inv => `<span class="badge-custom">${escapeHtml(inv)}</span>`).join('')}
                </div>
            </div>
        `;
    }

    html += `
        <div class="summary-section">
            <h6><i class="fas fa-handshake"></i> Engagement</h6>
            <div class="summary-item">
                <span class="summary-label">Fourchette</span>
                <span class="summary-value">${escapeHtml(commitmentRange)}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Calendrier</span>
                <span class="summary-value">${escapeHtml(timeline)}</span>
            </div>
        </div>
    `;

    if (strategicMessage !== 'Non renseigné') {
        html += `
            <div class="summary-section">
                <h6><i class="fas fa-comment-dots"></i> Message stratégique</h6>
                <p class="mb-0">${escapeHtml(strategicMessage)}</p>
            </div>
        `;
    }

    summary.innerHTML = html;
}







        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ==================== NOTIFICATIONS TOAST ====================
        function showToast(type, title, message, duration = 5000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-custom ${type}`;
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle'
            };

            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="fas ${icons[type]}"></i>
                </div>
                <div class="toast-content">
                    <h4>${title}</h4>
                    <p>${message}</p>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 500);
            }, duration);
        }















        // ==================== SOUMISSION DU FORMULAIRE ====================

// ==================== SOUMISSION DU FORMULAIRE SANS CSRF ====================
document.getElementById('eoiForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validation finale (similaire au contactForm)
    if (!validateStep5()) {
        showToast('error', 'Validation', 'Veuillez accepter les conditions');
        const consent = document.getElementById('terms');
        if (consent) {
            consent.classList.add('is-invalid');
            consent.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    // Loading state
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;

    // Préparation des données (comme contactForm)
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Gérer les tableaux (checkboxes) - spécifique à EOI
    data.capacity = Array.from(document.querySelectorAll('input[name="capacity[]"]:checked')).map(cb => cb.value);
    data.investor_types = Array.from(document.querySelectorAll('input[name="investor_types[]"]:checked')).map(cb => cb.value);

    // URL CORRECTE avec base_url()
    const submitUrl = '<?= base_url('Api/eoi_partners/Save') ?>';
    
    console.log('Envoi vers:', submitUrl);
    console.log('Données:', data);

    try {
        const response = await fetch(submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            // Afficher le modal de succès (comme contactForm)
            document.getElementById('successName').textContent = data.full_name.split(' ')[0] + ' !';
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
            
            // Reset formulaire (comme contactForm)
            this.reset();
            resetForm();
            
            // Reset validation visuelle
            document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            showToast('success', 'Succès !', result.message);
            
            // Retour étape 1 après délai
            setTimeout(() => {
                goToStep(1);
                modal.hide();
            }, 3000);
        } else {
            // Afficher erreurs de validation (comme contactForm)
            if (result.errors) {
                Object.keys(result.errors).forEach(field => {
                    const el = document.getElementById(field);
                    if (el) {
                        el.classList.add('is-invalid');
                        const feedback = el.parentElement.querySelector('.invalid-feedback') 
                                      || document.getElementById(field + '-error');
                        if (feedback) {
                            feedback.textContent = result.errors[field];
                            feedback.style.display = 'block';
                        }
                    }
                });
                
                // Scroll vers première erreur
                const firstError = document.querySelector('.is-invalid');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            showToast('error', 'Erreur', result.message || 'Une erreur est survenue');
        }

    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Erreur réseau', 'Impossible de contacter le serveur. Veuillez réessayer.');
    } finally {
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
    }
});








function resetForm() {
    document.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    clearCountry('juridiction');
    clearCountry('operation');
    
    document.querySelectorAll('.char-counter').forEach(el => {
        const id = el.id.replace('-count', '');
        const field = document.getElementById(id);
        if (field && field.maxLength) {
            el.textContent = `0/${field.maxLength}`;
        }
    });
    
    // Réinitialiser les selects
    document.getElementById('commitment_range').value = '';
    document.getElementById('timeline').value = '';
    document.getElementById('position_title').value = '';
    document.getElementById('strategic_message').value = '';
}





        function closeSuccessModal() {
            const modalEl = document.getElementById('successModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
        }
    </script>
</body>
</html>