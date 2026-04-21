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
    --text-light: #A0AEC0;
    --error: #C53030;
    --error-bg: #FFF5F5;
    --success: #2F855A;
    --success-bg: #F0FFF4;
    --border: #E2E8F0;
    --border-focus: #4A7C6F;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.05);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
    --transition: all 0.2s ease-in-out;
}

.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: var(--primary);
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    z-index: 9999;
    font-weight: 500;
    border-radius: 0 0 4px 0;
    transition: top 0.2s;
}

.skip-link:focus { top: 0; outline: 2px solid var(--accent); }

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
}

.col-lg-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
    padding: 0 15px;
}

.col-lg-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
    padding: 0 15px;
}

.consultation-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    padding: 60px 0;
    color: white;
    text-align: center;
}

.consultation-hero h1 {
    font-size: clamp(2rem, 5vw, 2.5rem);
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.consultation-hero p {
    font-size: 1.125rem;
    max-width: 600px;
    margin: 0 auto;
    opacity: 0.95;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.consultation-progress {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    position: relative;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--border);
    z-index: 1;
}

.progress-step {
    width: 36px;
    height: 36px;
    background: var(--bg-card);
    border: 2px solid var(--border);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--text-muted);
    position: relative;
    z-index: 2;
    transition: var(--transition);
}

.progress-step.active {
    border-color: var(--primary);
    background: var(--primary);
    color: white;
}

.progress-step.completed {
    border-color: var(--success);
    background: var(--success);
    color: white;
}

.progress-bar-container {
    height: 8px;
    background: var(--bg-section);
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    transition: width 0.3s ease;
    width: 20%;
}

.consultation-card {
    background: var(--bg-card);
    border-radius: 24px;
    padding: 30px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
}

