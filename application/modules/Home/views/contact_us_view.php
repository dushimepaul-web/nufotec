<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    :root {
        --primary: #0B4F2E;
        --primary-light: #1B7B4B;
        --accent: #27ae60;
        --warning: #FF6B35;
        --error: #E74C3C;
        --bg-warm: #faf9f7;
        --text: #1a1a2e;
        --text-muted: #64748b;
        --border: #e2e8f0;
        --shadow: 0 10px 25px -5px rgba(11, 79, 46, 0.1);
        --shadow-lg: 0 20px 40px -10px rgba(0, 0, 0, 0.2);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --radius: 16px;
        --radius-lg: 24px;
    }

    * {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }

    /* ===== HERO SECTION ===== */
    .hero-section {
        position: relative;
        height: 300px;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .hero-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .hero-bg-image img {
        object-fit: cover;
        object-position: center;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 76, 58, 0.85);
        z-index: 2;
    }

    .hero-content-wrapper {
        position: relative;
        z-index: 3;
        width: 100%;
        padding: 20px 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1.2;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.3rem;
        font-weight: 500;
        color: var(--accent);
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .hero-text {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 20px;
        opacity: 0.95;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    @media (max-width: 991px) {
        .hero-section { height: 280px; }
        .hero-title { font-size: 1.8rem; }
        .hero-subtitle { font-size: 1.1rem; }
    }

    @media (max-width: 768px) {
        .hero-section { height: 250px; min-height: 220px; }
        .hero-title { font-size: 1.5rem; }
        .hero-subtitle { font-size: 1rem; }
        .hero-text { font-size: 0.9rem; }
    }

    @media (max-width: 576px) {
        .hero-section { height: 220px; min-height: 200px; }
        .hero-title { font-size: 1.3rem; }
        .hero-subtitle { font-size: 0.95rem; }
    }


    /* Navigation Glassmorphism */
    .navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--primary) !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-brand i {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* Layout Principal */
    .contact-hero {
        padding: 2rem 0;
        position: relative;
        min-height: calc(100vh - 70px);
    }

    /* Carte de Contact - Design Moderne */
    .contact-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        min-height: auto;
    }

    /* Section Formulaire */
    .form-section {
        padding: 2rem;
        background: white;
        position: relative;
        z-index: 2;
        overflow-y: auto;
        max-height: 90vh;
    }

    .form-header {
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .form-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .form-header p {
        color: var(--text-muted);
        font-size: 0.95rem;
        line-height: 1.5;
    }

    /* Formulaire Compact */
    .form-floating {
        position: relative;
        margin-bottom: 1rem;
    }

    .form-floating > .form-control {
        height: calc(3rem + 2px);
        padding: 0.75rem 1rem 0.25rem 2.75rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 0.95rem;
        transition: var(--transition);
        background: #fafbfc;
        width: 100%;
    }

    .form-floating > .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(11, 79, 46, 0.08);
        background: white;
        outline: none;
    }

    .form-floating > label {
        position: absolute;
        left: 2.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.9rem;
        font-weight: 500;
        transition: var(--transition);
        pointer-events: none;
        background: transparent;
        padding: 0 0.25rem;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        top: 0;
        transform: translateY(-50%) scale(0.85);
        color: var(--primary);
        font-weight: 600;
        background: white;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        z-index: 10;
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-floating:focus-within .input-icon {
        color: var(--primary);
    }

    textarea.form-control {
        min-height: 100px !important;
        padding-top: 1.25rem !important;
        resize: none;
    }

    textarea ~ .input-icon {
        top: 1.25rem;
        transform: none;
    }

    textarea ~ label {
        top: 1.25rem;
        transform: none;
    }

    textarea.form-control:focus ~ label,
    textarea.form-control:not(:placeholder-shown) ~ label {
        top: 0;
        transform: translateY(-50%) scale(0.85);
    }

    .char-count {
        position: absolute;
        right: 0.75rem;
        bottom: -1.25rem;
        font-size: 0.7rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .char-count.warning { color: var(--warning); }
    .char-count.error { color: var(--error); }

    /* Checkbox RGPD */
    .consent-wrapper {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #bbf7d0;
        border-radius: 12px;
        padding: 1rem;
        margin: 1.5rem 0;
        transition: var(--transition);
    }

    .consent-wrapper:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -10px rgba(11, 79, 46, 0.15);
    }

    .form-check {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 4px;
        border: 2px solid var(--primary);
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .form-check-label {
        font-size: 0.85rem;
        color: var(--text);
        cursor: pointer;
        line-height: 1.4;
    }

    .form-check-label a {
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
    }

    /* Bouton Submit */
    .btn-submit {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -10px rgba(11, 79, 46, 0.3);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-submit.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-submit .spinner {
        display: none;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .btn-submit.loading .spinner { display: block; }
    .btn-submit.loading .btn-text { display: none; }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Section Info & Carte */
    .info-section {
        background: linear-gradient(135deg, var(--primary) 0%, #064e3b 100%);
        padding: 2rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .info-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 60%);
        animation: pulse 20s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .map-container {
        position: relative;
        height: 250px;
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4);
        margin-bottom: 1.5rem;
        z-index: 1;
    }

    #map {
        width: 100%;
        height: 100%;
        filter: grayscale(20%) contrast(1.1);
    }

    .map-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1.5rem;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: white;
    }

    .map-overlay h3 {
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }

    .map-overlay p {
        font-size: 0.85rem;
        margin: 0;
        opacity: 0.9;
    }

    .map-controls {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .map-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: white;
        border: none;
        color: var(--primary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-size: 0.9rem;
    }

    .map-btn:hover {
        transform: scale(1.1);
        background: var(--primary);
        color: white;
    }

    /* Cartes d'information */
    .info-cards {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        z-index: 1;
    }

    .info-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.875rem;
        transition: var(--transition);
        cursor: pointer;
    }

    .info-card:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(5px);
    }

    .info-icon {
        width: 42px;
        height: 42px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .info-content h4 {
        color: rgba(255,255,255,0.8);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.125rem;
    }

    .info-content p {
        color: white;
        margin: 0;
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .info-content small {
        color: rgba(255,255,255,0.7);
        font-size: 0.75rem;
    }

    /* Horaires */
    .hours-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        background: rgba(39, 174, 96, 0.25);
        color: #86efac;
        padding: 0.375rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.375rem;
        border: 1px solid rgba(39, 174, 96, 0.4);
    }

    .hours-badge.closed {
        background: rgba(231, 76, 60, 0.25);
        color: #fca5a5;
        border-color: rgba(231, 76, 60, 0.4);
    }

    /* Notifications Toast */
    .toast-container {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-width: calc(100vw - 2rem);
    }

    .toast-custom {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 300px;
        max-width: 100%;
        transform: translateX(150%);
        animation: slideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        border-left: 4px solid;
    }

    .toast-custom.success { border-left-color: var(--accent); }
    .toast-custom.error { border-left-color: var(--error); }
    .toast-custom.warning { border-left-color: var(--warning); }

    @keyframes slideIn {
        to { transform: translateX(0); }
    }

    @keyframes slideOut {
        to { transform: translateX(150%); opacity: 0; }
    }

    .toast-custom.hiding {
        animation: slideOut 0.4s ease forwards;
    }

    .toast-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .toast-custom.success .toast-icon { background: #dcfce7; color: var(--accent); }
    .toast-custom.error .toast-icon { background: #fee2e2; color: var(--error); }
    .toast-custom.warning .toast-icon { background: #ffedd5; color: var(--warning); }

    .toast-content { flex: 1; min-width: 0; }
    .toast-content h4 {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.125rem;
        color: var(--text);
    }

    .toast-content p {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .toast-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.375rem;
        border-radius: 6px;
        transition: var(--transition);
        flex-shrink: 0;
    }

    .toast-close:hover {
        background: #f1f5f9;
        color: var(--text);
    }

    /* Modal Success */
    .modal-success .modal-content {
        border: none;
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .modal-success .modal-header {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        border: none;
        padding: 1.5rem;
    }

    .modal-success .modal-body {
        padding: 2rem 1.5rem;
        text-align: center;
    }

    .success-animation {
        width: 80px;
        height: 80px;
        background: #dcfce7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        position: relative;
    }

    .success-animation::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #dcfce7;
        animation: ripple 1.5s ease-out infinite;
    }

    @keyframes ripple {
        to { transform: scale(1.5); opacity: 0; }
    }

    .success-animation i {
        color: var(--accent);
        font-size: 2rem;
        position: relative;
        z-index: 1;
    }

    /* États de validation */
    .form-control.is-valid {
        border-color: var(--accent) !important;
        padding-right: 2.5rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2327ae60' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
    }

    .form-control.is-invalid {
        border-color: var(--error) !important;
        padding-right: 2.5rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
    }

    .invalid-feedback {
        display: none;
        color: var(--error);
        font-size: 0.8rem;
        margin-top: 0.375rem;
        padding-left: 2.75rem;
        font-weight: 500;
    }

    .form-control.is-invalid ~ .invalid-feedback {
        display: block;
    }

    /* ================= RESPONSIVE ================= */
    
    /* Tablet */
    @media (max-width: 992px) {
        .contact-wrapper {
            grid-template-columns: 1fr;
            margin: 0;
            border-radius: 0;
            box-shadow: none;
            min-height: auto;
        }
        
        .form-section {
            max-height: none;
            padding: 1.5rem;
        }
        
        .info-section {
            order: -1;
            padding: 1.5rem;
        }
        
        .map-container {
            height: 200px;
        }
        
        .form-header h1 {
            font-size: 1.5rem;
        }
    }

    /* Mobile */
    @media (max-width: 576px) {
        .contact-hero {
            padding: 0;
        }
        
        .form-section, .info-section {
            padding: 1.25rem;
        }
        
        .form-header {
            margin-bottom: 1.25rem;
        }
        
        .form-header h1 {
            font-size: 1.35rem;
        }
        
        .form-header p {
            font-size: 0.9rem;
        }
        
        .form-floating > .form-control {
            height: calc(2.75rem + 2px);
            padding: 0.625rem 1rem 0.25rem 2.5rem;
            font-size: 16px; /* Empêche le zoom iOS */
        }
        
        .form-floating > label {
            left: 2.5rem;
            font-size: 0.9rem;
        }
        
        .input-icon {
            left: 0.875rem;
            font-size: 0.9rem;
        }
        
        textarea.form-control {
            min-height: 80px !important;
        }
        
        .consent-wrapper {
            padding: 0.875rem;
            margin: 1.25rem 0;
        }
        
        .form-check-label {
            font-size: 0.8rem;
        }
        
        .btn-submit {
            padding: 0.875rem;
            font-size: 0.95rem;
        }
        
        .info-card {
            padding: 0.875rem;
        }
        
        .info-icon {
            width: 38px;
            height: 38px;
            font-size: 1rem;
        }
        
        .info-content p {
            font-size: 0.9rem;
        }
        
        .map-container {
            height: 180px;
            border-radius: 12px;
        }
        
        .map-controls {
            top: 0.5rem;
            right: 0.5rem;
        }
        
        .map-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }
        
        .toast-container {
            left: 1rem;
            right: 1rem;
            top: 1rem;
        }
        
        .toast-custom {
            min-width: auto;
            width: 100%;
            padding: 0.875rem;
        }
        
        .toast-icon {
            width: 36px;
            height: 36px;
            font-size: 1.1rem;
        }
        
        .toast-content h4 {
            font-size: 0.9rem;
        }
        
        .toast-content p {
            font-size: 0.8rem;
        }
    }

    /* Très petits écrans */
    @media (max-width: 360px) {
        .form-section, .info-section {
            padding: 1rem;
        }
        
        .form-header h1 {
            font-size: 1.2rem;
        }
        
        .info-content p {
            font-size: 0.85rem;
        }
    }

    /* Mode paysage mobile */
    @media (max-height: 500px) and (orientation: landscape) {
        .contact-wrapper {
            display: flex;
            flex-direction: row;
        }
        
        .form-section, .info-section {
            width: 50%;
            overflow-y: auto;
            max-height: 100vh;
        }
        
        .map-container {
            height: 150px;
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        :root {
            --bg-warm: #0f172a;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>

<!-- Container pour les notifications -->
<div id="toastContainer" class="toast-container"></div>

<!-- ===== HERO SECTION ===== -->
<?php if (isset($hero_section) && !empty($hero_section)): ?>
<div class="hero-section position-relative overflow-hidden">
    <?php if (!empty($hero_section['image_url'])): ?>
    <div class="hero-bg-image">
        <img src="<?php echo base_url($hero_section['image_url']); ?>" 
             alt="<?php echo isset($hero_section['titre_section']) ? $hero_section['titre_section'] : 'FAQ'; ?>"
             class="w-100 h-100 object-fit-cover">
    </div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <?php if (!empty($hero_section['titre_section'])): ?>
                        <h1 class="hero-title animate__animated animate__fadeInUp">
                            <?php echo $hero_section['titre_section']; ?>
                        </h1>
                    <?php else: ?>
                        <h1 class="hero-title animate__animated animate__fadeInUp">FAQ</h1>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['sous_titre'])): ?>
                        <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                            <?php echo $hero_section['sous_titre']; ?>
                        </h2>
                    <?php else: ?>
                        <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                            Questions Fréquemment Posées
                        </h2>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['contenu_texte'])): ?>
                        <p class="hero-text animate__animated animate__fadeInUp animate__delay-2s">
                            <?php echo $hero_section['contenu_texte']; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php else: ?>
<!-- Hero par défaut si pas de hero_section -->
<div class="hero-section position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="hero-content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <h1 class="hero-title animate__animated animate__fadeInUp">Contact Us</h1>
                    <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                        We're Here to Help
                    </h2>
                    <p class="hero-text animate__animated animate__fadeInUp animate__delay-2s">
                        Have a question, project, or suggestion? Reach out to us and our team will get back to you as soon as possible.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php endif; ?>
<!-- Section Principale -->
<section class="contact-hero">
    <div class="container-fluid p-0">
        <div class="contact-wrapper">
            
            <!-- Formulaire -->
            <div class="form-section">
                <div class="form-header">
                    <h1 class="text-success">Contact us</h1>
                    <p>Fill out the form below and we'll get back to you within 24 hours.</p>
                </div>

                <form id="contactForm" novalidate>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>" id="csrfToken">

                    <!-- Nom -->
                    <div class="form-floating">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="John Doe" required maxlength="250" autocomplete="name">
                        <label for="fullname">Full name *</label>
                        <div class="invalid-feedback">Please enter your name (min. 2 characters)</div>
                        <span class="char-count" id="fullnameCount">0/250</span>
                    </div>

                    <!-- Email -->
                    <div class="form-floating">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required maxlength="250" autocomplete="email">
                        <label for="email">Email address *</label>
                        <div class="invalid-feedback">Please enter a valid email address</div>
                    </div>

                    <!-- Téléphone -->
                    <div class="form-floating">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+1 234 567 890" required maxlength="12" autocomplete="tel">
                        <label for="phone">Phone *</label>
                        <div class="invalid-feedback">Invalid format (max 12 characters)</div>
                        <span class="char-count" id="phoneCount">0/12</span>
                    </div>

                    <!-- Localisation -->
                    <div class="form-floating">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <input type="text" class="form-control" id="location" name="location" placeholder="New York, USA" maxlength="200" autocomplete="address-level1">
                        <label for="location">Location</label>
                        <span class="char-count" id="locationCount">0/200</span>
                    </div>

                    <!-- Sujet -->
                    <div class="form-floating">
                        <i class="fas fa-tag input-icon"></i>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject of your message" required maxlength="250">
                        <label for="subject">Subject *</label>
                        <div class="invalid-feedback">Subject must contain at least 3 characters</div>
                        <span class="char-count" id="subjectCount">0/250</span>
                    </div>

                    <!-- Message -->
                    <div class="form-floating">
                        <i class="fas fa-comment input-icon"></i>
                        <textarea class="form-control" id="message" name="message" placeholder="Your message..." required></textarea>
                        <label for="message">Message *</label>
                        <div class="invalid-feedback">Message must contain at least 10 characters</div>
                        <span class="char-count" id="messageCount">0 characters</span>
                    </div>

                    <!-- Consentement -->
                    <div class="consent-wrapper">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                            <label class="form-check-label" for="consent">
                                I agree that my personal data will be processed according to the 
                                <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">privacy policy</a> 
                                *
                            </label>
                        </div>
                        <div class="invalid-feedback ms-4 mt-2">You must accept the terms to continue</div>
                    </div>

                    <!-- Bouton Submit -->
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="spinner"></span>
                        <span class="btn-text">
                            <i class="fas fa-paper-plane"></i>
                            Send message
                        </span>
                    </button>
                </form>
            </div>

            <!-- Section Info & Carte -->
            <div class="info-section">
                <!-- Carte -->
                <div class="map-container">
                    <div id="map"></div>
                    <div class="map-overlay">
                        <h3><i class="fas fa-map-pin me-2"></i><?= $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI') ?></h3>
                        <p><?= $this->Model->get_setting('adresse_siege', 'Bujumbura, République du Burundi') ?></p>
                    </div>
                    <div class="map-controls">
                        <button class="map-btn" onclick="zoomIn()" title="Zoom in" aria-label="Zoom in">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="map-btn" onclick="zoomOut()" title="Zoom out" aria-label="Zoom out">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="map-btn" onclick="getDirections()" title="Get directions" aria-label="Get directions">
                            <i class="fas fa-directions"></i>
                        </button>
                        <button class="map-btn" onclick="resetMap()" title="Reset map" aria-label="Reset map">
                            <i class="fas fa-compress-arrows-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Infos Contact -->
                <div class="info-cards">
                    <div class="info-card" onclick="copyToClipboard('<?= $this->Model->get_setting('site_phone', '+257 79 666 439') ?>')" role="button" tabindex="0">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Phone</h4>
                            <p><?= $this->Model->get_setting('site_phone', '+257 79 666 439') ?></p>
                            <small>Click to copy</small>
                        </div>
                    </div>

                    <div class="info-card" onclick="window.location.href='mailto:<?= $this->Model->get_setting('contact_email_invest', 'nufotecburundi2026@gmail.com') ?>'" role="button" tabindex="0">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p><?= $this->Model->get_setting('contact_email_invest', 'nufotecburundi2026@gmail.com') ?></p>
                            <small>Click to send email</small>
                        </div>
                    </div>

                    <div class="info-card" role="button" tabindex="0">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Hours</h4>
                            <p><?= $this->Model->get_setting('horaires_travail', 'Dimanche - Vendredi: 8h00 - 17h00') ?></p>
                            <span class="hours-badge" id="statusBadge">
                                <i class="fas fa-circle"></i>
                                <span id="statusText">Checking...</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Privacy -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>We collect your personal data only to process your contact request. This information is stored securely and is never shared with third parties without your explicit consent.</p>
                <p>In accordance with GDPR, you have the right to access, rectify and delete your data. Contact us at privacy@nufotec.com to exercise these rights.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background: var(--primary); border: none;">I understand</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Success -->
<div class="modal fade modal-success" id="successModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Message sent!</h5>
            </div>
            <div class="modal-body">
                <div class="success-animation">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3>Thank you <span id="successName">!</span></h3>
                <p class="text-muted">Your message has been received. Our team will get back to you shortly.</p>
                <button type="button" class="btn btn-primary mt-3" onclick="closeSuccessModal()" style="background: var(--primary); border: none; padding: 0.75rem 2rem; border-radius: 12px; font-weight: 600;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>

<script>
    // Variables globales
    let map, marker, currentZoom = 15;
    const nufotecLocation = { lat: 40.7128, lng: -74.0060 }; // New York (changez selon votre localisation)

    // Initialisation Google Maps
    function initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        try {
            map = new google.maps.Map(mapElement, {
                center: nufotecLocation,
                zoom: currentZoom,
                mapTypeId: 'roadmap',
                disableDefaultUI: true,
                zoomControl: false,
                gestureHandling: 'cooperative',
                styles: [
                    { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] },
                    { featureType: "transit", elementType: "labels", stylers: [{ visibility: "off" }] },
                    { featureType: "water", elementType: "geometry", stylers: [{ color: "#e0f2fe" }] },
                    { featureType: "landscape", elementType: "geometry", stylers: [{ color: "#f0fdf4" }] }
                ]
            });

            marker = new google.maps.Marker({
                position: nufotecLocation,
                map: map,
                animation: google.maps.Animation.DROP,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: '#0B4F2E',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                }
            });

            const infowindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 8px; max-width: 180px;">
                        <h5 style="margin: 0 0 4px 0; color: #0B4F2E; font-size: 0.95rem;">Nufotec</h5>
                        <p style="margin: 0; font-size: 0.85rem;">123 Business Street</p>
                    </div>
                `
            });

            marker.addListener('click', () => infowindow.open(map, marker));
            
            // Cercle autour du marqueur
            new google.maps.Circle({
                strokeColor: '#0B4F2E',
                strokeOpacity: 0.2,
                strokeWeight: 1,
                fillColor: '#0B4F2E',
                fillOpacity: 0.05,
                map: map,
                center: nufotecLocation,
                radius: 200
            });
        } catch (e) {
            console.error('Map initialization failed:', e);
            mapElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:white;background:#0B4F2E;"><p>Map loading failed</p></div>';
        }
    }

    function zoomIn() {
        if (!map) return;
        currentZoom = Math.min(currentZoom + 1, 20);
        map.setZoom(currentZoom);
    }

    function zoomOut() {
        if (!map) return;
        currentZoom = Math.max(currentZoom - 1, 10);
        map.setZoom(currentZoom);
    }

    function resetMap() {
        if (!map) return;
        currentZoom = 15;
        map.setCenter(nufotecLocation);
        map.setZoom(currentZoom);
    }

    function getDirections() {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${nufotecLocation.lat},${nufotecLocation.lng}`, '_blank');
    }

    // Système de notification Toast
    function showToast(type, title, message, duration = 4000) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-custom ${type}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle'
        };

        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas ${icons[type]}" aria-hidden="true"></i>
            </div>
            <div class="toast-content">
                <h4>${title}</h4>
                <p>${message}</p>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Close notification">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        `;

        container.appendChild(toast);

        // Auto-remove
        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 400);
        }, duration);
    }

    // Validation et Compteurs
    const fields = {
        fullname: { min: 2, max: 250, required: true },
        email: { type: 'email', required: true },
        phone: { max: 12, pattern: /^[0-9+\-\s()]+$/, required: true },
        location: { max: 200 },
        subject: { min: 3, max: 250, required: true },
        message: { min: 10, required: true }
    };

    function validateField(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return true;
        
        const value = field.value.trim();
        const rules = fields[fieldId];
        
        field.classList.remove('is-valid', 'is-invalid');
        
        if (!rules) return true;

        if (rules.required && !value) {
            field.classList.add('is-invalid');
            return false;
        }

        if (value) {
            if (rules.min && value.length < rules.min) {
                field.classList.add('is-invalid');
                return false;
            }
            if (rules.max && value.length > rules.max) {
                field.classList.add('is-invalid');
                return false;
            }
            if (rules.pattern && !rules.pattern.test(value)) {
                field.classList.add('is-invalid');
                return false;
            }
            if (rules.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    field.classList.add('is-invalid');
                    return false;
                }
            }
        }

        if (value || !rules.required) {
            field.classList.add('is-valid');
        }
        return true;
    }

    // Compteurs de caractères
    ['fullname', 'phone', 'location', 'subject', 'message'].forEach(id => {
        const field = document.getElementById(id);
        const counter = document.getElementById(id + 'Count');
        if (!field || !counter) return;
        
        field.addEventListener('input', function() {
            const count = this.value.length;
            const max = this.getAttribute('maxlength');
            
            if (max) {
                counter.textContent = `${count}/${max}`;
                counter.classList.toggle('warning', count > max * 0.8);
                counter.classList.toggle('error', count >= max);
            } else {
                counter.textContent = `${count} characters`;
            }
            
            validateField(id);
        });
    });

    // Validation en temps réel
    Object.keys(fields).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field) return;
        
        field.addEventListener('blur', () => validateField(fieldId));
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) {
                validateField(fieldId);
            }
        });
    });

    // Vérification des heures d'ouverture
    function checkBusinessHours() {
        const now = new Date();
        const day = now.getDay();
        const hour = now.getHours();
        const badge = document.getElementById('statusBadge');
        const text = document.getElementById('statusText');
        
        if (!badge || !text) return;
        
        let isOpen = false;
        
        if (day >= 1 && day <= 5) { // Mon-Fri
            isOpen = hour >= 8 && hour < 18;
        } else if (day === 6) { // Saturday
            isOpen = hour >= 9 && hour < 13;
        }
        
        if (isOpen) {
            badge.classList.remove('closed');
            badge.innerHTML = '<i class="fas fa-circle text-success me-1"></i><span>Open now</span>';
        } else {
            badge.classList.add('closed');
            badge.innerHTML = '<i class="fas fa-circle text-danger me-1"></i><span>Closed</span>';
        }
    }

    // Copier dans le presse-papiers
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('success', 'Copied!', 'Number copied to clipboard');
            }).catch(() => {
                showToast('error', 'Error', 'Unable to copy');
            });
        } else {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showToast('success', 'Copied!', 'Number copied to clipboard');
            } catch (err) {
                showToast('error', 'Error', 'Unable to copy');
            }
            document.body.removeChild(textarea);
        }
    }

    // Soumission du formulaire avec AJAX
    document.getElementById('contactForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validation finale
        let isValid = true;
        Object.keys(fields).forEach(id => {
            if (!validateField(id)) isValid = false;
        });
        
        const consent = document.getElementById('consent');
        if (consent && !consent.checked) {
            consent.classList.add('is-invalid');
            isValid = false;
        } else if (consent) {
            consent.classList.remove('is-invalid');
        }
        
        if (!isValid) {
            showToast('error', 'Error', 'Please correct the errors in the form');
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                firstError.focus();
            }
            return;
        }

        // Loading state
        const btn = document.getElementById('submitBtn');
        if (!btn) return;
        
        btn.classList.add('loading');
        btn.disabled = true;

        // Préparation des données
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('<?= base_url('Home/Contact/sendMessage') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            let result;
            try {
                result = await response.json();
            } catch (e) {
                throw new Error('Invalid response from server');
            }

            if (result.success) {
                // Afficher le modal de succès
                const successName = document.getElementById('successName');
                if (successName) {
                    successName.textContent = (data.fullname || 'Guest').split(' ')[0] + '!';
                }
                
                const modalEl = document.getElementById('successModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
                
                // Reset formulaire
                this.reset();
                document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
                document.querySelectorAll('.char-count').forEach(el => {
                    if (el.id.includes('message')) el.textContent = '0 characters';
                    else if (el.id.includes('phone')) el.textContent = '0/12';
                    else el.textContent = '0/250';
                });
                
                showToast('success', 'Message sent', "We'll get back to you shortly");
            } else {
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const el = document.getElementById(field);
                        if (el) {
                            el.classList.add('is-invalid');
                            const feedback = el.parentElement?.querySelector('.invalid-feedback');
                            if (feedback) feedback.textContent = result.errors[field];
                        }
                    });
                }
                showToast('error', 'Error', result.message || 'An error occurred');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Network Error', 'Unable to contact server. Please try again.');
        } finally {
            btn.classList.remove('loading');
            btn.disabled = false;
            
            // Rafraîchir le token CSRF si présent
            if (result?.csrf_token) {
                const csrfToken = document.getElementById('csrfToken');
                if (csrfToken) csrfToken.value = result.csrf_token;
            }
        }
    });

    function closeSuccessModal() {
        const modalEl = document.getElementById('successModal');
        if (!modalEl) return;
        
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        } else {
            // Fallback
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    }

    // Animation au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -30px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Observer les cartes
        document.querySelectorAll('.info-card').forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(15px)';
            el.style.transition = `all 0.5s ease ${index * 0.1}s`;
            observer.observe(el);
        });

        // Vérifier les heures
        checkBusinessHours();
        setInterval(checkBusinessHours, 60000);

        // Support clavier pour les cartes info
        document.querySelectorAll('.info-card').forEach(card => {
            card.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
    });

    // Gestion du redimensionnement
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (map) {
                google.maps.event.trigger(map, 'resize');
                map.setCenter(nufotecLocation);
            }
        }, 250);
    });
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>