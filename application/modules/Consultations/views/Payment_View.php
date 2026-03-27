<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-muted: #6B9080;
    --accent: #C9A227;
    --accent-light: #D4B85A;
    --accent-bg: #FDF8E8;
    --bg-main: #F7F9F8;
    --bg-card: #FFFFFF;
    --bg-section: #F0F4F2;
    --text-primary: #1A202C;
    --text-secondary: #4A5568;
    --text-muted: #718096;
    --error: #C53030;
    --success: #2F855A;
    --border: #E2E8F0;
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
    --transition: all 0.2s ease-in-out;
}

/* Flash Message Styles */
.flash-message {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.3s ease;
    font-weight: 500;
}

.flash-message i {
    font-size: 20px;
}

.flash-message.warning {
    background: #FFF3E0;
    border-left: 4px solid #FF9800;
    color: #E65100;
}

.flash-message.success {
    background: #E8F5E9;
    border-left: 4px solid #4CAF50;
    color: #2E7D32;
}

.flash-message.error {
    background: #FFEBEE;
    border-left: 4px solid #F44336;
    color: #C62828;
}

.flash-message.info {
    background: #E3F2FD;
    border-left: 4px solid #2196F3;
    color: #0B5E7E;
}

.flash-message .close-btn {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
    transition: opacity 0.2s;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
}

.flash-message .close-btn:hover {
    opacity: 1;
    background: rgba(0,0,0,0.05);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Payment Hero */
.payment-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    padding: 40px 0;
    color: white;
    text-align: center;
}