.form-header {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.header-icon {
    width: 56px;
    height: 56px;
    background: var(--accent-bg);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: var(--accent);
    flex-shrink: 0;
}

.header-icon i {
    font-size: 28px;
}

.header-text h2 {
    font-size: 1.25rem;
    margin-bottom: 4px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-text p {
    color: var(--text-muted);
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 0;
    position: relative;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.form-label i {
    color: var(--primary);
    font-size: 14px;
}

.required {
    color: var(--error);
    margin-left: 4px;
}

.form-control,
.form-select {
    width: 100%;
    padding: 12px 16px 12px 40px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 1rem;
    transition: var(--transition);
    background: var(--bg-card);
    color: var(--text-primary);
    position: relative;
}

.form-control:focus,
.form-select:focus {
    outline: none;
    border-color: var(--border-focus);
    box-shadow: 0 0 0 3px rgba(74, 124, 111, 0.1);
}

.form-control.is-invalid {
    border-color: var(--error);
    background: var(--error-bg);
}

.form-control.is-valid {
    border-color: var(--success);
    background: var(--success-bg);
}

.input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 16px;
    z-index: 2;
    pointer-events: none;
}

.form-group:focus-within .input-icon {
    color: var(--primary);
}

.invalid-feedback {
    color: var(--error);
    font-size: 0.8125rem;
    margin-top: 6px;
    display: none;
    align-items: center;
    gap: 5px;
}

.invalid-feedback i {
    font-size: 12px;
}

.form-control.is-invalid ~ .invalid-feedback {
    display: flex;
}

/* Autocomplete styles */
.autocomplete-container {
    position: relative;
}

.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: var(--shadow-md);
    margin-top: 4px;
    display: none;
}

.autocomplete-item {
    padding: 12px 16px;
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover {
    background: var(--accent-bg);
}

.autocomplete-item i {
    color: var(--primary);
    font-size: 14px;
}

/* Textarea */
textarea.form-control {
    padding-left: 16px;
    min-height: 120px;
    resize: vertical;
}

/* Input group */
.input-group {
    display: flex;
    gap: 10px;
}

.input-group .form-select {
    width: 140px;
    flex-shrink: 0;
    padding-left: 12px;
}

.input-group .form-control {
    flex: 1;
}

/* Upload section */
.upload-section {
    background: var(--bg-section);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid var(--border);
}

.upload-header {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.upload-icon {
    width: 40px;
    height: 40px;
    background: var(--bg-card);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--primary);
}

.upload-icon i {
    font-size: 20px;
}

.upload-header h4 {
    font-size: 1rem;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.upload-header p {
    color: var(--text-muted);
    font-size: 0.8125rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.upload-box {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 25px 15px;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    background: var(--bg-card);
    position: relative;
}

.upload-box:hover {
    border-color: var(--primary-muted);
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

.upload-box i {
    font-size: 32px;
    color: var(--primary);
    margin-bottom: 8px;
    display: block;
}

.upload-preview {
    margin-top: 15px;
    padding: 12px;
    background: var(--bg-card);
    border-radius: 8px;
    border: 1px solid var(--border);
    display: none;
}

/* Payment section */
.payment-section {
    background: var(--bg-section);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    border: 1px solid var(--border);
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.payment-header h3 {
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-amount {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: baseline;
    gap: 5px;
}

.payment-amount small {
    font-size: 1rem;
    color: var(--text-muted);
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.payment-method {
    padding: 15px 10px;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}

.payment-method:hover,
.payment-method.selected {
    border-color: var(--primary);
    background: var(--accent-bg);
}

.payment-method i {
    font-size: 24px;
    margin-bottom: 6px;
    display: block;
    color: var(--primary);
}

.payment-method span {
    font-weight: 600;
    font-size: 0.8125rem;
    display: block;
}

.payment-method small {
    color: var(--text-muted);
    font-size: 0.6875rem;
}

/* Consultation summary */
.consultation-summary {
    background: var(--bg-section);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
}

.summary-title {
    font-weight: 600;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-primary);
}

.summary-title i {
    color: var(--primary);
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.summary-item {
    background: var(--bg-card);
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--border);
}

.summary-item label {
    font-size: 0.6875rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.summary-item .value {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.9375rem;
}

.summary-total {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 2px dashed var(--border);
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    font-size: 1.125rem;
    color: var(--primary);
}

.summary-total i {
    margin-right: 5px;
}

/* Terms checkbox */
.terms-checkbox {
    display: flex;
    gap: 12px;
    padding: 15px;
    background: var(--accent-bg);
    border-radius: 8px;
    border: 1px solid var(--border);
    margin: 20px 0;
}

.terms-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    accent-color: var(--primary);
    flex-shrink: 0;
    cursor: pointer;
}

.terms-checkbox label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    cursor: pointer;
}

.terms-checkbox label i {
    color: var(--primary);
    margin-right: 3px;
}

.terms-checkbox a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: underline;
}

/* Form actions */
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn-prev,
.btn-next,
.btn-submit {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 120px;
}

.btn-prev {
    background: var(--bg-section);
    color: var(--text-secondary);
    border: 1.5px solid var(--border);
}

.btn-prev:hover:not(:disabled) {
    background: var(--border);
}

.btn-next,
.btn-submit {
    background: var(--primary);
    color: white;
    flex: 1;
}

.btn-next:hover:not(:disabled),
.btn-submit:hover:not(:disabled) {
    background: var(--primary-light);
    transform: translateY(-1px);
}

.btn-prev:disabled,
.btn-next:disabled,
.btn-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Info panel */
.info-panel {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 25px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-lg);
    position: sticky;
    top: 20px;
}

.info-panel h3 {
    font-size: 1.125rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-panel h3 i {
    color: var(--primary);
}

.info-panel ul {
    list-style: none;
    margin-bottom: 25px;
}

.info-panel li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
}

.info-panel li:last-child {
    border-bottom: none;
}

.info-panel li span {
    width: 24px;
    height: 24px;
    background: var(--accent-bg);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    color: var(--accent);
    font-weight: 600;
    flex-shrink: 0;
}

.info-panel li i {
    color: var(--primary);
    font-size: 14px;
}

.price-badge {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    color: white;
    margin: 25px 0;
}

.price-badge .amount {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.price-badge .amount i {
    font-size: 24px;
}

.price-badge .period {
    font-size: 0.875rem;
    opacity: 0.9;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.875rem;
}

.alert-info {
    background: var(--accent-bg);
    border: 1px solid var(--border);
    color: var(--text-primary);
}

.alert-info i {
    color: var(--accent);
    font-size: 18px;
}

/* Success modal */
.success-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.success-modal.active {
    display: flex;
}

.success-content {
    background: var(--bg-card);
    padding: 40px;
    border-radius: 24px;
    text-align: center;
    max-width: 400px;
    width: 100%;
    box-shadow: var(--shadow-lg);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-content > div:first-child {
    font-size: 48px;
    margin-bottom: 20px;
    color: var(--success);
}

.success-content > div:first-child i {
    font-size: 64px;
}

.success-content h2 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.success-content p {
    color: var(--text-secondary);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tracking-box {
    background: var(--accent-bg);
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border: 1px solid var(--border);
}

.tracking-box label {
    font-size: 0.6875rem;
    color: var(--text-muted);
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.tracking-box label i {
    color: var(--primary);
}

.tracking-box .number {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--primary);
    font-family: monospace;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tracking-box .number i {
    font-size: 16px;
}

.success-content .btn {
    padding: 12px 24px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.success-content .btn:hover {
    background: var(--primary-light);
}

.step-content.hidden {
    display: none;
}

.d-flex {
    display: flex;
}

.gap-4 {
    gap: 20px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 8px 0;
}

.form-check-input {
    width: 16px;
    height: 16px;
    accent-color: var(--primary);
    cursor: pointer;
}

.form-check label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-check label i {
    color: var(--primary);
    font-size: 14px;
}

/* Radio buttons styled */
.radio-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
}

.radio-option:hover {
    border-color: var(--primary-muted);
}

.radio-option input[type="radio"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    cursor: pointer;
}

.radio-option span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.radio-option span i {
    color: var(--primary);
    font-size: 14px;
}

/* Responsive */
@media (max-width: 991px) {
    .col-lg-8,
    .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .info-panel {
        position: static;
        margin-top: 20px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group.full-width {
        grid-column: span 1;
    }
}

@media (max-width: 768px) {
    .consultation-card {
        padding: 20px;
    }
    
    .form-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .header-icon {
        margin: 0 auto;
    }
    
    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn-prev,
    .btn-next,
    .btn-submit {
        width: 100%;
    }
    
    .progress-steps {
        display: none;
    }
    
    .input-group {
        flex-direction: column;
    }
    
    .input-group .form-select {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .consultation-hero {
        padding: 40px 0;
    }
    
    .payment-methods {
        grid-template-columns: 1fr;
    }
    
    .radio-group {
        flex-direction: column;
    }
}

*:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Doctor Info Card Styles */
.doctor-info-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    color: white;
    text-align: center;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.doctor-info-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.doctor-avatar-container {
    position: relative;
    display: inline-block;
    margin-bottom: 15px;
}

.doctor-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    position: relative;
    z-index: 2;
}

.doctor-status {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 20px;
    height: 20px;
    background: var(--success);
    border-radius: 50%;
    border: 3px solid white;
    z-index: 3;
}

.doctor-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 5px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.doctor-specialty {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.doctor-price {
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 15px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(10px);
}

.doctor-price .amount {
    font-size: 1.5rem;
    font-weight: 700;
}

.doctor-price .currency {
    font-size: 0.9rem;
    opacity: 0.9;
}

.change-doctor-btn {
    margin-top: 15px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 20px;
    color: white;
    font-size: 0.85rem;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.change-doctor-btn:hover {
    background: rgba(255,255,255,0.3);
}
</style>

<a href="#main-content" class="skip-link"><i class="bi bi-skip-forward"></i> <?= t('skip_to_content') ?></a>

<!-- SECTION HERO -->
<section class="consultation-hero">
    <div class="container">
        <h1 id="hero-title"><i class="bi bi-heart-pulse"></i> <?= t('online_consultation') ?></h1>
        <p id="hero-subtitle"><i class="bi bi-shield-check"></i> <?= t('hero_subtitle') ?></p>
    </div>
</section>

<!-- FORMULAIRE PRINCIPAL -->
<div class="container consultation-container py-5" id="main-content">
    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Barre de progression (4 étapes) -->
            <div class="consultation-progress" 
                 role="progressbar" 
                 aria-valuenow="25" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <div class="progress-steps">
                    <div class="progress-step active" id="step1-indicator"><i class="bi bi-person"></i></div>
                    <div class="progress-step" id="step2-indicator"><i class="bi bi-hospital"></i></div>
                    <div class="progress-step" id="step3-indicator"><i class="bi bi-file-earmark-medical"></i></div>
                    <div class="progress-step" id="step4-indicator"><i class="bi bi-check-lg"></i></div>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
            </div>

            <!-- Carte formulaire -->
            <div class="consultation-card">
                <form id="consultationForm" 
                      action="<?= base_url($lang . '/patient-form/create') ?>" 
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
                    <?php if(function_exists('get_csrf_token_name') && function_exists('get_csrf_hash')): ?>
                    <input type="hidden" 
                           name="<?= get_csrf_token_name() ?>" 
                           value="<?= get_csrf_hash() ?>">
                    <?php endif; ?>
                    
                    <!-- ÉTAPE 1: Informations Personnelles -->
                    <div class="step-content" id="step1">
                        <div class="form-header">
                            <div class="header-icon"><i class="bi bi-person-badge"></i></div>
                            <div class="header-text">
                                <h2><i class="bi bi-person"></i> <?= t('personal_info_title') ?></h2>
                                <p><i class="bi bi-lock"></i> <?= t('personal_info_subtitle') ?></p>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label" for="full_name">
                                    <i class="bi bi-person"></i> <?= t('full_name') ?> <span class="required">*</span>
                                </label>
                                <div style="position: relative;">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" 
                                           class="form-control" 
                                           name="full_name" 
                                           id="full_name"
                                           placeholder="<?= t('full_name_placeholder') ?>"
                                           required
                                           minlength="3"
                                           maxlength="100"
                                           value="<?= htmlspecialchars($this->session->userdata('fullname') ?: $this->session->userdata('fu') ?: '', ENT_QUOTES, 'UTF-8') ?>"
                                           <?= ($this->session->userdata('fullname') || $this->session->userdata('fu')) ? 'readonly' : '' ?>>
                                </div>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> <?= t('full_name_error') ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="age">
                                    <i class="bi bi-calendar"></i> <?= t('age') ?> <span class="required">*</span>
                                </label>
                                <div style="position: relative;">
                                    <i class="bi bi-calendar input-icon"></i>
                                    <input type="number" 
                                           class="form-control" 
                                           name="age" 
                                           id="age"
                                           placeholder="<?= t('age_placeholder') ?>"
                                           required
                                           min="1"
                                           max="120"
                                           value="<?= set_value('age'); ?>">
                                </div>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> <?= t('age_error') ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-globe"></i> <?= t('country') ?> <span class="required">*</span>
                                </label>
                                <div class="autocomplete-container" style="position: relative;">
                                    <i class="bi bi-globe input-icon"></i>
                                    <input type="text" 
                                           class="form-control"  
                                           id="country_search" 
                                           placeholder="<?= t('country_placeholder') ?>" 
                                           autocomplete="off"
                                           required>
                                    <input type="hidden" name="country" id="selected_country" value="<?= set_value('country'); ?>">
                                    <div id="autocomplete_list" class="autocomplete-results"></div>
                                    <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> <?= t('country_error') ?></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="weight">
                                    <i class="bi bi-speedometer"></i> <?= t('weight') ?> <span class="required">*</span>
                                </label>
                                <div style="position: relative;">
                                    <i class="bi bi-speedometer input-icon"></i>
                                    <input type="number" 
                                           class="form-control" 
                                           name="weight" 
                                           id="weight"
                                           placeholder="<?= t('weight_placeholder') ?>"
                                           required
                                           min="1"
                                           max="300"
                                           step="0.1"
                                           value="<?= set_value('weight'); ?>">
                                </div>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> <?= t('weight_error') ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="height">
                                    <i class="bi bi-rulers"></i> <?= t('height') ?> <span class="required">*</span>
                                </label>
                                <div style="position: relative;">
                                    <i class="bi bi-rulers input-icon"></i>
                                    <input type="number" 
                                           class="form-control" 
                                           name="height" 
                                           id="height"
                                           placeholder="<?= t('height_placeholder') ?>"
                                           required
                                           min="50"
                                           max="250"
                                           value="<?= set_value('height'); ?>">
                                </div>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> <?= t('height_error') ?></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-prev" disabled><i class="bi bi-arrow-left"></i> <?= t('previous') ?></button>
                            <button type="button" class="btn-next" onclick="nextStep(1)"><?= t('next') ?> <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- ÉTAPE 2: Symptômes -->
                    <div class="step-content hidden" id="step2">
                        <div class="form-header">
                            <div class="header-icon"><i class="bi bi-hospital"></i></div>
                            <div class="header-text">
                                <h2><i class="bi bi-activity"></i> <?= t('symptoms_title') ?></h2>
                                <p><i class="bi bi-pencil-square"></i> <?= t('symptoms_subtitle') ?></p>
                            </div>
                        </div>

                        <div class="form-group full-width" style="margin-bottom: 20px;">
                            <label class="form-label" for="symptoms">
                                <i class="bi bi-chat-text"></i> <?= t('symptoms_detailed') ?> <span class="required">*</span>
                            </label>
                            <textarea class="form-control" 
                                      name="symptoms" 
                                      id="symptoms"
                                      rows="6"
                                      required
                                      minlength="20"
                                      placeholder="<?= t('symptoms_placeholder') ?>"><?= set_value('symptoms'); ?></textarea>
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> <?= t('symptoms_error') ?></div>
                        </div>

                        <div class="form-group full-width" style="margin-bottom: 20px;">
                            <label class="form-label" for="symptoms_duration">
                                <i class="bi bi-clock-history"></i> <?= t('symptoms_duration_label') ?>
                            </label>
                            <div style="position: relative;">
                                <i class="bi bi-clock input-icon" style="top: 12px; transform: none;"></i>
                                <select class="form-select" name="symptoms_duration" id="symptoms_duration" style="padding-left: 40px;">
                                    <option value=""><?= t('select_duration') ?></option>
                                    <option value="24h" <?= set_select('symptoms_duration', '24h'); ?>><?= t('duration_24h') ?></option>
                                    <option value="2-3j" <?= set_select('symptoms_duration', '2-3j'); ?>><?= t('duration_2_3days') ?></option>
                                    <option value="1sem" <?= set_select('symptoms_duration', '1sem'); ?>><?= t('duration_1week') ?></option>
                                    <option value="2sem" <?= set_select('symptoms_duration', '2sem'); ?>><?= t('duration_2weeks') ?></option>
                                    <option value="1mois" <?= set_select('symptoms_duration', '1mois'); ?>><?= t('duration_1month') ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group full-width" style="margin-bottom: 20px;">
                            <label class="form-label">
                                <i class="bi bi-question-circle"></i> <?= t('previous_consultation_label') ?>
                            </label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="previous_consultation" value="yes" <?= set_radio('previous_consultation', 'yes'); ?>> 
                                    <span><i class="bi bi-check-lg"></i> <?= t('yes') ?></span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="previous_consultation" value="no" <?= set_radio('previous_consultation', 'no', true); ?>> 
                                    <span><i class="bi bi-x-lg"></i> <?= t('no') ?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-prev" onclick="prevStep(2)"><i class="bi bi-arrow-left"></i> <?= t('previous') ?></button>
                            <button type="button" class="btn-next" onclick="nextStep(2)"><?= t('next') ?> <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- ÉTAPE 3: Documents -->
                    <div class="step-content hidden" id="step3">
                        <div class="form-header">
                            <div class="header-icon"><i class="bi bi-file-earmark-medical"></i></div>
                            <div class="header-text">
                                <h2><i class="bi bi-folder"></i> <?= t('documents_title') ?></h2>
                                <p><i class="bi bi-shield-lock"></i> <?= t('documents_subtitle') ?></p>
                            </div>
                        </div>

                        <div class="upload-section">
                            <div class="upload-header">
                                <div class="upload-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <div>
                                    <h4><i class="bi bi-clipboard-data"></i> <?= t('medical_analysis') ?></h4>
                                    <p><i class="bi bi-info-circle"></i> <?= t('medical_analysis_desc') ?></p>
                                </div>
                            </div>

                            <div class="upload-box" tabindex="0">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span style="display: block; font-weight: 500; margin-bottom: 4px;"><?= t('click_to_upload') ?></span>
                                <small style="color: var(--text-muted); font-size: 0.6875rem;"><i class="bi bi-file-earmark"></i> <?= t('upload_format') ?></small>
                                <input type="file" name="medical_docs[]" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewFiles(this, 'medical-preview')">
                            </div>
                            <div id="medical-preview" class="upload-preview"></div>
                        </div>

                        <div class="upload-section">
                            <div class="upload-header">
                                <div class="upload-icon"><i class="bi bi-capsule"></i></div>
                                <div>
                                    <h4><i class="bi bi-prescription"></i> <?= t('previous_prescriptions') ?></h4>
                                    <p><i class="bi bi-info-circle"></i> <?= t('previous_prescriptions_desc') ?></p>
                                </div>
                            </div>

                            <div class="upload-box" tabindex="0">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span style="display: block; font-weight: 500; margin-bottom: 4px;"><?= t('click_to_upload') ?></span>
                                <small style="color: var(--text-muted); font-size: 0.6875rem;"><i class="bi bi-file-earmark"></i> <?= t('upload_format') ?></small>
                                <input type="file" name="prescriptions[]" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewFiles(this, 'prescription-preview')">
                            </div>
                            <div id="prescription-preview" class="upload-preview"></div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-prev" onclick="prevStep(3)"><i class="bi bi-arrow-left"></i> <?= t('previous') ?></button>
                            <button type="button" class="btn-next" onclick="nextStep(3)"><?= t('next') ?> <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- ÉTAPE 4: Confirmation -->
                    <div class="step-content hidden" id="step4">
                        <div class="form-header">
                            <div class="header-icon"><i class="bi bi-check-circle"></i></div>
                            <div class="header-text">
                                <h2><i class="bi bi-eye"></i> <?= t('summary_title') ?></h2>
                                <p><i class="bi bi-info-circle"></i> <?= t('summary_subtitle') ?></p>
                            </div>
                        </div>

                        <div class="consultation-summary">
                            <div class="summary-title"><i class="bi bi-person"></i> <?= t('personal_info_title') ?></div>
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <label><i class="bi bi-person"></i> <?= t('full_name') ?></label>
                                    <div class="value" id="summary-name">-</div>
                                </div>
                                <div class="summary-item">
                                    <label><i class="bi bi-calendar"></i> <?= t('age') ?></label>
                                    <div class="value" id="summary-age">-</div>
                                </div>
                                <div class="summary-item">
                                    <label><i class="bi bi-globe"></i> <?= t('country') ?></label>
                                    <div class="value" id="summary-country">-</div>
                                </div>
                                <div class="summary-item">
                                    <label><i class="bi bi-rulers"></i> <?= t('weight_height') ?></label>
                                    <div class="value" id="summary-size">-</div>
                                </div>
                            </div>

                            <div class="summary-title" style="margin-top: 20px;"><i class="bi bi-activity"></i> <?= t('symptoms_title') ?></div>
                            <div class="summary-item" style="margin-bottom: 15px;">
                                <div class="value" id="summary-symptoms" style="white-space: pre-wrap;">-</div>
                            </div>

                            <div class="summary-total">
                                <span><i class="bi bi-cash-stack"></i> <?= t('total_to_pay') ?></span>
                                <?php
                                    $taux = $this->config->item('taux_devise');
                                    $prix_usd = isset($medecin['honoraires_consultation']) ? (float)$medecin['honoraires_consultation'] : 50;
                                    $prix_eur = $prix_usd * ($taux['USD_TO_EUR'] ?? 0.92);
                                    $prix_bif = $prix_usd * ($taux['USD_TO_BIF'] ?? 2900);
                                ?>
                                <span id="summary-total">
                                    <div class="price-value">
                                        <?= number_format($prix_usd, 2) ?> <span>USD/EUR</span>
                                    </div>
                                    <div class="burundi-price" style="margin-top: 10px; padding: 10px 12px; background: #e8f5f0; border-radius: 8px; border-left: 4px solid #0f4c3a; color: #0f4c3a; font-size: 0.95rem;">
                                        <i class="bi bi-geo-alt-fill" style="color: #d4af37; margin-right: 6px;"></i>
                                        <strong><?= t('burundi_price') ?> 40 000 Fbu</strong>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <div class="terms-checkbox">
                            <input type="checkbox" name="terms" id="terms" required>
                            <label for="terms">
                                <i class="bi bi-file-text"></i> <?= t('terms_accept') ?> <a href="<?= base_url($lang . '/conditions'); ?>" target="_blank"><i class="bi bi-link-45deg"></i> <?= t('terms_link') ?></a> <?= t('terms_privacy') ?>
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-prev" onclick="prevStep(4)"><i class="bi bi-arrow-left"></i> <?= t('previous') ?></button>
                            <button type="submit" class="btn-submit" id="submitBtn">
                                <i class="bi bi-check-lg"></i> <?= t('confirm_consultation') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar info -->
        <div class="col-lg-4">
            <div class="info-panel">
                <div id="doctor-card-container">
                    <div class="doctor-info-card">
                        <div class="doctor-avatar-container">
                            <img src="<?= base_url('attachments/Users/' . htmlspecialchars($medecin['photo'] ?? '')) ?>" 
                                 alt="<?= htmlspecialchars($medecin['nom'] ?? 'Médecin') ?>" 
                                 class="doctor-avatar" 
                                 onerror="this.src='<?= base_url('assets/images/default-doctor.png') ?>'">
                            <span class="doctor-status"></span>
                        </div>
                        <h4 class="doctor-name">
                            <i class="bi bi-person-badge"></i><?= htmlspecialchars($medecin['prenom'] ?? '') . ' ' . htmlspecialchars($medecin['nom'] ?? '') ?>
                        </h4>
                        <p class="doctor-specialty">
                            <i class="bi bi-star-fill" style="color: var(--accent);"></i> <?= htmlspecialchars($medecin['specialite'] ?? t('general_practitioner')) ?>
                        </p>
                        <div class="doctor-price">
                            <div class="price-value">
                                <?= number_format($prix_usd ?? 50, 2) ?> <span>USD/EUR</span>
                            </div>
                        </div>
                        <div class="burundi-price" style="margin-top: 10px; padding: 10px 12px; background: #e8f5f0; border-radius: 8px; border-left: 4px solid #0f4c3a; color: #0f4c3a; font-size: 0.95rem;">
                            <i class="bi bi-geo-alt-fill" style="color: #d4af37; margin-right: 6px;"></i>
                            <strong><?= t('burundi_price') ?><br> 40 000 Fbu </strong>
                        </div>
                        <a style="text-decoration: none;" href="javascript:void(0)" onclick="confirmChangeDoctor()" class="change-doctor-btn">
                            <i class="bi bi-arrow-left"></i> <?= t('change_doctor') ?>
                        </a>
                    </div>
                </div>

                <h3><i class="bi bi-question-circle"></i> <?= t('how_it_works') ?></h3>
                <ul>
                    <li><span>1</span> <i class="bi bi-pencil-square"></i> <?= t('step1_fill_form') ?></li>
                    <li><span>2</span> <i class="bi bi-chat-text"></i> <?= t('step2_describe_symptoms') ?></li>
                    <li><span>3</span> <i class="bi bi-upload"></i> <?= t('step3_upload_documents') ?></li>
                    <li><span>4</span> <i class="bi bi-credit-card"></i> <?= t('step4_make_payment') ?></li>
                    <li><span>5</span> <i class="bi bi-person-check"></i> <?= t('step5_doctor_response') ?></li>
                </ul>

                <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">

                <div style="margin-bottom: 15px;">
                    <strong style="font-weight: bold; display: block; margin-bottom: 5px;">
                        <i class="bi bi-wallet2" style="margin-right: 5px;"></i> <?= t('accepted_payment_methods') ?> :
                    </strong>
                    <?php foreach($mode_payements as $mode): ?>
                    <div style="display: flex; gap: 15px; margin-top: 10px; font-size: 24px; align-items: center;">
                        <i class="bi <?php 
                            switch($mode['description']) {
                                case 'Carte bancaire': echo 'bi-credit-card'; break;
                                case 'PayPal': echo 'bi-paypal'; break;
                                case 'Mobile Money': echo 'bi-phone'; break;
                                case 'Virement': echo 'bi-bank'; break;
                                default: echo 'bi-cash';
                            }
                        ?>" style="font-size: 24px;"></i>
                        <span style="font-size: 16px; font-weight: 500;"><?= htmlspecialchars($mode['description']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-shield-check"></i>
                    <small><?= t('payment_secure') ?></small>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <small style="color: var(--text-muted);"><i class="bi bi-headset"></i> <?= t('need_help') ?></small><br>
                    <a href="tel:+25779666439" style="color: var(--primary); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; margin-top: 5px;">
                        <i class="bi bi-telephone"></i> <?= $this->Model->get_setting('contact_whatsapp', '+257 79 666 439') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal succès -->
<div class="success-modal" id="successModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="success-content">
        <div><i class="bi bi-check-circle-fill"></i></div>
        <h2 id="modalTitle"><i class="bi bi-emoji-smile"></i> <?= t('thank_you') ?></h2>
        <p><i class="bi bi-envelope-check"></i> <?= t('success_message') ?></p>
        
        <div class="tracking-box">
            <label><i class="bi bi-upc-scan"></i> <?= t('tracking_number') ?></label>
            <div class="number" id="trackingNumber"><i class="bi bi-hash"></i> -</div>
        </div>
        
        <button class="btn" onclick="window.location.href='<?= base_url($lang); ?>'">
            <i class="bi bi-house"></i> <?= t('back_to_home') ?>
        </button>
    </div>
</div>

<?php 
if (!isset($products)) {
    $products = $this->Model->get_products_translated($this->current_lang);
}
?>
<?php include VIEWPATH.'sections/Products_Section.php'; ?>


<script>
// Mettre à jour le nombre total d'étapes
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
    if (confirm('<?= t('confirm_change_doctor') ?>')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url($lang . '/Swap-medecin') ?>';
        
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
const doctorData = {
    nom: '<?= htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? ''), ENT_QUOTES) ?>',
    specialite: '<?= htmlspecialchars($medecin['specialite'] ?? t('general_practitioner'), ENT_QUOTES) ?>',
    prix: '<?= htmlspecialchars($medecin['honoraires_consultation'] ?? 50, ENT_QUOTES) ?>',
    devise: '<?= htmlspecialchars($medecin['currency'] ?? 'USD', ENT_QUOTES) ?>',
    photo: '<?= base_url('attachments/Users/' . htmlspecialchars($medecin['photo'] ?? '', ENT_QUOTES)) ?>'
};

document.addEventListener('DOMContentLoaded', function() {
    initializeAutocomplete();
    initializeFormValidation();
    
    const heroTitle = document.querySelector('.consultation-hero h1');
    if (heroTitle) {
        heroTitle.innerHTML = `<i class="bi bi-heart-pulse"></i> <?= t('consultation_with') ?> ${doctorData.nom}`;
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
        showNotification('<?= t('validation_error') ?>', 'error');
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
            if (!field.files || field.files.length === 0) {
                field.parentElement.style.borderColor = 'var(--error)';
                isValid = false;
            } else {
                field.parentElement.style.borderColor = '';
            }
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
            // Documents optionnels, pas de validation stricte
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
    document.getElementById('summary-age').textContent = CONFIG.formData.age ? CONFIG.formData.age + ' <?= t('years_short') ?>' : '-';
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
        '<small style="font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 5px;"><i class="bi bi-check-circle"></i> ' + files.length + ' <?= t('file_selected') ?> :</small>' +
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
        showNotification('<?= t('accept_terms_error') ?>', 'error');
        terms.focus();
        return false;
    }
    
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> <?= t('sending') ?>';
    }
    
    sessionStorage.removeItem('consultation_form');
});

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('successModal');
    if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
        window.location.href = '<?= base_url($lang); ?>';
    }
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>