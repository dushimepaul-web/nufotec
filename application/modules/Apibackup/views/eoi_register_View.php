<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Devenir Intermédiaire' ?> | AGF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #0B4F2E;
            --secondary-green: #1B7B4B;
            --accent-green: #27ae60;
            --light-green: #2ecc71;
            --jaune: #FFD700;
            --orange: #FF6B35;
            --rouge: #E74C3C;
            --bleu: #3498DB;
            --violet: #9B59B6;
            --text-dark: #1a2e3f;
            --text-muted: #6c757d;
            --text-light: #95a5a6;
            --bg-light: #f8faf9;
            --border-light: #e9ecef;
            --shadow-soft: 0 4px 15px rgba(0,0,0,0.08);
            --shadow-hover: 0 15px 35px rgba(11, 79, 46, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, #e8f5e9 100%);
            min-height: 100vh;
            color: var(--text-dark);
        }

        .header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            padding: 3rem 0 6rem;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .main-container {
            margin-top: -4rem;
            padding-bottom: 4rem;
            position: relative;
            z-index: 2;
        }

        .form-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-hover);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .progress-steps {
            display: flex;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-light);
            gap: 0.5rem;
        }

        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
            opacity: 0.5;
            transition: var(--transition);
            cursor: pointer;
        }

        .step.active {
            opacity: 1;
        }

        .step.completed {
            opacity: 1;
        }

        .step.completed .step-number {
            background: var(--accent-green);
            color: white;
            position: relative;
        }

        .step.completed .step-number::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            font-size: 0.8rem;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--border-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.75rem;
            transition: var(--transition);
        }

        .step.active .step-number {
            background: var(--primary-green);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(11, 79, 46, 0.3);
        }

        .step.active .step-text {
            color: var(--primary-green);
            font-weight: 600;
        }

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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .shake {
            animation: shake 0.5s;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-subtitle {
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-light);
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1);
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: var(--rouge);
            background-image: none;
        }

        .form-control.is-invalid:focus, .form-select.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.1);
        }

        .invalid-feedback {
            color: var(--rouge);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        .input-group-text {
            background: var(--bg-light);
            border: 2px solid var(--border-light);
            border-right: none;
            color: var(--text-muted);
            border-radius: 12px 0 0 12px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--accent-green);
        }

        .checkbox-card {
            border: 2px solid var(--border-light);
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: var(--transition);
            height: 100%;
            text-align: center;
            background: white;
        }

        .checkbox-card:hover {
            border-color: var(--accent-green);
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        .checkbox-card.selected {
            border-color: var(--accent-green);
            background: rgba(39, 174, 96, 0.05);
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.1);
        }

        .checkbox-card input[type="checkbox"] {
            display: none;
        }

        .checkbox-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
            transition: var(--transition);
        }

        .checkbox-card.selected .checkbox-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .btn-nav {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-next {
            background: linear-gradient(135deg, var(--secondary-green) 0%, var(--accent-green) 100%);
            color: white;
        }

        .btn-next:hover:not(:disabled) {
            transform: translateX(5px);
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
            color: white;
        }

        .btn-next:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-prev {
            background: white;
            color: var(--text-muted);
            border: 2px solid var(--border-light);
        }

        .btn-prev:hover {
            border-color: var(--text-muted);
            color: var(--text-dark);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            color: white;
            border: none;
            padding: 1.25rem 3rem;
            font-size: 1.1rem;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(11, 79, 46, 0.3);
            color: white;
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-submit.loading {
            pointer-events: none;
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
            margin-top: -10px;
            margin-left: -10px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background: var(--jaune)20;
            color: #b7950b;
        }

        .status-approved {
            background: var(--accent-green)20;
            color: var(--accent-green);
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .info-box {
            background: linear-gradient(135deg, var(--bleu)10 0%, var(--bleu)05 100%);
            border-left: 4px solid var(--bleu);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: start;
            gap: 1rem;
        }

        .summary-section {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .summary-section h6 {
            color: var(--primary-green);
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
            border-bottom: 1px dashed var(--border-light);
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

        .tooltip-icon {
            color: var(--bleu);
            cursor: help;
            margin-left: 0.25rem;
        }

        .selected-country-badge {
            background: var(--bg-light);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pays-option {
            cursor: pointer;
            transition: var(--transition);
        }

        .pays-option:hover {
            background-color: var(--bg-light);
        }

        .pays-option.active {
            background-color: #e3f2fd;
            border-left: 3px solid var(--bleu);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.75rem;
            }
            .step-text {
                display: none;
            }
            .form-section {
                padding: 1.5rem;
            }
            .progress-steps {
                gap: 0;
            }
            .step {
                margin: 0 0.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="mb-3">
                    <i class="fas fa-handshake fa-3x opacity-75"></i>
                </div>
                <h1>Devenir Intermédiaire Partenaire</h1>
                <p>Rejoignez notre réseau de professionnels de l'investissement et accédez à des opportunités uniques en Afrique</p>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Messages Flash -->
                <div id="flashMessages">
                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger alert-custom mb-4">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                            <div>
                                <strong>Erreur</strong><br>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success alert-custom mb-4">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <div>
                                <strong>Succès !</strong><br>
                                <?= htmlspecialchars($success) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

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

                    <form action="<?= base_url('eoi_register/submit') ?>" method="post" id="eoiForm" class="needs-validation" novalidate>
                        
                        <!-- Étape 1: Identité -->
                        <div class="form-section active" id="step-1">
                            <h2 class="section-title">
                                <i class="fas fa-user-circle" style="color: var(--primary-green);"></i>
                                Informations d'identité
                            </h2>
                            <p class="section-subtitle">Commençons par les informations de base</p>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Nom complet <span class="text-danger">*</span>
                                        <i class="fas fa-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Votre nom tel qu'il apparaît sur vos documents officiels"></i>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" name="full_name" class="form-control" required 
                                               placeholder="Prénom et nom" value="<?= set_value('full_name') ?>"
                                               data-validation="required|min:3|max:100">
                                    </div>
                                    <div class="invalid-feedback" id="full_name-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nom de la société</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input type="text" name="firm_name" class="form-control" 
                                               placeholder="Raison sociale" value="<?= set_value('firm_name') ?>"
                                               data-validation="max:200">
                                    </div>
                                    <div class="invalid-feedback" id="firm_name-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Email professionnel <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control" required 
                                               placeholder="exemple@domaine.com" value="<?= set_value('email') ?>"
                                               data-validation="required|email|max:191">
                                    </div>
                                    <div class="invalid-feedback" id="email-error"></div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Téléphone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" name="mobile" class="form-control" 
                                               placeholder="+225..." value="<?= set_value('mobile') ?>"
                                               data-validation="phone|max:15">
                                    </div>
                                    <div class="invalid-feedback" id="mobile-error"></div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-whatsapp" style="color: #25D366;"></i></span>
                                        <input type="tel" name="whatsapp" class="form-control" 
                                               placeholder="+225..." value="<?= set_value('whatsapp') ?>"
                                               data-validation="phone|max:15">
                                    </div>
                                    <div class="invalid-feedback" id="whatsapp-error"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Site web</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                        <input type="url" name="website" class="form-control" 
                                               placeholder="https://www.votresite.com" value="<?= set_value('website') ?>"
                                               data-validation="url|max:255">
                                    </div>
                                    <div class="invalid-feedback" id="website-error"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-5">
                                <button type="button" class="btn btn-nav btn-next" onclick="validateStep(1, 2)" id="nextBtn1">
                                    Continuer <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Étape 2: Localisation -->
                        <div class="form-section" id="step-2">
                            <h2 class="section-title">
                                <i class="fas fa-map-marked-alt" style="color: var(--bleu);"></i>
                                Localisation
                            </h2>
                            <p class="section-subtitle">Où exercez-vous vos activités ?</p>

                            <div class="row g-4">
                                <!-- Pays de juridiction avec recherche -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Pays de juridiction <span class="text-danger">*</span>
                                        <i class="fas fa-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Pays où votre entreprise est enregistrée légalement"></i>
                                    </label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-gavel"></i></span>
                                            <input type="text" 
                                                   id="juridiction_search" 
                                                   class="form-control" 
                                                   placeholder="Rechercher un pays..." 
                                                   autocomplete="off"
                                                   value="<?= isset($selected_juridiction) ? htmlspecialchars($selected_juridiction['nom']) : '' ?>"
                                                   required>
                                            <input type="hidden" name="id_pays_jurisdiction" id="selected_juridiction_id" value="<?= set_value('id_pays_jurisdiction') ?>">
                                        </div>
                                        
                                        <!-- Dropdown des résultats -->
                                        <div id="juridiction_dropdown" class="dropdown-menu shadow-lg border-0" 
                                             style="max-height: 300px; overflow-y: auto; display: none; position: absolute; top: 100%; left: 0; z-index: 1050; background: white; border-radius: 8px; margin-top: 5px; width: 100%;">
                                            
                                            <?php if (isset($pays) && is_array($pays)): ?>
                                                <?php foreach ($pays as $p): ?>
                                                    <div class="dropdown-item pays-option py-2 px-3" 
                                                         data-id="<?= htmlspecialchars($p['id'] ?? $p['id']) ?>"
                                                         data-value="<?= htmlspecialchars($p['nom'] ?? $p['name']) ?>"
                                                         data-search="<?= htmlspecialchars(strtolower($p->nom ?? $p['name'])) ?>"
                                                         onclick="selectPays(this, 'juridiction')">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-map-marker-alt me-2" style="color: var(--bleu);"></i>
                                                                <span class="fw-medium"><?= htmlspecialchars($p->nom ?? $p['name']) ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="invalid-feedback" id="juridiction-error"></div>
                                    </div>
                                    <div class="form-text">Pays où votre entreprise est enregistrée</div>
                                </div>

                                <!-- Pays d'opération avec recherche -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Pays d'opération <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                            <input type="text" 
                                                   id="operation_search" 
                                                   class="form-control" 
                                                   placeholder="Rechercher un pays..." 
                                                   autocomplete="off"
                                                   value="<?= isset($selected_operation) ? htmlspecialchars($selected_operation['nom']) : '' ?>"
                                                   required>
                                            <input type="hidden" name="id_pays_operation" id="selected_operation_id" value="<?= set_value('id_pays_operation') ?>">
                                        </div>
                                        
                                        <!-- Dropdown des résultats -->
                                        <div id="operation_dropdown" class="dropdown-menu shadow-lg border-0" 
                                             style="max-height: 300px; overflow-y: auto; display: none; position: absolute; top: 100%; left: 0; z-index: 1050; background: white; border-radius: 8px; margin-top: 5px; width: 100%;">
                                            
                                            <?php if (isset($pays) && is_array($pays)): ?>
                                                <?php foreach ($pays as $p): ?>
                                                    <div class="dropdown-item pays-option py-2 px-3" 
                                                         data-id="<?= htmlspecialchars($p->id ?? $p['id']) ?>"
                                                         data-value="<?= htmlspecialchars($p->nom ?? $p['name']) ?>"
                                                         data-search="<?= htmlspecialchars(strtolower($p->nom ?? $p['name'])) ?>"
                                                         onclick="selectPays(this, 'operation')">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-map-marker-alt me-2" style="color: var(--bleu);"></i>
                                                                <span class="fw-medium"><?= htmlspecialchars($p->nom ?? $p['name']) ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="invalid-feedback" id="operation-error"></div>
                                    </div>
                                    <div class="form-text">Pays où vous opérez principalement</div>
                                </div>
                            </div>

                            <!-- Affichage des pays sélectionnés -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div id="selected_juridiction_display" class="selected-country-badge d-none">
                                        <i class="fas fa-check-circle" style="color: var(--accent-green);"></i>
                                        <span id="selected_juridiction_name"></span>
                                        <button type="button" class="btn btn-link btn-sm ms-auto p-0" onclick="clearCountrySelection('juridiction')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div id="selected_operation_display" class="selected-country-badge d-none">
                                        <i class="fas fa-check-circle" style="color: var(--accent-green);"></i>
                                        <span id="selected_operation_name"></span>
                                        <button type="button" class="btn btn-link btn-sm ms-auto p-0" onclick="clearCountrySelection('operation')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(1)">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <button type="button" class="btn btn-nav btn-next" onclick="validateStep(2, 3)" id="nextBtn2">
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
                                <i class="fas fa-info-circle fa-2x" style="color: var(--bleu);"></i>
                                <div>
                                    <strong>Important :</strong> Ces informations nous permettent de vérifier votre statut professionnel et sont essentielles pour le processus de validation.
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Statut régulatoire</label>
                                    <select name="regulatory_status" class="form-select" data-validation="max:50">
                                        <option value="">Sélectionner...</option>
                                        <option value="Licensed" <?= set_select('regulatory_status', 'Licensed') ?>>Licensed (Agréé)</option>
                                        <option value="Exempt" <?= set_select('regulatory_status', 'Exempt') ?>>Exempt (Exempté)</option>
                                        <option value="Unlicensed" <?= set_select('regulatory_status', 'Unlicensed') ?>>Unlicensed (Non agréé)</option>
                                    </select>
                                    <div class="invalid-feedback" id="regulatory_status-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Numéro d'enregistrement</label>
                                    <input type="text" name="registration_number" class="form-control" 
                                           placeholder="Numéro d'immatriculation" value="<?= set_value('registration_number') ?>"
                                           data-validation="max:100">
                                    <div class="invalid-feedback" id="registration_number-error"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Autorité de régulation</label>
                                    <input type="text" name="regulatory_authority" class="form-control" 
                                           placeholder="Ex: SEC, FCA, AMF, BRVM..." value="<?= set_value('regulatory_authority') ?>"
                                           data-validation="max:255">
                                    <div class="invalid-feedback" id="regulatory_authority-error"></div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-check-lg p-3 border rounded-3">
                                        <input class="form-check-input" type="checkbox" name="aml_kyc_compliant" value="1" id="amlCheck"
                                            <?= set_checkbox('aml_kyc_compliant', '1') ?>>
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
                                <button type="button" class="btn btn-nav btn-next" onclick="validateStep(3, 4)" id="nextBtn3">
                                    Continuer <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Étape 4: Capacités -->
                        <div class="form-section" id="step-4">
                            <h2 class="section-title">
                                <i class="fas fa-briefcase" style="color: var(--orange);"></i>
                                Capacités & Profil
                            </h2>
                            <p class="section-subtitle">Décrivez votre expertise et vos préférences</p>

                            <div class="mb-4">
                                <label class="form-label d-block mb-3">
                                    Votre capacité principale <span class="text-danger">*</span>
                                    <i class="fas fa-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Sélectionnez au moins une capacité"></i>
                                </label>
                                <div class="row g-3" id="capacityGroup">
                                    <?php 
                                    $capacities = [
                                        'Investment Broker' => ['icon' => 'fa-chart-line', 'color' => 'var(--primary-green)', 'desc' => 'Courtage en investissement'],
                                        'Placement Agent' => ['icon' => 'fa-handshake', 'color' => 'var(--orange)', 'desc' => 'Agent de placement'],
                                        'Finance Advisor' => ['icon' => 'fa-user-tie', 'color' => 'var(--bleu)', 'desc' => 'Conseiller financier'],
                                        'Fund Manager' => ['icon' => 'fa-university', 'color' => 'var(--violet)', 'desc' => 'Gestionnaire de fonds'],
                                        'Family Office' => ['icon' => 'fa-home', 'color' => 'var(--jaune)', 'desc' => 'Family Office'],
                                        'ESG Advisor' => ['icon' => 'fa-leaf', 'color' => 'var(--accent-green)', 'desc' => 'Conseiller ESG'],
                                        'Independent' => ['icon' => 'fa-user', 'color' => 'var(--text-muted)', 'desc' => 'Indépendant']
                                    ];
                                    foreach ($capacities as $key => $cap): 
                                    ?>
                                    <div class="col-md-4 col-sm-6">
                                        <label class="checkbox-card w-100" onclick="toggleCheckbox(this)">
                                            <input type="checkbox" name="capacity[]" value="<?= htmlspecialchars($key) ?>" 
                                                <?= set_checkbox('capacity[]', $key) ?>>
                                            <div class="checkbox-icon" style="background: <?= $cap['color'] ?>20; color: <?= $cap['color'] ?>;">
                                                <i class="fas <?= $cap['icon'] ?>"></i>
                                            </div>
                                            <div class="fw-semibold" style="color: var(--text-dark);"><?= htmlspecialchars($key) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($cap['desc']) ?></small>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="invalid-feedback capacity-error" style="display: none;">Veuillez sélectionner au moins une capacité</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block mb-3">Types d'investisseurs que vous représentez</label>
                                <div class="row g-3">
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
                                    <div class="col-md-3 col-sm-6">
                                        <label class="checkbox-card w-100 text-center" onclick="toggleCheckbox(this)">
                                            <input type="checkbox" name="investor_types[]" value="<?= htmlspecialchars($key) ?>"
                                                <?= set_checkbox('investor_types[]', $key) ?>>
                                            <div class="checkbox-icon mx-auto" style="background: <?= $inv['color'] ?>20; color: <?= $inv['color'] ?>;">
                                                <i class="fas <?= $inv['icon'] ?>"></i>
                                            </div>
                                            <div class="small fw-semibold" style="color: var(--text-dark);"><?= htmlspecialchars($key) ?></div>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Modèle d'engagement préféré</label>
                                <select name="engagement_model" class="form-select" data-validation="max:50">
                                    <option value="">Sélectionner...</option>
                                    <option value="Success-Based" <?= set_select('engagement_model', 'Success-Based') ?>>Success-Based (Succès uniquement)</option>
                                    <option value="Retainer + Success" <?= set_select('engagement_model', 'Retainer + Success') ?>>Retainer + Success (Forfait + Succès)</option>
                                    <option value="Referral" <?= set_select('engagement_model', 'Referral') ?>>Referral (Parrainage)</option>
                                    <option value="To be negotiated" <?= set_select('engagement_model', 'To be negotiated') ?>>À négocier</option>
                                </select>
                                <div class="invalid-feedback" id="engagement_model-error"></div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(3)">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <button type="button" class="btn btn-nav btn-next" onclick="validateStep(4, 5)" id="nextBtn4">
                                    Continuer <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Étape 5: Finalisation -->
                        <div class="form-section" id="step-5">
                            <h2 class="section-title">
                                <i class="fas fa-check-circle" style="color: var(--accent-green);"></i>
                                Finalisation
                            </h2>
                            <p class="section-subtitle">Vérifiez vos informations avant soumission</p>

                            <div class="bg-light p-4 rounded-3 mb-4" id="summary"></div>

                            <div class="form-check mb-4 p-3 border rounded-3 bg-white">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label ms-2" for="terms">
                                    <strong>J'accepte les conditions d'utilisation <span class="text-danger">*</span></strong><br>
                                    <small class="text-muted">
                                        Je confirme que toutes les informations fournies sont exactes et j'accepte que AGF vérifie mon profil 
                                        avant toute autorisation. <a href="#" style="color: var(--secondary-green);" data-bs-toggle="modal" data-bs-target="#termsModal">Lire les conditions</a>
                                    </small>
                                </label>
                                <div class="invalid-feedback" id="terms-error">Vous devez accepter les conditions pour continuer.</div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(4)">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <button type="submit" class="btn btn-nav btn-submit" id="submitBtn">
                                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Soumettre ma candidature</span>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Lien statut -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Déjà inscrit ? <a href="<?= base_url('eoi_register/status') ?>" style="color: var(--secondary-green); font-weight: 600;">
                            Vérifier mon statut <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Conditions -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conditions d'utilisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ==================== VARIABLES GLOBALES ====================
        let currentStep = 1;
        const totalSteps = 5;
        let isSubmitting = false;

        // ==================== INITIALISATION ====================
        document.addEventListener('DOMContentLoaded', function() {
            initTooltips();
            initCountrySearch('juridiction');
            initCountrySearch('operation');
            initCheckboxStates();
            initValidation();
            restoreFormState();
        });

        function initTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        function initCheckboxStates() {
            document.querySelectorAll('.checkbox-card input:checked').forEach(cb => {
                cb.closest('.checkbox-card').classList.add('selected');
            });
        }

        function initValidation() {
            document.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const errorId = this.name + '-error';
                    const errorElement = document.getElementById(errorId);
                    if (errorElement) errorElement.style.display = 'none';
                });
            });
        }

        function restoreFormState() {
            // Restaurer les pays sélectionnés si présents
            <?php if (set_value('id_pays_jurisdiction')): ?>
            setTimeout(() => {
                const jurId = "<?= set_value('id_pays_jurisdiction') ?>";
                const jurName = "<?= $selected_juridiction['nom'] ?? '' ?>";
                if (jurId && jurName) {
                    document.getElementById('juridiction_search').value = jurName;
                    document.getElementById('selected_juridiction_id').value = jurId;
                    document.getElementById('selected_juridiction_name').textContent = jurName;
                    document.getElementById('selected_juridiction_display').classList.remove('d-none');
                }
            }, 100);
            <?php endif; ?>

            <?php if (set_value('id_pays_operation')): ?>
            setTimeout(() => {
                const opId = "<?= set_value('id_pays_operation') ?>";
                const opName = "<?= $selected_operation['nom'] ?? '' ?>";
                if (opId && opName) {
                    document.getElementById('operation_search').value = opName;
                    document.getElementById('selected_operation_id').value = opId;
                    document.getElementById('selected_operation_name').textContent = opName;
                    document.getElementById('selected_operation_display').classList.remove('d-none');
                }
            }, 100);
            <?php endif; ?>
        }

        // ==================== FONCTIONS PAYS ====================
        function initCountrySearch(type) {
            const searchInput = document.getElementById(`${type}_search`);
            const dropdown = document.getElementById(`${type}_dropdown`);
            
            if (!searchInput || !dropdown) return;

            let searchTimeout;

            // Recherche en temps réel
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.toLowerCase().trim();
                
                searchTimeout = setTimeout(() => {
                    const options = dropdown.querySelectorAll('.pays-option');
                    let hasResults = false;
                    
                    options.forEach(option => {
                        const searchTerms = option.getAttribute('data-search').toLowerCase();
                        if (searchTerms.includes(query) || query === '') {
                            option.style.display = 'block';
                            hasResults = true;
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    
                    if (hasResults && document.activeElement === searchInput) {
                        dropdown.style.display = 'block';
                    } else {
                        dropdown.style.display = 'none';
                    }
                }, 200);
            });

            // Focus
            searchInput.addEventListener('focus', function() {
                const options = dropdown.querySelectorAll('.pays-option');
                options.forEach(opt => opt.style.display = 'block');
                dropdown.style.display = 'block';
            });

            // Clic extérieur
            document.addEventListener('click', function(e) {
                if (!e.target.closest(`#${type}_search`) && !e.target.closest(`#${type}_dropdown`)) {
                    dropdown.style.display = 'none';
                }
            });

            // Navigation clavier
            searchInput.addEventListener('keydown', function(e) {
                const visibleOptions = Array.from(dropdown.querySelectorAll('.pays-option'))
                    .filter(opt => opt.style.display !== 'none');
                
                let currentIndex = visibleOptions.findIndex(opt => opt.classList.contains('active'));
                
                visibleOptions.forEach(opt => opt.classList.remove('active'));
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (visibleOptions.length > 0) {
                        const nextIndex = (currentIndex + 1) % visibleOptions.length;
                        visibleOptions[nextIndex].classList.add('active');
                        visibleOptions[nextIndex].scrollIntoView({ block: 'nearest' });
                    }
                } 
                else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (visibleOptions.length > 0) {
                        const prevIndex = currentIndex <= 0 ? visibleOptions.length - 1 : currentIndex - 1;
                        visibleOptions[prevIndex].classList.add('active');
                        visibleOptions[prevIndex].scrollIntoView({ block: 'nearest' });
                    }
                } 
                else if (e.key === 'Enter') {
                    e.preventDefault();
                    const activeOption = dropdown.querySelector('.pays-option.active');
                    if (activeOption) {
                        selectPays(activeOption, type);
                    } else if (visibleOptions.length === 1) {
                        selectPays(visibleOptions[0], type);
                    }
                } 
                else if (e.key === 'Escape') {
                    dropdown.style.display = 'none';
                }
            });
        }

        window.selectPays = function(element, type) {
            const id = element.getAttribute('data-id');
            const name = element.getAttribute('data-value');
            
            document.getElementById(`${type}_search`).value = name;
            document.getElementById(`selected_${type}_id`).value = id;
            document.getElementById(`selected_${type}_name`).textContent = name;
            document.getElementById(`selected_${type}_display`).classList.remove('d-none');
            
            document.getElementById(`${type}_dropdown`).style.display = 'none';
            
            document.getElementById(`${type}_search`).classList.remove('is-invalid');
            document.getElementById(`${type}-error`).style.display = 'none';
        };

        window.clearCountrySelection = function(type) {
            document.getElementById(`${type}_search`).value = '';
            document.getElementById(`selected_${type}_id`).value = '';
            document.getElementById(`selected_${type}_display`).classList.add('d-none');
            document.getElementById(`${type}_search`).focus();
        };

        function validateCountry(type) {
            const hasValue = document.getElementById(`selected_${type}_id`).value && 
                            document.getElementById(`selected_${type}_id`).value !== '';
            
            if (!hasValue) {
                document.getElementById(`${type}_search`).classList.add('is-invalid');
                document.getElementById(`${type}-error`).style.display = 'block';
                document.getElementById(`${type}-error`).textContent = 'Veuillez sélectionner un pays';
                return false;
            } else {
                document.getElementById(`${type}_search`).classList.remove('is-invalid');
                document.getElementById(`${type}-error`).style.display = 'none';
                return true;
            }
        }

        // ==================== FONCTIONS DE NAVIGATION ====================
        window.goToStep = function(step) {
            if (step < 1 || step > totalSteps) return;

            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            
            if (step > currentStep) {
                for (let i = currentStep; i < step; i++) {
                    document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
                }
            }

            currentStep = step;
            
            document.getElementById(`step-${currentStep}`).classList.add('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');

            if (currentStep === 5) {
                generateSummary();
            }

            document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

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
            }
        };

        // ==================== VALIDATION DES ÉTAPES ====================
        function validateStep1() {
            let isValid = true;
            
            // Nom complet
            const fullName = document.querySelector('input[name="full_name"]');
            if (!fullName.value || fullName.value.length < 3) {
                showFieldError(fullName, 'full_name-error', 'Le nom complet est requis (min 3 caractères)');
                isValid = false;
            } else {
                hideFieldError(fullName, 'full_name-error');
            }

            // Email
            const email = document.querySelector('input[name="email"]');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value || !emailRegex.test(email.value)) {
                showFieldError(email, 'email-error', 'Email professionnel valide requis');
                isValid = false;
            } else {
                hideFieldError(email, 'email-error');
            }

            return isValid;
        }

        function validateStep2() {
            let isValid = true;
            
            if (!validateCountry('juridiction')) isValid = false;
            if (!validateCountry('operation')) isValid = false;
            
            return isValid;
        }

        function validateStep3() {
            return true; // Pas de champs requis obligatoires
        }

        function validateStep4() {
            let isValid = true;
            
            const capacities = document.querySelectorAll('input[name="capacity[]"]:checked');
            if (capacities.length === 0) {
                document.querySelector('.capacity-error').style.display = 'block';
                isValid = false;
            } else {
                document.querySelector('.capacity-error').style.display = 'none';
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
            } else {
                terms.classList.remove('is-invalid');
                document.getElementById('terms-error').style.display = 'none';
            }

            return isValid;
        }

        function showFieldError(field, errorId, message) {
            field.classList.add('is-invalid');
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }
        }

        function hideFieldError(field, errorId) {
            field.classList.remove('is-invalid');
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }

        // ==================== FONCTIONS UTILITAIRES ====================
        window.toggleCheckbox = function(card) {
            const checkbox = card.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            card.classList.toggle('selected', checkbox.checked);
            
            if (currentStep === 4) {
                const capacities = document.querySelectorAll('input[name="capacity[]"]:checked');
                if (capacities.length > 0) {
                    document.querySelector('.capacity-error').style.display = 'none';
                }
            }
        };

        function generateSummary() {
            const summary = document.getElementById('summary');
            
            const fullName = document.querySelector('input[name="full_name"]').value || 'Non renseigné';
            const email = document.querySelector('input[name="email"]').value || 'Non renseigné';
            const firmName = document.querySelector('input[name="firm_name"]').value || 'Non renseigné';
            
            const jurName = document.getElementById('selected_juridiction_name').textContent || 'Non sélectionné';
            const opName = document.getElementById('selected_operation_name').textContent || 'Non sélectionné';
            
            const capacities = Array.from(document.querySelectorAll('input[name="capacity[]"]:checked')).map(cb => cb.value);
            const investors = Array.from(document.querySelectorAll('input[name="investor_types[]"]:checked')).map(cb => cb.value);
            
            const regulatoryStatus = document.querySelector('select[name="regulatory_status"] option:checked')?.text || 'Non spécifié';
            const engagementModel = document.querySelector('select[name="engagement_model"] option:checked')?.text || 'Non spécifié';

            let html = `
                <div class="summary-section">
                    <h6><i class="fas fa-user-circle" style="color: var(--primary-green);"></i> Identité</h6>
                    <div class="summary-item">
                        <span class="summary-label">Nom complet</span>
                        <span class="summary-value">${escapeHtml(fullName)}</span>
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
                    <h6><i class="fas fa-map-marked-alt" style="color: var(--bleu);"></i> Localisation</h6>
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
                    <h6><i class="fas fa-shield-alt" style="color: var(--violet);"></i> Régulation</h6>
                    <div class="summary-item">
                        <span class="summary-label">Statut</span>
                        <span class="summary-value">${escapeHtml(regulatoryStatus)}</span>
                    </div>
                </div>
            `;

            if (capacities.length > 0) {
                html += `
                    <div class="summary-section">
                        <h6><i class="fas fa-briefcase" style="color: var(--orange);"></i> Capacités</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            ${capacities.map(cap => `<span class="badge bg-success bg-opacity-10 text-success p-2">${escapeHtml(cap)}</span>`).join('')}
                        </div>
                    </div>
                `;
            }

            if (investors.length > 0) {
                html += `
                    <div class="summary-section">
                        <h6><i class="fas fa-users" style="color: var(--accent-green);"></i> Types d'investisseurs</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            ${investors.map(inv => `<span class="badge bg-info bg-opacity-10 text-info p-2">${escapeHtml(inv)}</span>`).join('')}
                        </div>
                    </div>
                `;
            }

            html += `
                <div class="summary-section">
                    <h6><i class="fas fa-handshake" style="color: var(--orange);"></i> Engagement</h6>
                    <div class="summary-item">
                        <span class="summary-label">Modèle préféré</span>
                        <span class="summary-value">${escapeHtml(engagementModel)}</span>
                    </div>
                </div>
            `;

            summary.innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ==================== SOUMISSION ====================
        document.getElementById('eoiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (isSubmitting) return;

            if (!validateStep(5)) {
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            isSubmitting = true;

            // Simulation d'envoi (remplacer par this.submit() en production)
            setTimeout(() => {
                showFlashMessage('success', 'Votre candidature a été soumise avec succès !');
                
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                isSubmitting = false;

                // Redirection après succès
                setTimeout(() => {
                    window.location.href = '<?= base_url('eoi_register/success') ?>';
                }, 3000);
            }, 2000);
        });

        function showFlashMessage(type, message) {
            const flashDiv = document.getElementById('flashMessages');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            
            flashDiv.innerHTML = `
                <div class="alert ${alertClass} alert-custom mb-4">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} fa-2x"></i>
                    <div>
                        <strong>${type === 'success' ? 'Succès !' : 'Erreur'}</strong><br>
                        ${message}
                    </div>
                </div>
            `;
            
            flashDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
</body>
</html>