.payment-hero h1 {
    font-size: clamp(1.8rem, 4vw, 2.2rem);
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.payment-hero p {
    font-size: 1rem;
    opacity: 0.95;
}

.payment-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Layout à 2 colonnes pour grands écrans */
.payment-layout {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.payment-main {
    flex: 2;
    min-width: 0;
}

.payment-sidebar {
    flex: 1;
    min-width: 280px;
}

@media (max-width: 992px) {
    .payment-layout {
        flex-direction: column;
    }
    .payment-sidebar {
        order: -1;
    }
}

.payment-card {
    background: var(--bg-card);
    border-radius: 24px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: 1px solid var(--border);
}

.payment-header {
    background: var(--bg-section);
    padding: 25px 30px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.payment-header h2 {
    font-size: 1.3rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-primary);
}

.tracking-badge {
    background: var(--primary);
    color: white;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.payment-body {
    padding: 30px;
}

/* Sidebar Process Card */
.process-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 24px;
    padding: 25px;
    color: white;
    position: sticky;
    top: 20px;
    box-shadow: var(--shadow-lg);
}

.process-card h3 {
    font-size: 1.2rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-align: center;
    justify-content: center;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.process-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 25px;
}

.process-step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    background: rgba(255,255,255,0.1);
    padding: 15px;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.process-step:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

.step-number {
    width: 36px;
    height: 36px;
    background: var(--accent);
    color: var(--primary-dark);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-title {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 5px;
    display: block;
}

.step-desc {
    font-size: 0.8rem;
    opacity: 0.85;
    line-height: 1.4;
}

.whatsapp-contact {
    background: rgba(37, 211, 102, 0.2);
    border: 1px solid rgba(37, 211, 102, 0.5);
    border-radius: 16px;
    padding: 15px;
    margin: 20px 0;
    text-align: center;
}

.whatsapp-contact i {
    font-size: 28px;
    color: #25D366;
    display: block;
    margin-bottom: 8px;
}

.whatsapp-contact .number {
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: 1px;
    display: block;
}

.whatsapp-contact small {
    font-size: 0.7rem;
    opacity: 0.8;
}

.payment-note {
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.75rem;
    text-align: center;
    justify-content: center;
}

.payment-note i {
    font-size: 16px;
    color: var(--accent);
}

/* Info Consultation */
.info-consultation {
    background: var(--accent-bg);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 30px;
}

.info-consultation h3 {
    font-size: 1rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--bg-card);
    border-radius: 12px;
}

.info-icon {
    width: 40px;
    height: 40px;
    background: var(--bg-section);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 18px;
}

.info-content {
    flex: 1;
}

.info-content label {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    display: block;
}

.info-content .value {
    font-weight: 600;
    color: var(--text-primary);
}

/* Prix */
.price-section {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    color: white;
    text-align: center;
}

.price-amount {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.price-equivalents {
    font-size: 0.85rem;
    opacity: 0.85;
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

/* Symptômes */
.symptoms-section {
    background: var(--bg-section);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 30px;
}

.symptoms-section h3 {
    font-size: 1rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.symptoms-text {
    background: var(--bg-card);
    padding: 15px;
    border-radius: 12px;
    line-height: 1.6;
}

/* Documents */
.documents-section {
    background: var(--bg-section);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 30px;
}

.documents-section h3 {
    font-size: 1rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.doc-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.doc-badge {
    background: var(--bg-card);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--border);
}

/* Méthodes de paiement */
.payment-methods-section {
    margin-bottom: 30px;
}

.payment-methods-section h3 {
    font-size: 1rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.payment-methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
}

.payment-method-card {
    background: var(--bg-card);
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 15px;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
}

.payment-method-card:hover {
    border-color: var(--primary-muted);
    transform: translateY(-2px);
}

.payment-method-card.selected {
    border-color: var(--primary);
    background: var(--accent-bg);
}

.payment-method-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.payment-method-header i {
    font-size: 28px;
    color: var(--primary);
}

.payment-method-header span {
    font-weight: 700;
    font-size: 1.1rem;
}

.payment-details {
    padding-left: 40px;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.payment-details p {
    margin: 5px 0;
}

.payment-details i {
    margin-right: 8px;
    width: 20px;
}

.check-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    color: var(--success);
    font-size: 20px;
    display: none;
}

.payment-method-card.selected .check-icon {
    display: block;
}

/* Actions */
.payment-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn-cancel {
    padding: 12px 24px;
    background: var(--bg-section);
    color: var(--text-secondary);
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.btn-cancel:hover {
    background: var(--border);
}

.btn-pay {
    flex: 1;
    padding: 12px 24px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.btn-pay:hover:not(:disabled) {
    background: var(--primary-light);
    transform: translateY(-1px);
}

.btn-pay:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #6c757d;
}

@media (max-width: 768px) {
    .payment-body {
        padding: 20px;
    }
    .payment-actions {
        flex-direction: column-reverse;
    }
    .btn-cancel, .btn-pay {
        width: 100%;
        justify-content: center;
    }
    .info-grid {
        grid-template-columns: 1fr;
    }
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(100%); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideOutRight {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(100%); }
}
</style>

<!-- SECTION HERO -->
<section class="payment-hero">
    <div class="container">
        <h1><i class="bi bi-credit-card"></i> Paiement sécurisé</h1>
        <p>Finalisez votre paiement pour confirmer votre consultation</p>
    </div>
</section>

<div class="payment-container">
    <div class="payment-layout">
        
        <!-- COLONNE PRINCIPALE - FORMULAIRE DE PAIEMENT -->
        <div class="payment-main">
            <div class="payment-card">
                <div class="payment-header">
                    <h2><i class="bi bi-receipt"></i> Détails du paiement</h2>
                    <div class="tracking-badge">
                        <i class="bi bi-upc-scan"></i>
                        N°: <?= htmlspecialchars($consultation['numero_consultation'] ?? 'N/A') ?>
                    </div>
                </div>

                <div class="payment-body">
                    
                    <!-- AFFICHAGE DES MESSAGES FLASH -->
                    <?php if($this->session->flashdata('warning')): ?>
                    <div class="flash-message warning" id="flashWarning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= $this->session->flashdata('warning') ?></span>
                        <button class="close-btn" onclick="this.parentElement.remove()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                   
                 

                    <!-- Informations de la consultation -->
                    <div class="info-consultation">
                        <h3><i class="bi bi-calendar-check"></i> Récapitulatif de la consultation</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-person"></i></div>
                                <div class="info-content">
                                    <label>Patient</label>
                                    <div class="value"><?= htmlspecialchars($patient_name ?? 'Non défini') ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-hospital"></i></div>
                                <div class="info-content">
                                    <label>Médecin</label>
                                    <div class="value"><?= htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? '')) ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-calendar"></i></div>
                                <div class="info-content">
                                    <label>Âge</label>
                                    <div class="value"><?= htmlspecialchars($patient_age ?? 'Non défini') ?> ans</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-globe"></i></div>
                                <div class="info-content">
                                    <label>Pays</label>
                                    <div class="value"><?= htmlspecialchars($patient_pays ?? 'Non défini') ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-rulers"></i></div>
                                <div class="info-content">
                                    <label>Poids / Taille</label>
                                    <div class="value"><?= htmlspecialchars($patient_poids ?? '?') ?> kg / <?= htmlspecialchars($patient_taille ?? '?') ?> cm</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-envelope"></i></div>
                                <div class="info-content">
                                    <label>Email</label>
                                    <div class="value"><?= htmlspecialchars($patient_email ?? 'Non défini') ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-clock-history"></i></div>
                                <div class="info-content">
                                    <label>Date de création</label>
                                    <div class="value"><?= date('d/m/Y H:i', strtotime($consultation['created_at'] ?? date('Y-m-d H:i:s'))) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prix -->
                    <div class="price-section">
                        <div class="price-amount">
                            <?= number_format($prix_usd, 2) ?> <?= htmlspecialchars($consultation['devise'] ?? 'USD') ?>
                        </div>
                        <div class="price-equivalents">
                            <span><i class="bi bi-currency-euro"></i> ≈ <?= number_format($prix_eur, 2) ?> EUR</span>
                            <span><i class="bi bi-cash-stack"></i> ≈ <?= number_format($prix_bif, 0) ?> BIF</span>
                        </div>
                    </div>

                    <!-- Symptômes -->
                    <div class="symptoms-section">
                        <h3><i class="bi bi-activity"></i> Symptômes décrits</h3>
                        <div class="symptoms-text">
                            <?= nl2br(htmlspecialchars($consultation['symptomes'] ?? 'Non renseignés')) ?>
                        </div>
                        <?php if(!empty($consultation['duree_symptomes'])): ?>
                        <div style="margin-top: 10px; font-size: 0.85rem; color: var(--text-muted);">
                            <i class="bi bi-clock"></i> Durée: <?= htmlspecialchars($consultation['duree_symptomes']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($consultation['consultation_precedente'])): ?>
                        <div style="margin-top: 5px; font-size: 0.85rem; color: var(--text-muted);">
                            <i class="bi bi-question-circle"></i> Consultation précédente: <?= $consultation['consultation_precedente'] == 'yes' ? 'Oui' : 'Non' ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Documents -->
                    <?php if(!empty($examens_demandes) || !empty($ordonnances)): ?>
                    <div class="documents-section">
                        <h3><i class="bi bi-folder"></i> Documents joints</h3>
                        <?php if(!empty($examens_demandes)): ?>
                        <div style="margin-bottom: 15px;">
                            <strong><i class="bi bi-file-earmark-text"></i> Analyses médicales:</strong>
                            <div class="doc-list" style="margin-top: 8px;">
                                <?php foreach($examens_demandes as $doc): ?>
                                <span class="doc-badge"><i class="bi bi-file-earmark"></i> <?= basename($doc) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($ordonnances)): ?>
                        <div>
                            <strong><i class="bi bi-prescription"></i> Ordonnances:</strong>
                            <div class="doc-list" style="margin-top: 8px;">
                                <?php foreach($ordonnances as $doc): ?>
                                <span class="doc-badge"><i class="bi bi-file-earmark"></i> <?= basename($doc) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Méthodes de paiement -->
                    <div class="payment-methods-section">
                        <h3><i class="bi bi-wallet2"></i> Choisissez votre méthode de paiement</h3>
                        <div class="payment-methods-grid" id="paymentMethodsGrid">
                            <?php foreach($mode_payements as $mode): ?>
                            <div class="payment-method-card" onclick="selectPaymentMethod('<?= htmlspecialchars($mode['description']) ?>', this, '<?= htmlspecialchars($mode['numero_compte'] ?? '') ?>', '<?= htmlspecialchars($mode['nom_compte'] ?? '') ?>')">
                                <div class="payment-method-header">
                                    <i class="bi <?php 
                                        switch(strtolower($mode['description'])) {
                                            case 'carte bancaire': echo 'bi-credit-card'; break;
                                            case 'paypal': echo 'bi-paypal'; break;
                                            case 'virement bancaire': echo 'bi-bank'; break;
                                            case 'western': echo 'bi-cash-stack'; break;
                                            case 'lumicash': echo 'bi-phone'; break;
                                            case 'ecocash': echo 'bi-phone-fill'; break;
                                            default: echo 'bi-cash';
                                        }
                                    ?>"></i>
                                    <span><?= htmlspecialchars($mode['description']) ?></span>
                                </div>
                                <?php if(!empty($mode['numero_compte']) || !empty($mode['nom_compte'])): ?>
                                <div class="payment-details">
                                    <?php if(!empty($mode['numero_compte'])): ?>
                                    <p><i class="bi bi-hash"></i> Compte: <?= htmlspecialchars($mode['numero_compte']) ?></p>
                                    <?php endif; ?>
                                    <?php if(!empty($mode['nom_compte'])): ?>
                                    <p><i class="bi bi-person-badge"></i> Titulaire: <?= htmlspecialchars($mode['nom_compte']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <div class="check-icon"><i class="bi bi-check-circle-fill"></i></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="payment-actions">
                        <button type="button" class="btn-cancel" onclick="window.location.href='<?= base_url('PatientForm') ?>'">
                            <i class="bi bi-arrow-left"></i> Retour
                        </button>
                        <button type="button" class="btn-pay" id="payButton" onclick="processPayment()" disabled>
                            <i class="bi bi-whatsapp"></i> Payer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLONNE LATÉRALE - PROCESSUS DE PAIEMENT (AFFICHÉ À DROITE SUR GRANDS ÉCRANS) -->
        <div class="payment-sidebar">
            <div class="process-card">
                <h3><i class="bi bi-question-circle-fill"></i> Comment payer ?</h3>
                
                <div class="process-steps">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <span class="step-title">Choisissez votre mode de paiement</span>
                            <span class="step-desc">Western, Lumicash, EcoCash, ou autre</span>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <span class="step-title">Cliquez sur "Payer"</span>
                            <span class="step-desc">Un message automatique sera préparé</span>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <span class="step-title">WhatsApp s'ouvre automatiquement</span>
                            <span class="step-desc">Le message contient tous vos détails</span>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <span class="step-title">Joignez votre preuve de paiement</span>
                            <span class="step-desc">Photo du reçu, capture d'écran du virement</span>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <span class="step-title">Envoyez et attendez la confirmation</span>
                            <span class="step-desc">Votre consultation sera confirmée sous 24h</span>
                        </div>
                    </div>
                </div>
                
                <div class="whatsapp-contact">
                    <i class="bi bi-whatsapp"></i>
                    <span class="number">+257 79 666 439</span>
                    <small>Numéro pour l'envoi de la preuve</small>
                </div>
                
                <div class="payment-note">
                    <i class="bi bi-info-circle"></i>
                    <span>Après envoi de la preuve, votre consultation sera confirmée sous 24h</span>
                </div>
            </div>
        </div>
    </div>
</div>


<?php 
        // Vérifier si la variable $products existe et contient des données
        if (!isset($products)) {
            // Si les produits ne sont pas encore chargés, on les récupère
            $products = $this->Model->read('advertise_product', null, 'id', 'DESC');
        }
        ?>
<?php include VIEWPATH.'sections/Products_Section.php'; ?>



<script>
let selectedPaymentMethod = null;
let selectedMethodElement = null;
let selectedPaymentDetails = {};

// Sélectionner une méthode de paiement
function selectPaymentMethod(method, element, compte, titulaire) {
    if (selectedMethodElement) {
        selectedMethodElement.classList.remove('selected');
    }
    element.classList.add('selected');
    selectedPaymentMethod = method;
    selectedMethodElement = element;
    selectedPaymentDetails = { compte: compte, titulaire: titulaire };
    checkFormValidity();
}

function checkFormValidity() {
    const payButton = document.getElementById('payButton');
    if (payButton) {
        payButton.disabled = (selectedPaymentMethod === null);
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    
    let bgColor, borderColor, icon;
    if (type === 'error') {
        bgColor = '#FFEBEE';
        borderColor = '#F44336';
        icon = 'bi-exclamation-triangle-fill';
    } else if (type === 'warning') {
        bgColor = '#FFF3E0';
        borderColor = '#FF9800';
        icon = 'bi-exclamation-triangle-fill';
    } else if (type === 'success') {
        bgColor = '#E8F5E9';
        borderColor = '#4CAF50';
        icon = 'bi-check-circle-fill';
    } else {
        bgColor = '#E3F2FD';
        borderColor = '#2196F3';
        icon = 'bi-info-circle-fill';
    }
    
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        padding: 15px 20px;
        background: ${bgColor};
        color: ${type === 'warning' ? '#E65100' : (type === 'error' ? '#C62828' : (type === 'success' ? '#2E7D32' : '#0B5E7E'))};
        border-left: 4px solid ${borderColor};
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 100000;
        animation: slideInRight 0.3s ease;
        font-size: 14px;
        max-width: 400px;
        font-weight: 500;
    `;
    
    toast.innerHTML = `
        <i class="bi ${icon}" style="font-size: 20px;"></i>
        <span>${escapeHtml(message)}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; margin-left: auto; color: inherit;">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast && toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

function processPayment() {
    if (!selectedPaymentMethod) {
        showToast('Veuillez sélectionner une méthode de paiement avant de continuer', 'warning');
        return;
    }
    
    const payButton = document.getElementById('payButton');
    payButton.disabled = true;
    payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Préparation...';
    
    const consultationData = {
        numero: '<?= htmlspecialchars($consultation['numero_consultation'] ?? 'N/A', ENT_QUOTES) ?>',
        medecin: '<?= htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? ''), ENT_QUOTES) ?>',
        prix_usd: '<?= number_format($prix_usd, 2) ?>',
        prix_bif: '<?= number_format($prix_bif, 0) ?>',
        patient: '<?= htmlspecialchars($patient_name ?? $this->session->userdata('fullname') ?? '', ENT_QUOTES) ?>',
        email: '<?= htmlspecialchars($patient_email ?? $this->session->userdata('email') ?? '', ENT_QUOTES) ?>',
        date: '<?= date('d/m/Y H:i') ?>'
    };
    
    let message = `*NOUVELLE DEMANDE DE CONSULTATION*\n\n`;
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `*INFORMATIONS PATIENT*\n`;
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `• Nom : ${consultationData.patient}\n`;
    message += `• Email : ${consultationData.email}\n`;
    message += `• Date : ${consultationData.date}\n\n`;
    
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `*DÉTAILS CONSULTATION*\n`;
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `• Numéro : ${consultationData.numero}\n`;
    message += `• Médecin : ${consultationData.medecin}\n`;
    message += `• Montant : ${consultationData.prix_usd} USD\n`;
    message += `• Équivalent : ≈ ${consultationData.prix_bif} BIF\n\n`;
    
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `*MODE DE PAIEMENT CHOISI*\n`;
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `• ${selectedPaymentMethod}\n`;
    
    if (selectedPaymentDetails && selectedPaymentDetails.compte) {
        message += `• Compte à créditer : ${selectedPaymentDetails.compte}\n`;
    }
    if (selectedPaymentDetails && selectedPaymentDetails.titulaire) {
        message += `• Titulaire : ${selectedPaymentDetails.titulaire}\n`;
    }
    
    message += `\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `*DEMANDE DE RENDEZ-VOUS*\n`;
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `Bonjour,\n\n`;
    message += `Je souhaite confirmer ma consultation en ligne avec ${consultationData.medecin}.\n`;
    message += `Je choisis de payer via ${selectedPaymentMethod}.\n\n`;
    message += `📎 *PREUVE DE PAIEMENT* : Je joins ma preuve de paiement.\n\n`;
    message += `Une fois ma demande reçue, merci de me confirmer :\n`;
    message += `• La date et l'heure de la consultation\n`;
    message += `• Le lien pour la consultation en ligne\n\n`;
    
    message += `*CONTACT*\n`;
    message += `Téléphone : ${consultationData.patient} | Email : ${consultationData.email}\n\n`;
    
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `Message envoyé depuis la plateforme NUFOTEC\n`;
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`;
    
    const encodedMessage = encodeURIComponent(message);
    const whatsappNumber = '25779666439';
    const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
    
    window.open(whatsappUrl, '_blank');
    showToast('✅ WhatsApp ouvert ! Envoyez votre preuve de paiement (photo ou capture d\'écran) sur le numéro +257 79 666 439', 'success');
    
    const formData = new FormData();
    formData.append('consultation_id', '<?= $consultation_id ?? $consultation['id'] ?? '' ?>');
    formData.append('payment_method', selectedPaymentMethod);
    formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    
    fetch('<?= base_url('Payment/process') ?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.redirect) {
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 5000);
        }
    })
    .catch(error => console.error('Erreur:', error));
    
    setTimeout(() => {
        payButton.disabled = false;
        payButton.innerHTML = '<i class="bi bi-whatsapp"></i> Payer';
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            if (message && message.parentElement) {
                message.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => message.remove(), 300);
            }
        }, 5000);
    });
    checkFormValidity();
});

const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-20px); }
    }
`;
document.head.appendChild(style);
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>