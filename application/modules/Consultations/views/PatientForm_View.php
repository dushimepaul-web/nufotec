<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
/* ============================================================
   CSS FONCTIONNEL REQUIS (pas un habillage)
   Assuré par le JS de cette page : wizard (étapes), upload,
   autocomplétion, variables CSS utilisées en style inline/JS.
   ============================================================ */
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-muted: #6B9080;
    --accent: #C9A227;
    --accent-bg: #FDF8E8;
    --error: #C53030;
    --error-bg: #FFF5F5;
    --success: #2F855A;
    --success-bg: #F0FFF4;
    --border: #E2E8F0;
    --text-secondary: #4A5568;
    --text-muted: #718096;
    --shadow-md: 0 4px 6px rgba(0,0,0,0.05);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
}

.step-content.hidden { display: none !important; }

.progress-step {
    width: 36px;
    height: 36px;
    border: 2px solid var(--border);
    background: #fff;
    color: var(--text-muted);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
.progress-step.active {
    border-color: var(--primary);
    background: var(--primary);
    color: #fff;
}
.progress-step.completed {
    border-color: var(--success);
    background: var(--success);
    color: #fff;
}

.autocomplete-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: .5rem;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
    box-shadow: var(--shadow-md);
}
.autocomplete-item {
    padding: .625rem .75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.autocomplete-item:hover,
.autocomplete-item:focus {
    background: var(--accent-bg);
}

.upload-box {
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.upload-box:hover {
    border-color: var(--primary-muted) !important;
    background: var(--accent-bg);
}
.upload-box input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
</style>

<a href="#main-content" class="visually-hidden-focusable p-3 text-white text-decoration-none" style="background: var(--primary);"><i class="bi bi-skip-forward me-2"></i> Aller au contenu principal</a>

<!-- SECTION HERO -->
<section class="consultation-hero text-center text-white" style="background: linear-gradient(135deg, #0f4c3a 0%, #1a6b52 100%);">
    <div class="container py-5">
        <h1 id="hero-title" class="display-5 fw-bold mb-2"><i class="bi bi-heart-pulse me-2"></i> Consultation en ligne</h1>
        <p id="hero-subtitle" class="opacity-75 mb-0"><i class="bi bi-shield-check me-2"></i> Demande de consultation - NUFOTEC Burundi</p>
        <p class="opacity-75 mb-0 mt-2">Remplissez ce formulaire pour demander votre consultation &agrave; distance. Tout ce que vous saisissez est transmis en toute confidentialit&eacute; directement au m&eacute;decin <strong>sur WhatsApp</strong>.</p>
        <div class="mx-auto mt-3 d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.15); font-size: 0.9rem;">
            <i class="bi bi-info-circle me-1"></i> &Agrave; la fin du formulaire : montant &agrave; payer, puis preuve de paiement (capture d'&eacute;cran ou PDF)
        </div>
    </div>
</section>

<!-- FORMULAIRE PRINCIPAL -->
<div class="container py-5" id="main-content">
    <div class="row g-4">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Barre de progression -->
            <div class="consultation-progress card border-0 rounded-4 shadow-sm mb-4"
                 role="progressbar"
                 aria-valuenow="25"
                 aria-valuemin="0"
                 aria-valuemax="100">
                <div class="card-body py-4">
                    <div class="progress-steps d-flex justify-content-between align-items-center mb-3 px-2">
                        <div class="progress-step active" id="step1-indicator"><i class="bi bi-person"></i></div>
                        <div class="progress-step" id="step2-indicator"><i class="bi bi-hospital"></i></div>
                        <div class="progress-step" id="step3-indicator"><i class="bi bi-file-earmark-medical"></i></div>
                        <div class="progress-step" id="step4-indicator"><i class="bi bi-check-lg"></i></div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" id="progressBar" style="background: linear-gradient(90deg, var(--primary), var(--primary-light));"></div>
                    </div>
                </div>
            </div>

            <!-- Carte formulaire -->
            <div class="consultation-card card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form id="consultationForm"
                          action="<?= base_url('patient-form/create') ?>"
                          method="POST"
                          enctype="multipart/form-data"
                          novalidate>

                        <!-- Champs cachés pour les données du médecin -->
                        <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($medecin['id'] ?? '') ?>">
                        <input type="hidden" name="doctor_uuid" value="<?= htmlspecialchars($medecin['uuid'] ?? '') ?>">
                        <input type="hidden" name="doctor_nom" value="<?= htmlspecialchars($medecin['nom'] ?? '') ?>">
                        <input type="hidden" name="doctor_specialite" value="<?= htmlspecialchars($medecin['specialite'] ?? '') ?>">
                        <input type="hidden" name="consultation_prix" value="<?= htmlspecialchars($medecin['honoraires_consultation'] ?? 50) ?>">
                        <input type="hidden" name="consultation_devise" value="<?= htmlspecialchars($medecin['currency'] ?? 'USD') ?>">

                        <!-- CSRF Token -->
                        <input type="hidden"
                               name="<?= $this->security->get_csrf_token_name() ?>"
                               value="<?= $this->security->get_csrf_hash() ?>">

                        <!-- ÉTAPE 1: Informations Personnelles -->
                        <div class="step-content" id="step1">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-center rounded-3 text-success bg-warning-subtle bg-opacity-50 flex-shrink-0" style="width: 56px; height: 56px;">
                                    <i class="bi bi-person-badge fs-3"></i>
                                </div>
                                <div class="header-text">
                                    <h5 class="fw-bold mb-1"><i class="bi bi-person me-1"></i> Vos informations personnelles</h5>
                                    <p class="text-muted small mb-0"><i class="bi bi-lock me-1"></i> Vos données sont confidentielles</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="full_name">
                                        <i class="bi bi-person me-1"></i> Nom complet <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text"
                                               class="form-control"
                                               name="full_name"
                                               id="full_name"
                                               placeholder="Jean Dupont"
                                               required
                                               minlength="3"
                                               maxlength="100"
                                               value="<?= htmlspecialchars($this->session->userdata('fullname') ?: $this->session->userdata('fu') ?: '', ENT_QUOTES, 'UTF-8') ?>"
                                               <?= ($this->session->userdata('fullname') || $this->session->userdata('fu')) ? 'readonly' : '' ?>>
                                        <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Veuillez entrer votre nom complet (minimum 3 caractères)</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="age">
                                        <i class="bi bi-calendar me-1"></i> Âge <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                        <input type="number"
                                               class="form-control"
                                               name="age"
                                               id="age"
                                               placeholder="Ex: 30"
                                               required
                                               min="1"
                                               max="120"
                                               value="<?= set_value('age'); ?>">
                                        <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Âge invalide (1-120 ans)</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-globe me-1"></i> Pays de résidence <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                            <input type="text"
                                                   class="form-control"
                                                   id="country_search"
                                                   placeholder="Rechercher votre pays"
                                                   autocomplete="off"
                                                   required>
                                            <input type="hidden" name="country" id="selected_country" value="<?= set_value('country'); ?>">
                                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Veuillez sélectionner votre pays</div>
                                        </div>
                                        <div id="autocomplete_list" class="autocomplete-results" style="display: none;"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="weight">
                                        <i class="bi bi-speedometer me-1"></i> Poids (kg) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-speedometer"></i></span>
                                        <input type="number"
                                               class="form-control"
                                               name="weight"
                                               id="weight"
                                               placeholder="Ex: 70"
                                               required
                                               min="1"
                                               max="300"
                                               step="0.1"
                                               value="<?= set_value('weight'); ?>">
                                        <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Poids invalide (1-300 kg)</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="height">
                                        <i class="bi bi-rulers me-1"></i> Taille (cm) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                                        <input type="number"
                                               class="form-control"
                                               name="height"
                                               id="height"
                                               placeholder="Ex: 170"
                                               required
                                               min="50"
                                               max="250"
                                               value="<?= set_value('height'); ?>">
                                        <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Taille invalide (50-250 cm)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-between mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary" disabled><i class="bi bi-arrow-left me-1"></i> Précédent</button>
                                <button type="button" class="btn btn-primary px-4" onclick="nextStep(1)">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        <!-- ÉTAPE 2: Symptômes -->
                        <div class="step-content hidden" id="step2">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-center rounded-3 text-success bg-warning-subtle bg-opacity-50 flex-shrink-0" style="width: 56px; height: 56px;">
                                    <i class="bi bi-hospital fs-3"></i>
                                </div>
                                <div class="header-text">
                                    <h5 class="fw-bold mb-1"><i class="bi bi-activity me-1"></i> Vos symptômes</h5>
                                    <p class="text-muted small mb-0"><i class="bi bi-pencil-square me-1"></i> Décrivez précisément vos symptômes</p>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold" for="symptoms">
                                    <i class="bi bi-chat-text me-1"></i> Description détaillée <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control"
                                          name="symptoms"
                                          id="symptoms"
                                          rows="6"
                                          required
                                          minlength="20"
                                          placeholder="Décrivez vos symptômes : depuis quand, leur intensité, les facteurs qui les aggravent ou les soulagent..."><?= set_value('symptoms'); ?></textarea>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i> Veuillez décrire vos symptômes (minimum 20 caractères)</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="symptoms_duration">
                                    <i class="bi bi-clock-history me-1"></i> Depuis combien de temps ?
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <select class="form-select" name="symptoms_duration" id="symptoms_duration">
                                        <option value="">Sélectionnez une durée</option>
                                        <option value="24h" <?= set_select('symptoms_duration', '24h'); ?>>Moins de 24 heures</option>
                                        <option value="2-3j" <?= set_select('symptoms_duration', '2-3j'); ?>>2-3 jours</option>
                                        <option value="1sem" <?= set_select('symptoms_duration', '1sem'); ?>>1 semaine</option>
                                        <option value="2sem" <?= set_select('symptoms_duration', '2sem'); ?>>2 semaines</option>
                                        <option value="1mois" <?= set_select('symptoms_duration', '1mois'); ?>>Plus d'un mois</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold d-block">
                                    <i class="bi bi-question-circle me-1"></i> Avez-vous déjà consulté un médecin pour ces symptômes ?
                                </label>
                                <div class="d-flex flex-wrap gap-4 mt-2">
                                    <label class="radio-option form-check me-4">
                                        <input class="form-check-input me-2" type="radio" name="previous_consultation" value="yes" <?= set_radio('previous_consultation', 'yes'); ?>>
                                        <span class="form-check-label"><i class="bi bi-check-lg me-1"></i> Oui</span>
                                    </label>
                                    <label class="radio-option form-check">
                                        <input class="form-check-input me-2" type="radio" name="previous_consultation" value="no" <?= set_radio('previous_consultation', 'no', true); ?>>
                                        <span class="form-check-label"><i class="bi bi-x-lg me-1"></i> Non</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-between mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)"><i class="bi bi-arrow-left me-1"></i> Précédent</button>
                                <button type="button" class="btn btn-primary px-4" onclick="nextStep(2)">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        <!-- ÉTAPE 3: Documents -->
                        <div class="step-content hidden" id="step3">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-center rounded-3 text-success bg-warning-subtle bg-opacity-50 flex-shrink-0" style="width: 56px; height: 56px;">
                                    <i class="bi bi-file-earmark-medical fs-3"></i>
                                </div>
                                <div class="header-text">
                                    <h5 class="fw-bold mb-1"><i class="bi bi-folder me-1"></i> Documents médicaux (optionnel)</h5>
                                    <p class="text-muted small mb-0"><i class="bi bi-shield-lock me-1"></i> Sécurisé et confidentiel</p>
                                </div>
                            </div>

                            <div class="card upload-section mb-3 border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary bg-primary-subtle flex-shrink-0" style="width: 42px; height: 42px;">
                                            <i class="bi bi-file-earmark-text fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1"><i class="bi bi-clipboard-data me-1"></i> Analyses médicales</h5>
                                            <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Téléchargez vos analyses, radios, résultats d'examens (PDF, JPG, PNG)</p>
                                        </div>
                                    </div>

                                    <div class="upload-box text-center" tabindex="0" style="border: 2px dashed var(--border); border-radius: 10px; padding: 25px 15px; background: #fff;">
                                        <i class="bi bi-cloud-arrow-up fs-1 text-primary d-block mb-2"></i>
                                        <span class="d-block fw-semibold mb-1">Cliquez pour télécharger</span>
                                        <small class="text-muted"><i class="bi bi-file-earmark me-1"></i> PDF, JPG, PNG (max 5 Mo)</small>
                                        <input type="file" name="medical_docs[]" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewFiles(this, 'medical-preview')">
                                    </div>
                                    <div id="medical-preview" class="upload-preview mt-3 rounded-3 bg-light p-3" style="display: none;"></div>
                                </div>
                            </div>

                            <div class="card upload-section mb-3 border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary bg-primary-subtle flex-shrink-0" style="width: 42px; height: 42px;">
                                            <i class="bi bi-capsule fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1"><i class="bi bi-prescription me-1"></i> Ordonnances précédentes</h5>
                                            <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Téléchargez vos ordonnances antérieures (PDF, JPG, PNG)</p>
                                        </div>
                                    </div>

                                    <div class="upload-box" tabindex="0" style="border: 2px dashed var(--border); border-radius: 12px; padding: 25px 15px; background: #fff; text-align: center;">
                                        <i class="bi bi-cloud-arrow-up fs-1 text-primary d-block mb-2"></i>
                                        <span class="d-block fw-semibold mb-1">Cliquez pour télécharger</span>
                                        <small class="text-muted"><i class="bi bi-file-earmark me-1"></i> PDF, JPG, PNG (max 5 Mo)</small>
                                        <input type="file" name="prescriptions[]" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewFiles(this, 'prescription-preview')">
                                    </div>
                                    <div id="prescription-preview" class="upload-preview mt-3 rounded-3 bg-light p-3" style="display: none;"></div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-between mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(3)"><i class="bi bi-arrow-left me-1"></i> Précédent</button>
                                <button type="button" class="btn btn-primary px-4" onclick="nextStep(3)">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        <!-- ÉTAPE 4: Confirmation -->
                        <div class="step-content hidden" id="step4">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-center rounded-3 text-success bg-warning-subtle bg-opacity-50 flex-shrink-0" style="width: 56px; height: 56px;">
                                    <i class="bi bi-check-circle fs-3"></i>
                                </div>
                                <div class="header-text">
                                    <h5 class="fw-bold mb-1"><i class="bi bi-eye me-1"></i> Récapitulatif de votre consultation</h5>
                                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Vérifiez vos informations avant validation</p>
                                </div>
                            </div>

                            <div class="consultation-summary border rounded-4 p-4 bg-body-tertiary mb-4">
                                <div class="fw-bold mb-3"><i class="bi bi-person me-1"></i> Vos informations</div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="summary-item bg-white border rounded-3 p-3 h-100">
                                            <label class="text-muted small text-uppercase d-block mb-1"><i class="bi bi-person me-1"></i> Nom complet</label>
                                            <div class="fw-semibold" id="summary-name">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-item bg-white border rounded-3 p-3 h-100">
                                            <label class="text-muted small text-uppercase d-block mb-1"><i class="bi bi-calendar me-1"></i> Âge</label>
                                            <div class="fw-semibold" id="summary-age">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-item bg-white border rounded-3 p-3 h-100">
                                            <label class="text-muted small text-uppercase d-block mb-1"><i class="bi bi-globe me-1"></i> Pays</label>
                                            <div class="fw-semibold" id="summary-country">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-item bg-white border rounded-3 p-3 h-100">
                                            <label class="text-muted small text-uppercase d-block mb-1"><i class="bi bi-rulers me-1"></i> Poids / Taille</label>
                                            <div class="fw-semibold" id="summary-size">-</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-top pt-3 mt-3">
                                    <div class="fw-bold mb-2"><i class="bi bi-activity me-1"></i> Vos symptômes</div>
                                    <div class="summary-item bg-white border rounded-3 p-3">
                                        <div class="value" id="summary-symptoms" style="white-space: pre-wrap;">-</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 rounded-3 px-3 py-3" style="background: var(--accent-bg); border: 1px solid rgba(201, 162, 39, 0.25);">
                                    <span class="fw-bold"><i class="bi bi-cash-stack me-1"></i> Montant total à payer</span>
                                    <?php
                                        $taux = $this->config->item('taux_devise');
                                        $prix_usd = isset($medecin['honoraires_consultation']) ? (float)$medecin['honoraires_consultation'] : 50;
                                        $prix_eur = $prix_usd * ($taux['USD_TO_EUR'] ?? 0.92);
                                        $prix_bif = $prix_usd * ($taux['USD_TO_BIF'] ?? 2900);
                                    ?>
                                    <span id="summary-total">
                                        <div class="price-value fs-5 fw-bold">
                                            <?= number_format($prix_usd, 2) ?> USD
                                        </div>
                                    </span>
                                </div>
                            </div>

                            <!-- INFO PAIEMENT -->
                            <div class="alert alert-info d-flex align-items-start gap-2 small mb-4" role="alert" aria-live="polite">
                                <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                                <span>
                                    <strong>Comment payer votre consultation :</strong>
                                    <ol class="mb-0 ps-3 mt-1">
                                        <li>Choisissez votre moyen de paiement ci-dessous.</li>
                                        <li>Payez le montant affiché : <strong><?= number_format($prix_usd, 2) ?> USD (Prix Burundi : 40 000 FBu)</strong>.</li>
                                        <li>Téléchargez votre preuve de paiement : capture d'écran de l'opération ou <strong>PDF</strong> du reçu.</li>
                                    </ol>
                                    <span class="d-block mt-2">Votre demande complète est envoyée directement au médecin <strong>sur WhatsApp</strong>.</span>
                                </span>
                            </div>

                            <!-- MODE DE PAIEMENT + PREUVE -->
                            <div class="payment-pick-section border rounded-4 p-4 bg-body-tertiary mb-4">
                                <div class="fw-bold mb-1"><i class="bi bi-wallet2 me-1"></i> Mode de paiement</div>
                                <p class="payment-help text-muted small mb-3">
                                    Effectuez le paiement sur votre téléphone via le moyen que vous choisissez, puis joignez la preuve : capture d'écran de l'opération ou reçu en <strong>PDF</strong>.
                                </p>
                                <div class="row g-3" id="paymentOptions">
                                    <?php foreach ($mode_payements as $mode): ?>
                                    <div class="col-md-6">
                                        <label class="payment-option-card form-check p-3 border rounded-3 d-flex align-items-center gap-3 w-100 mb-0">
                                            <input class="form-check-input mt-0 flex-shrink-0" type="radio" name="payment_method" value="<?= htmlspecialchars($mode['description']) ?>" required>
                                            <span class="payment-option-icon d-flex align-items-center justify-content-center flex-shrink-0 text-primary bg-primary-subtle rounded-3" style="width: 42px; height: 42px;"><i class="bi bi-phone"></i></span>
                                            <span>
                                                <span class="payment-option-name d-block fw-bold"><?= htmlspecialchars($mode['description']) ?></span>
                                                <?php if (!empty($mode['numero_compte']) || !empty($mode['nom_compte'])): ?>
                                                <span class="payment-option-details d-block small text-muted">
                                                    <?= htmlspecialchars($mode['numero_compte'] ?? '') ?><?= (!empty($mode['numero_compte']) && !empty($mode['nom_compte'])) ? ' — ' : '' ?><?= htmlspecialchars($mode['nom_compte'] ?? '') ?>
                                                </span>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="payment-upload-box mt-3 pt-3">
                                    <div class="payment-note alert alert-warning d-flex align-items-start gap-2" role="alert" aria-live="polite">
                                        <i class="bi bi-lightning-charge-fill mt-1"></i>
                                        <span>
                                            <strong>Important :</strong> après l'envoi de votre demande, effectuez le paiement si ce n'est pas encore fait, puis joignez la preuve ci-dessous : capture d'écran de l'opération ou <strong>PDF</strong> du reçu. Votre dossier complet est transmis au médecin <strong>sur WhatsApp</strong> pour confirmer votre consultation.
                                        </span>
                                    </div>
                                    <label class="form-label fw-semibold" for="paymentProof">
                                        <i class="bi bi-camera me-1"></i> Preuve de paiement (capture d'écran ou <strong>PDF</strong>) <span class="optional text-muted">(optionnel)</span>
                                    </label>
                                    <input type="file" class="form-control" id="paymentProof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="payment-hint text-muted d-block mt-2">Si vous payez maintenant, joignez ici la capture ou le <strong>PDF</strong> du reçu. Sinon, envoyez-la au médecin sur WhatsApp après le paiement.</small>
                                </div>
                            </div>

                            <div class="terms-checkbox d-flex align-items-start gap-3 p-3 rounded-3 mb-3" style="background: var(--accent-bg); border: 1px solid var(--border);">
                                <input type="checkbox" name="terms" id="terms" required class="form-check-input mt-1 flex-shrink-0" style="width: 20px; height: 20px;">
                                <label class="form-check-label" for="terms">
                                    <i class="bi bi-file-text me-1"></i> J'accepte les <a href="<?= base_url('conditions'); ?>" target="_blank">conditions générales</a> et la politique de confidentialité
                                </label>
                            </div>

                            <div class="d-flex gap-2 justify-content-between mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(4)"><i class="bi bi-arrow-left me-1"></i> Précédent</button>
                                <button type="submit" class="btn btn-success px-4" id="submitBtn">
                                    <i class="bi bi-check-lg me-1"></i> Confirmer et payer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar info -->
        <div class="col-lg-4">
            <div class="info-panel card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div id="doctor-card-container">
                        <div class="doctor-info-card text-white text-center rounded-4 p-4 mb-3 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);">
                            <div class="doctor-avatar-container d-inline-block position-relative">
                                <img src="<?= base_url('attachments/Users/' . htmlspecialchars($medecin['photo'] ?? '')) ?>"
                                     alt="<?= htmlspecialchars($medecin['nom'] ?? 'Médecin') ?>"
                                     class="doctor-avatar rounded-circle border border-4 border-white shadow"
                                     style="width: 90px; height: 90px; object-fit: cover;"
                                     onerror="this.src='<?= base_url('assets/frontend/img/default-doctor.jpg') ?>'">
                                <span class="doctor-status position-absolute rounded-circle border border-3 border-white" style="width: 20px; height: 20px; background: var(--success); bottom: 5px; right: 5px;"></span>
                            </div>
                            <h5 class="doctor-name fw-bold mt-3 mb-1">
                                <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($medecin['prenom'] ?? '') . ' ' . htmlspecialchars($medecin['nom'] ?? 'Médecin') ?>
                            </h5>
                            <p class="doctor-specialty opacity-75 small mb-3">
                                <i class="bi bi-star-fill me-1" style="color: var(--accent);"></i> <?= htmlspecialchars($medecin['specialite'] ?? 'Médecin généraliste') ?>
                            </p>
                            <div class="doctor-price d-inline-flex align-items-center gap-2 rounded-3 p-3" style="background: rgba(255,255,255,0.2);">
                                <div class="price-value fs-3 fw-bold">
                                    <?= number_format($prix_usd ?? 50, 2) ?> USD
                                </div>
                            </div>
                            <div class="burundi-price mt-3 p-2 rounded-3 text-start" style="background: rgba(255,255,255,0.2); border-left: 4px solid var(--accent); font-size: 0.9rem;">
                                <i class="bi bi-geo-alt-fill me-1" style="color: var(--accent);"></i>
                                <strong>Prix Burundi : 40 000 FBu</strong>
                            </div>
                            <?php if (!empty($doctor_count) && $doctor_count > 1): ?>
                            <a style="text-decoration: none;" href="javascript:void(0)" onclick="confirmChangeDoctor()" class="change-doctor-btn btn btn-sm btn-outline-light mt-3 rounded-pill">
                                <i class="bi bi-arrow-left me-1"></i> Changer de médecin
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3"><i class="bi bi-question-circle me-1"></i> Comment ça marche ?</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="d-inline-flex align-items-center justify-content-center rounded-circle text-warning" style="width: 24px; height: 24px; background: var(--accent-bg); font-weight: 600; flex-shrink: 0;">1</span> <i class="bi bi-pencil-square me-1"></i> Remplissez le formulaire</li>
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="d-inline-flex align-items-center justify-content-center rounded-circle text-warning" style="width: 24px; height: 24px; background: var(--accent-bg); font-weight: 600; flex-shrink: 0;">2</span> <i class="bi bi-chat-text me-1"></i> Décrivez vos symptômes</li>
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="d-inline-flex align-items-center justify-content-center rounded-circle text-warning" style="width: 24px; height: 24px; background: var(--accent-bg); font-weight: 600; flex-shrink: 0;">3</span> <i class="bi bi-upload me-1"></i> Téléchargez vos documents</li>
                        <li class="d-flex align-items-center gap-3 py-2 border-bottom"><span class="d-inline-flex align-items-center justify-content-center rounded-circle text-warning" style="width: 24px; height: 24px; background: var(--accent-bg); font-weight: 600; flex-shrink: 0;">4</span> <i class="bi bi-credit-card me-1"></i> Effectuez le paiement</li>
                        <li class="d-flex align-items-center gap-3 py-2"><span class="d-inline-flex align-items-center justify-content-center rounded-circle text-warning" style="width: 24px; height: 24px; background: var(--accent-bg); font-weight: 600; flex-shrink: 0;">5</span> <i class="bi bi-person-check me-1"></i> Recevez la réponse du médecin</li>
                    </ul>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">

                    <div class="mb-3">
                        <strong class="d-block mb-1">
                            <i class="bi bi-wallet2 me-1"></i> Moyens de paiement acceptés :
                        </strong>
                        <?php if(isset($mode_payements) && !empty($mode_payements)): ?>
                            <?php foreach($mode_payements as $mode): ?>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <i style="font-size: 24px;" class="bi <?php
                                    switch($mode['description']) {
                                        case 'Carte bancaire': echo 'bi-credit-card'; break;
                                        case 'PayPal': echo 'bi-paypal'; break;
                                        case 'Mobile Money': echo 'bi-phone'; break;
                                        case 'Virement': echo 'bi-bank'; break;
                                        default: echo 'bi-cash';
                                    }
                                ?>"></i>
                                <span class="fw-medium"><?= htmlspecialchars($mode['description']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <i class="bi bi-credit-card" style="font-size: 24px;"></i>
                                <span>Carte bancaire</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <i class="bi bi-phone" style="font-size: 24px;"></i>
                                <span>Mobile Money</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-shield-check"></i>
                        <small>Paiement sécurisé - Vos données sont protégées</small>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted"><i class="bi bi-headset me-1"></i> Besoin d'aide ?</small><br>
                        <a href="tel:+25779666439" class="d-inline-flex align-items-center gap-2 fw-semibold mt-1 text-decoration-none" style="color: var(--primary);">
                            <i class="bi bi-telephone"></i> <?= $this->Model->get_setting('contact_whatsapp', '+257 79 666 439') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal succès -->
<div class="modal fade" id="successModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-4 text-center">
            <div class="fs-1 text-success mb-2"><i class="bi bi-check-circle-fill"></i></div>
            <h5 class="modal-title fw-bold mb-2" id="modalTitle"><i class="bi bi-emoji-smile me-1"></i> Merci !</h5>
            <p class="text-muted mb-3"><i class="bi bi-envelope-check me-1"></i> Votre demande a été enregistrée avec succès</p>

            <div class="tracking-box rounded-3 p-3 my-3 text-start" style="background: var(--accent-bg); border: 1px solid var(--border);">
                <label class="text-muted small text-uppercase d-block mb-1"><i class="bi bi-upc-scan me-1"></i> Votre numéro de suivi</label>
                <div class="number fs-5 fw-bold" id="trackingNumber" style="color: var(--primary); font-family: monospace;"><i class="bi bi-hash"></i> -</div>
            </div>

            <button type="button" class="btn btn-success w-100" onclick="window.location.href='<?= base_url() ?>'">
                <i class="bi bi-house me-1"></i> Retour à l'accueil
            </button>
        </div>
    </div>
</div>

<?php 
if (!isset($products)) {
    $products = array();
}
?>
<?php include VIEWPATH.'sections/Products_Section.php'; ?>

<script>
const CONFIG = {
    totalSteps: 4,
    currentStep: 1,
    formData: {}
};

function getStepIcon(step) {
    const icons = [
        '<i class="bi bi-person"></i>', 
        '<i class="bi bi-hospital"></i>', 
        '<i class="bi bi-file-earmark-medical"></i>', 
        '<i class="bi bi-check-lg"></i>'
    ];
    return icons[step - 1] || step;
}

function confirmChangeDoctor() {
    if (confirm('Voulez-vous vraiment changer de médecin ? Toutes vos données non enregistrées seront perdues.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('swap-medecin') ?>';
        
        var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
        
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = csrfName;
        input.value = csrfHash;
        form.appendChild(input);
        
        document.body.appendChild(form);
        form.submit();
    }
}

const countries = <?= json_encode($pays ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

document.addEventListener('DOMContentLoaded', function() {
    initializeAutocomplete();
    initializeFormValidation();
    
    const heroTitle = document.querySelector('.consultation-hero h1');
    if (heroTitle) {
        heroTitle.innerHTML = `<i class="bi bi-heart-pulse"></i> Consultation <?= htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? ''), ENT_QUOTES) ?>`;
    }
});

function initializeAutocomplete() {
    const searchInput = document.getElementById('country_search');
    const resultsList = document.getElementById('autocomplete_list');
    const hiddenCountryInput = document.getElementById('selected_country');

    if (!searchInput || !resultsList) return;

    if (hiddenCountryInput.value) {
        const selected = countries.find(c => c.pays === hiddenCountryInput.value);
        if (selected) {
            searchInput.value = selected.pays;
        }
    }

    searchInput.addEventListener('input', function() {
        const val = this.value.toLowerCase().trim();
        resultsList.innerHTML = '';
        
        if (val.length < 2) { 
            resultsList.style.display = 'none'; 
            return; 
        }

        const filtered = countries.filter(c => 
            c.pays && c.pays.toLowerCase().includes(val)
        ).slice(0, 8);
        
        if (filtered.length > 0) {
            filtered.forEach(c => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.innerHTML = `<i class="bi bi-geo-alt"></i> <strong>${escapeHtml(c.pays)}</strong>`;
                div.onclick = function() { 
                    selectCountry(c, searchInput, hiddenCountryInput, resultsList); 
                };
                resultsList.appendChild(div);
            });
            resultsList.style.display = 'block';
        } else {
            resultsList.style.display = 'none';
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
            resultsList.style.display = 'none';
        }
    });
}

function selectCountry(country, searchInput, hiddenInput, resultsList) {
    searchInput.value = country.pays;
    hiddenInput.value = country.pays;
    resultsList.style.display = 'none';
    searchInput.classList.remove('is-invalid');
    searchInput.classList.add('is-valid');
}

function initializeFormValidation() {
    updateProgress();
    loadSavedData();
    
    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
    
    document.querySelectorAll('.upload-box').forEach(box => {
        box.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.querySelector('input[type="file"]').click();
            }
        });
    });
}

function validateField() {
    if (this.checkValidity && this.checkValidity() && this.value.trim()) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else if (this.value.trim()) {
        this.classList.remove('is-valid');
    }
}

function updateProgress() {
    for (let i = 1; i <= CONFIG.totalSteps; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        const panel = document.getElementById(`step${i}`);
        
        if (!indicator || !panel) continue;
        
        if (i < CONFIG.currentStep) {
            indicator.className = 'progress-step completed';
            indicator.innerHTML = '<i class="bi bi-check-lg"></i>';
        } else if (i === CONFIG.currentStep) {
            indicator.className = 'progress-step active';
            indicator.innerHTML = getStepIcon(i);
        } else {
            indicator.className = 'progress-step';
            indicator.innerHTML = getStepIcon(i);
        }
        
        panel.classList.toggle('hidden', i !== CONFIG.currentStep);
    }
    
    const progress = (CONFIG.currentStep / CONFIG.totalSteps) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
}

function nextStep(step) {
    if (!validateStep(step)) {
        showNotification('Veuillez remplir correctement tous les champs obligatoires', 'error');
        return;
    }
    
    saveStepData(step);
    
    if (CONFIG.currentStep < CONFIG.totalSteps) {
        CONFIG.currentStep++;
        updateProgress();
        
        if (CONFIG.currentStep === CONFIG.totalSteps) {
            updateSummary();
        }
        
        document.querySelector('.consultation-card').scrollIntoView({ behavior: 'smooth' });
    }
}

function prevStep(step) {
    if (CONFIG.currentStep > 1) {
        CONFIG.currentStep--;
        updateProgress();
        document.querySelector('.consultation-card').scrollIntoView({ behavior: 'smooth' });
    }
}

function validateStep(step) {
    let isValid = true;
    const currentPanel = document.getElementById(`step${step}`);
    
    if (!currentPanel) return false;
    
    const requiredFields = currentPanel.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (field.type === 'checkbox') {
            if (!field.checked) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        } else if (field.type === 'file') {
            // Champs optionnels, pas de validation stricte
        } else {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        }
    });
    
    switch(step) {
        case 1:
            const age = document.getElementById('age');
            if (age && age.value && (age.value < 1 || age.value > 120)) {
                age.classList.add('is-invalid');
                isValid = false;
            }
            
            const weight = document.getElementById('weight');
            if (weight && weight.value && (weight.value < 1 || weight.value > 300)) {
                weight.classList.add('is-invalid');
                isValid = false;
            }
            
            const height = document.getElementById('height');
            if (height && height.value && (height.value < 50 || height.value > 250)) {
                height.classList.add('is-invalid');
                isValid = false;
            }
            
            const country = document.getElementById('selected_country');
            if (!country || !country.value) {
                document.getElementById('country_search').classList.add('is-invalid');
                isValid = false;
            }
            break;
            
        case 2:
            const symptoms = document.getElementById('symptoms');
            if (symptoms && symptoms.value.length < 20) {
                symptoms.classList.add('is-invalid');
                isValid = false;
            }
            break;
            
        case 3:
            // Documents optionnels
            break;
    }
    
    return isValid;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'error' ? 'var(--error-bg)' : 'var(--accent-bg)'};
        color: ${type === 'error' ? 'var(--error)' : 'var(--primary)'};
        border: 1px solid ${type === 'error' ? 'var(--error)' : 'var(--primary)'};
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    notification.innerHTML = `
        <i class="bi ${type === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'}"></i>
        <span>${escapeHtml(message)}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function saveStepData(step) {
    switch(step) {
        case 1:
            CONFIG.formData.full_name = document.getElementById('full_name')?.value || '';
            CONFIG.formData.age = document.getElementById('age')?.value || '';
            CONFIG.formData.country = document.getElementById('selected_country')?.value || '';
            CONFIG.formData.weight = document.getElementById('weight')?.value || '';
            CONFIG.formData.height = document.getElementById('height')?.value || '';
            break;
        case 2:
            CONFIG.formData.symptoms = document.getElementById('symptoms')?.value || '';
            CONFIG.formData.symptoms_duration = document.getElementById('symptoms_duration')?.value || '';
            CONFIG.formData.previous_consultation = document.querySelector('input[name="previous_consultation"]:checked')?.value || '';
            break;
        case 3:
            break;
    }
    
    sessionStorage.setItem('consultation_form', JSON.stringify(CONFIG.formData));
}

function loadSavedData() {
    const saved = sessionStorage.getItem('consultation_form');
    if (saved) {
        try {
            CONFIG.formData = JSON.parse(saved);
            
            if (CONFIG.formData.full_name) document.getElementById('full_name').value = CONFIG.formData.full_name;
            if (CONFIG.formData.age) document.getElementById('age').value = CONFIG.formData.age;
            if (CONFIG.formData.symptoms) document.getElementById('symptoms').value = CONFIG.formData.symptoms;
            if (CONFIG.formData.weight) document.getElementById('weight').value = CONFIG.formData.weight;
            if (CONFIG.formData.height) document.getElementById('height').value = CONFIG.formData.height;
        } catch(e) {
            console.error('Erreur chargement données sauvegardées:', e);
        }
    }
}

function updateSummary() {
    document.getElementById('summary-name').textContent = CONFIG.formData.full_name || '-';
    document.getElementById('summary-age').textContent = CONFIG.formData.age ? CONFIG.formData.age + ' ans' : '-';
    document.getElementById('summary-country').textContent = CONFIG.formData.country || '-';
    document.getElementById('summary-size').textContent = 
        (CONFIG.formData.weight || '?') + ' kg / ' + (CONFIG.formData.height || '?') + ' cm';
    document.getElementById('summary-symptoms').textContent = CONFIG.formData.symptoms || '-';
}

function previewFiles(input, previewId) {
    const preview = document.getElementById(previewId);
    const files = Array.from(input.files);
    
    if (!preview) return;
    
    if (files.length === 0) {
        preview.style.display = 'none';
        return;
    }
    
    preview.style.display = 'block';
    preview.innerHTML = '<div style="display: flex; flex-direction: column; gap: 8px;">' +
        '<small style="font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 5px;"><i class="bi bi-check-circle"></i> ' + files.length + ' fichier(s) sélectionné(s) :</small>' +
        files.map(f => '<div style="font-size: 0.875rem; padding: 4px 0; display: flex; align-items: center; gap: 8px;"><i class="bi bi-file-earmark"></i> ' + escapeHtml(f.name) + ' (' + (f.size/1024).toFixed(1) + ' Ko)</div>').join('') +
        '</div>';
    
    input.parentElement.style.borderColor = '';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('consultationForm').addEventListener('submit', function(e) {
    const terms = document.getElementById('terms');
    
    if (!terms.checked) {
        e.preventDefault();
        showNotification('Veuillez accepter les conditions générales', 'error');
        terms.focus();
        return false;
    }
    
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethod) {
        e.preventDefault();
        showNotification('Veuillez sélectionner votre mode de paiement', 'error');
        return false;
    }
    
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Envoi en cours...';
    }
    
    sessionStorage.removeItem('consultation_form');
});

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('successModal');
    if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
        window.location.href = '<?= base_url(); ?>';
    }
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>