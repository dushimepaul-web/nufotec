<?php
// Définition des fonctions manquantes (inchangé)
if (!function_exists('fix_image_path')) {
    function fix_image_path($path) {
        if (empty($path)) return '';
        if (preg_match('#^https?://#', $path)) {
            return $path;
        }
        $CI =& get_instance();
        return $CI->config->base_url($path);
    }
}

if (!function_exists('fix_content_images')) {
    function fix_content_images($content) {
        if (empty($content)) return $content;
        return preg_replace_callback(
            '<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i',
            function($matches) {
                $old_src = $matches[1];
                $new_src = fix_image_path($old_src);
                return str_replace($old_src, $new_src, $matches[0]);
            },
            $content
        );
    }
}
?>

<?php include VIEWPATH . 'includes/frontend/Header.php'; ?>

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

    /* ===== SECTION HERO ===== */
    .page-hero {
        position: relative;
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        background-color: #0f4c3a; /* fallback */
        overflow: hidden;
        padding: 4rem 0;
    }
    .hero-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        z-index: 1;
    }
    .hero-gradient-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(15,76,58,0.85) 0%, rgba(26,95,74,0.9) 100%);
        z-index: 2;
    }
    .page-hero .container {
        position: relative;
        z-index: 3;
    }
    .page-hero-title {
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
    }
    .page-hero-title span {
        display: block;
        font-size: 1.5rem;
        font-weight: 400;
        color: #d4af37;
    }
    .page-hero-subtitle {
        font-size: 1.25rem;
        max-width: 700px;
        margin: 1.5rem auto;
        opacity: 0.95;
    }
    .cta-button {
        display: inline-block;
        background: #d4af37;
        color: #0f4c3a;
        font-weight: 600;
        padding: 0.9rem 2.5rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s;
        border: 2px solid #d4af37;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .cta-button:hover {
        background: transparent;
        color: #fff;
        border-color: #fff;
    }

    /* ===== SECTIONS TEXTE (CARTES) ===== */
    .section-texte {
        padding: 5rem 0;
        background-color: #fff;
    }
    .section-texte:nth-child(even) {
        background-color: #f9f9f9;
    }

    /* Carte personnalisée */
    .card-custom {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 40px -12px rgba(0,32,64,0.15);
        padding: 2.5rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .card-custom:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 50px -12px rgba(15,76,58,0.25);
    }

    .section-tag {
        display: inline-block;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #d4af37;
        background: rgba(212,175,55,0.1);
        padding: 0.3rem 1rem;
        border-radius: 30px;
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: clamp(1.8rem, 4vw, 2.5rem);
        font-weight: 700;
        color: #0f4c3a;
        margin-bottom: 1.2rem;
    }

    .tinymce-content {
        font-size: 1.1rem;
        color: #334155;
    }
    .tinymce-content p:last-child {
        margin-bottom: 0;
    }
    .tinymce-content.text-center {
        text-align: center;
    }

    .btn-primary-custom {
        display: inline-block;
        background: #0f4c3a;
        color: white;
        font-weight: 600;
        padding: 0.8rem 2rem;
        border-radius: 40px;
        text-decoration: none;
        border: 2px solid #0f4c3a;
        transition: all 0.3s;
    }
    .btn-primary-custom:hover {
        background: transparent;
        color: #0f4c3a;
    }

    /* Images dans les cartes */
    .image-wrapper {
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .image-wrapper img {
        width: 100%;
        height: auto;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .image-wrapper:hover img {
        transform: scale(1.05);
    }

    /* Espacement responsive */
    .g-5 {
        --bs-gutter-y: 3rem;
    }

    @media (max-width: 768px) {
        .section-texte {
            padding: 3rem 0;
        }
        .card-custom {
            padding: 1.8rem;
        }
        .page-hero-title {
            font-size: 2.2rem;
        }
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
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 99999;
        display: flex;
        flex-direction: column-reverse;
        align-items: center;
        gap: 0.75rem;
        padding: 0 20px;
        pointer-events: none;
        max-width: 100%;
        width: auto;
    }

    .toast-custom {
        background: white;
        border-radius: var(--radius-md);
        padding: 1.25rem;
        box-shadow: var(--shadow-lg);
        display: flex;
        align-items: center;
        gap: 1rem;
        width: 100%;
        transform: translateX(120%);
        animation: slideIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        border-left: 4px solid;
        pointer-events: all;
        position: relative;
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
        to { transform: translateX(120%); opacity: 0; }
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

    .toast-content {
        flex: 1;
        min-width: 0;
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
        word-wrap: break-word;
    }

    .toast-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: var(--radius-sm);
        transition: all 0.3s;
        flex-shrink: 0;
    }

    .toast-close:hover {
        background: #f1f5f9;
        color: var(--text-dark);
    }

    /* Responsive toast */
    @media (max-width: 576px) {
        .toast-container {
            top: 80px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
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
</style>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

<?php
// ============================================
// SECTION HERO (unique)
// ============================================
if (!empty($hero)):
    $options = $hero['options'] ?? [];
    $image_opacity = $options['image_opacity'] ?? '0.85';
    $raw_content = $hero['contenu_texte'] ?? '';
?>
    <section class="page-hero <?= $hero['custom_class'] ?? '' ?>">
        <?php if (!empty($hero['image_url'])): ?>
            <div class="hero-bg-image" style="background-image: url('<?= fix_image_path($hero['image_url']) ?>'); opacity: <?= $image_opacity ?>;"></div>
        <?php endif; ?>
        <div class="hero-gradient-overlay"></div>
        <div class="container position-relative">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="page-hero-title">
                        <?= $hero['titre_section'] ?? '' ?>
                        <?php if (!empty($hero['sous_titre'])): ?>
                            <span><?= $hero['sous_titre'] ?></span>
                        <?php endif; ?>
                    </h1>
                    <?php if (!empty($raw_content)): ?>
                        <p class="page-hero-subtitle"><?= strip_tags($raw_content) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($hero['bouton_texte'])): ?>
                        <a href="<?= $hero['bouton_lien'] ?? '#' ?>" class="cta-button">
                            <?= $hero['bouton_texte'] ?> <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php
// ============================================
// SECTIONS TEXTE (multiples)
// ============================================
foreach ($textes as $texte):
    $options = $texte['options'] ?? [];
    $layout = $options['layout'] ?? 'simple';
    $has_image = !empty($texte['image_url']);
    $image_url = $has_image ? fix_image_path($texte['image_url']) : null;
    $text_align = $options['text_align'] ?? 'text-center';
    $raw_content = $texte['contenu_texte'] ?? '';
    $content_with_fixed_images = fix_content_images($raw_content);
    $bg_color = $options['bg_color'] ?? 'transparent';
?>

    <section class="section-texte <?= $texte['custom_class'] ?? '' ?>" style="background: <?= $bg_color ?>;">
        <div class="container">
            <?php if ($layout === 'with-image-left' && $has_image): ?>
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <div class="image-wrapper">
                            <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($texte['titre_section'] ?? 'Image') ?>" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card-custom <?= $text_align ?>">
                            <?php if (!empty($texte['titre_section'])): ?>
                                <span class="section-tag"><?= htmlspecialchars($texte['titre_section']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($texte['sous_titre'])): ?>
                                <h2 class="section-title"><?= htmlspecialchars($texte['sous_titre']) ?></h2>
                            <?php endif; ?>
                            <div class="tinymce-content">
                                <?= $content_with_fixed_images ?>
                            </div>
                            <?php if (!empty($texte['bouton_texte']) && !empty($texte['bouton_lien'])): ?>
                                <a href="<?= htmlspecialchars($texte['bouton_lien']) ?>" class="btn-primary-custom mt-4 align-self-center">
                                    <?= htmlspecialchars($texte['bouton_texte']) ?> <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($layout === 'with-image-right' && $has_image): ?>
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <div class="card-custom <?= $text_align ?>">
                            <?php if (!empty($texte['titre_section'])): ?>
                                <span class="section-tag"><?= htmlspecialchars($texte['titre_section']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($texte['sous_titre'])): ?>
                                <h2 class="section-title"><?= htmlspecialchars($texte['sous_titre']) ?></h2>
                            <?php endif; ?>
                            <div class="tinymce-content">
                                <?= $content_with_fixed_images ?>
                            </div>
                            <?php if (!empty($texte['bouton_texte']) && !empty($texte['bouton_lien'])): ?>
                                <a href="<?= htmlspecialchars($texte['bouton_lien']) ?>" class="btn-primary-custom mt-4 align-self-center">
                                    <?= htmlspecialchars($texte['bouton_texte']) ?> <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="image-wrapper">
                            <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($texte['titre_section'] ?? 'Image') ?>" class="img-fluid">
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card-custom <?= $text_align ?>">
                            <?php if (!empty($texte['titre_section']) || !empty($texte['sous_titre'])): ?>
                                <div class="mb-4">
                                    <?php if (!empty($texte['titre_section'])): ?>
                                        <span class="section-tag"><?= htmlspecialchars($texte['titre_section']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($texte['sous_titre'])): ?>
                                        <h2 class="section-title"><?= htmlspecialchars($texte['sous_titre']) ?></h2>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="tinymce-content">
                                <?= $content_with_fixed_images ?>
                            </div>
                            <?php if (!empty($texte['bouton_texte']) && !empty($texte['bouton_lien'])): ?>
                                <div class="mt-5">
                                    <a href="<?= htmlspecialchars($texte['bouton_lien']) ?>" class="btn-primary-custom">
                                        <?= htmlspecialchars($texte['bouton_texte']) ?> <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php endforeach; ?>

<!-- Main Container -->
<div class="main-container">
    <div class="form-card">

        <!-- Progress Steps (Traduits) -->
        <div class="progress-steps">
            <div class="step active" data-step="1" onclick="goToStep(1)">
                <div class="step-number">1</div>
                <div class="step-text"><?= t('step_identity') ?></div>
            </div>
            <div class="step" data-step="2" onclick="goToStep(2)">
                <div class="step-number">2</div>
                <div class="step-text"><?= t('step_location') ?></div>
            </div>
            <div class="step" data-step="3" onclick="goToStep(3)">
                <div class="step-number">3</div>
                <div class="step-text"><?= t('step_regulation') ?></div>
            </div>
            <div class="step" data-step="4" onclick="goToStep(4)">
                <div class="step-number">4</div>
                <div class="step-text"><?= t('step_capacities') ?></div>
            </div>
            <div class="step" data-step="5" onclick="goToStep(5)">
                <div class="step-number">5</div>
                <div class="step-text"><?= t('step_finalization') ?></div>
            </div>
        </div>

        <!-- Formulaire -->
        <form id="brokerForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $this->security->get_csrf_hash() ?>" id="csrfToken">

            <!-- Étape 1: Identité -->
            <div class="form-section active" id="step-1">
                <h2 class="section-title">
                    <i class="fas fa-user-circle"></i>
                    <?= t('identity_info_title') ?>
                </h2>
                <p class="section-subtitle"><?= t('identity_info_subtitle') ?></p>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-user"></i>
                            <?= t('full_name') ?> <span class="text-danger">*</span>
                            <i class="fas fa-info-circle tooltip-icon" data-bs-toggle="tooltip" title="<?= t('full_name_tooltip') ?>"></i>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text"
                                   class="form-control"
                                   name="full_name"
                                   id="full_name"
                                   placeholder="<?= t('full_name_placeholder') ?>"
                                   required
                                   maxlength="150">
                        </div>
                        <div class="invalid-feedback" id="full_name-error"></div>
                        <div class="char-counter" id="full_name-count">0/150</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-building"></i>
                            <?= t('firm_name') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <input type="text"
                                   class="form-control"
                                   name="firm_name"
                                   id="firm_name"
                                   placeholder="<?= t('firm_name_placeholder') ?>"
                                   required
                                   maxlength="200">
                        </div>
                        <div class="invalid-feedback" id="firm_name-error"></div>
                        <div class="char-counter" id="firm_name-count">0/200</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-briefcase"></i>
                            <?= t('jurisdiction_incorporation') ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-gavel"></i></span>
                            <input type="text"
                                   class="form-control"
                                   name="jurisdiction_of_incorporation"
                                   id="jurisdiction_of_incorporation"
                                   placeholder="<?= t('jurisdiction_placeholder') ?>"
                                   maxlength="150">
                        </div>
                        <div class="invalid-feedback" id="jurisdiction_of_incorporation-error"></div>
                        <div class="char-counter" id="jurisdiction_of_incorporation-count">0/150</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-hashtag"></i>
                            <?= t('registration_number') ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                            <input type="text"
                                   class="form-control"
                                   name="registration_number"
                                   id="registration_number"
                                   placeholder="<?= t('registration_placeholder') ?>"
                                   maxlength="100">
                        </div>
                        <div class="invalid-feedback" id="registration_number-error"></div>
                        <div class="char-counter" id="registration_number-count">0/100</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            <?= t('professional_email') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email"
                                   class="form-control"
                                   name="email"
                                   id="email"
                                   placeholder="<?= t('email_placeholder') ?>"
                                   required
                                   maxlength="150">
                        </div>
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-phone"></i>
                            <?= t('phone_label') ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel"
                                   class="form-control"
                                   name="mobile_phone"
                                   id="mobile_phone"
                                   placeholder="+225 01 23 45 67 89"
                                   maxlength="50">
                        </div>
                        <div class="invalid-feedback" id="mobile_phone-error"></div>
                        <div class="char-counter" id="mobile_phone-count">0/50</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fab fa-whatsapp"></i>
                            <?= t('whatsapp_label') ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fab fa-whatsapp" style="color: #25D366;"></i></span>
                            <input type="tel"
                                   class="form-control"
                                   name="whatsapp"
                                   id="whatsapp"
                                   placeholder="+225 01 23 45 67 89"
                                   maxlength="50">
                        </div>
                        <div class="invalid-feedback" id="whatsapp-error"></div>
                        <div class="char-counter" id="whatsapp-count">0/50</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-globe"></i>
                            <?= t('website_label') ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-globe"></i></span>
                            <input type="url"
                                   class="form-control"
                                   name="corporate_website"
                                   id="corporate_website"
                                   placeholder="https://www.votresite.com"
                                   maxlength="200">
                        </div>
                        <div class="invalid-feedback" id="corporate_website-error"></div>
                        <div class="char-counter" id="corporate_website-count">0/200</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-5">
                    <button type="button" class="btn btn-nav btn-next" onclick="validateStep(1, 2)">
                        <?= t('continue_btn') ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 2: Localisation -->
            <div class="form-section" id="step-2">
                <h2 class="section-title">
                    <i class="fas fa-map-marked-alt" style="color: var(--info);"></i>
                    <?= t('location_title') ?>
                </h2>
                <p class="section-subtitle"><?= t('location_subtitle') ?></p>

                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= t('country_label') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="country-search-container">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text"
                                       class="form-control country-search"
                                       id="pays_search"
                                       placeholder="<?= t('country_search_placeholder') ?>"
                                       data-target="pays"
                                       autocomplete="off"
                                       required>
                            </div>
                            <div class="country-dropdown" id="pays_dropdown"></div>
                            <input type="hidden" name="id_pays" id="pays_id">
                        </div>
                        <div class="invalid-feedback" id="pays-error"></div>
                        <div id="pays_selected" class="selected-country-badge" style="display: none;">
                            <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                            <span id="pays_name"></span>
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="clearCountry('pays')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="map-container">
                    <div id="map"></div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left"></i> <?= t('back_btn') ?>
                    </button>
                    <button type="button" class="btn btn-nav btn-next" onclick="validateStep(2, 3)">
                        <?= t('continue_btn') ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 3: Régulation -->
            <div class="form-section" id="step-3">
                <h2 class="section-title">
                    <i class="fas fa-shield-alt" style="color: var(--violet);"></i>
                    <?= t('regulation_title') ?>
                </h2>
                <p class="section-subtitle"><?= t('regulation_subtitle') ?></p>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong><?= t('important_label') ?> :</strong> <?= t('regulation_info_text') ?>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label"><?= t('regulatory_status_label') ?></label>
                        <select name="regulatory_status" class="form-select" id="regulatory_status">
                            <option value=""><?= t('select_option') ?></option>
                            <option value="Licensed"><?= t('status_licensed') ?></option>
                            <option value="Exempt"><?= t('status_exempt') ?></option>
                            <option value="Unlicensed"><?= t('status_unlicensed') ?></option>
                        </select>
                        <div class="invalid-feedback" id="regulatory_status-error"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><?= t('regulatory_authority_label') ?></label>
                        <input type="text"
                               class="form-control"
                               name="regulatory_authority"
                               id="regulatory_authority"
                               placeholder="<?= t('regulatory_authority_placeholder') ?>"
                               maxlength="150">
                        <div class="invalid-feedback" id="regulatory_authority-error"></div>
                        <div class="char-counter" id="regulatory_authority-count">0/150</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left"></i> <?= t('back_btn') ?>
                    </button>
                    <button type="button" class="btn btn-nav btn-next" onclick="validateStep(3, 4)">
                        <?= t('continue_btn') ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 4: Capacités -->
            <div class="form-section" id="step-4">
                <h2 class="section-title">
                    <i class="fas fa-briefcase" style="color: var(--warning);"></i>
                    <?= t('capacities_title') ?>
                </h2>
                <p class="section-subtitle"><?= t('capacities_subtitle') ?></p>

                <div class="mb-4">
                    <label class="form-label d-block mb-3">
                        <i class="fas fa-check-circle"></i>
                        <?= t('your_capacities_label') ?> <span class="text-danger">*</span>
                    </label>
                    <div class="checkbox-grid" id="capacityGroup">
                        <?php
                        $capacities = [
                            'capacity_investment_broker' => ['label' => t('cap_investment_broker'), 'icon' => 'fa-chart-line', 'color' => '#0B4F2E', 'desc' => t('cap_investment_broker_desc')],
                            'capacity_placement_agent' => ['label' => t('cap_placement_agent'), 'icon' => 'fa-handshake', 'color' => '#FF6B35', 'desc' => t('cap_placement_agent_desc')],
                            'capacity_corporate_finance_advisor' => ['label' => t('cap_corporate_finance'), 'icon' => 'fa-user-tie', 'color' => '#3498DB', 'desc' => t('cap_corporate_finance_desc')],
                            'capacity_fund_manager' => ['label' => t('cap_fund_manager'), 'icon' => 'fa-university', 'color' => '#9B59B6', 'desc' => t('cap_fund_manager_desc')],
                            'capacity_family_office_rep' => ['label' => t('cap_family_office'), 'icon' => 'fa-home', 'color' => '#FFD700', 'desc' => t('cap_family_office_desc')],
                            'capacity_esg_advisor' => ['label' => t('cap_esg_advisor'), 'icon' => 'fa-leaf', 'color' => '#27ae60', 'desc' => t('cap_esg_advisor_desc')],
                            'capacity_independent_introducer' => ['label' => t('cap_independent_introducer'), 'icon' => 'fa-user', 'color' => '#94a3b8', 'desc' => t('cap_independent_introducer_desc')]
                        ];
                        foreach ($capacities as $key => $cap):
                        ?>
                        <div class="checkbox-card" onclick="toggleCheckbox(this, 'capacity')">
                            <input type="checkbox" name="<?= $key ?>" value="1">
                            <div class="checkbox-icon" style="background: <?= $cap['color'] ?>20; color: <?= $cap['color'] ?>;">
                                <i class="fas <?= $cap['icon'] ?>"></i>
                            </div>
                            <div class="fw-semibold"><?= htmlspecialchars($cap['label']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($cap['desc']) ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="invalid-feedback capacity-error" style="display: none;"><?= t('capacity_error') ?></div>
                </div>

                <div class="mb-4">
                    <label class="form-label"><?= t('other_capacity_label') ?></label>
                    <input type="text"
                           class="form-control"
                           name="capacity_other"
                           id="capacity_other"
                           placeholder="<?= t('other_capacity_placeholder') ?>"
                           maxlength="255">
                    <div class="char-counter" id="capacity_other-count">0/255</div>
                </div>

                <div class="mb-4">
                    <label class="form-label d-block mb-3">
                        <i class="fas fa-users"></i>
                        <?= t('investor_types_label') ?>
                    </label>
                    <div class="checkbox-grid">
                        <?php
                        $investors = [
                            'investor_private_equity' => ['label' => t('investor_pe'), 'icon' => 'fa-building', 'color' => '#e74c3c'],
                            'investor_venture_capital' => ['label' => t('investor_vc'), 'icon' => 'fa-rocket', 'color' => '#9b59b6'],
                            'investor_esg_impact' => ['label' => t('investor_esg'), 'icon' => 'fa-leaf', 'color' => '#27ae60'],
                            'investor_dfi' => ['label' => t('investor_dfi'), 'icon' => 'fa-globe', 'color' => '#3498db'],
                            'investor_institutional' => ['label' => t('investor_institutional'), 'icon' => 'fa-landmark', 'color' => '#f39c12'],
                            'investor_hnwi' => ['label' => t('investor_hnwi'), 'icon' => 'fa-gem', 'color' => '#1abc9c'],
                            'investor_sovereign' => ['label' => t('investor_sovereign'), 'icon' => 'fa-crown', 'color' => '#e67e22']
                        ];
                        foreach ($investors as $key => $inv):
                        ?>
                        <div class="checkbox-card" onclick="toggleCheckbox(this, 'investor')">
                            <input type="checkbox" name="<?= $key ?>" value="1">
                            <div class="checkbox-icon mx-auto" style="background: <?= $inv['color'] ?>20; color: <?= $inv['color'] ?>;">
                                <i class="fas <?= $inv['icon'] ?>"></i>
                            </div>
                            <div class="small fw-semibold"><?= htmlspecialchars($inv['label']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label"><?= t('typical_ticket_label') ?></label>
                        <input type="text"
                               class="form-control"
                               name="typical_ticket_size"
                               id="typical_ticket_size"
                               placeholder="<?= t('typical_ticket_placeholder') ?>"
                               maxlength="150">
                        <div class="char-counter" id="typical_ticket_size-count">0/150</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><?= t('geographic_coverage_label') ?></label>
                        <input type="text"
                               class="form-control"
                               name="geographic_coverage"
                               id="geographic_coverage"
                               placeholder="<?= t('geographic_coverage_placeholder') ?>"
                               maxlength="65535">
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(3)">
                        <i class="fas fa-arrow-left"></i> <?= t('back_btn') ?>
                    </button>
                    <button type="button" class="btn btn-nav btn-next" onclick="validateStep(4, 5)">
                        <?= t('continue_btn') ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 5: Finalisation -->
            <div class="form-section" id="step-5">
                <h2 class="section-title">
                    <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                    <?= t('finalization_title') ?>
                </h2>
                <p class="section-subtitle"><?= t('finalization_subtitle') ?></p>

                <div id="summary" class="mb-4"></div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label"><?= t('engagement_model_label') ?></label>
                        <select name="engagement_model" class="form-select" id="engagement_model">
                            <option value=""><?= t('select_option') ?></option>
                            <option value="Success Commission"><?= t('engagement_success_commission') ?></option>
                            <option value="Retainer + Success Fee"><?= t('engagement_retainer_fee') ?></option>
                            <option value="Referral Arrangement"><?= t('engagement_referral') ?></option>
                            <option value="To be negotiated"><?= t('engagement_negotiated') ?></option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><?= t('mandate_type_label') ?></label>
                        <div class="checkbox-grid" style="grid-template-columns: repeat(2, 1fr);">
                            <?php
                            $mandates = [
                                'mandate_equity' => t('mandate_equity'),
                                'mandate_structured_debt' => t('mandate_structured_debt'),
                                'mandate_blended_finance' => t('mandate_blended_finance'),
                                'mandate_grant' => t('mandate_grant'),
                                'mandate_strategic_partnership' => t('mandate_strategic_partnership'),
                                'mandate_full_program' => t('mandate_full_program')
                            ];
                            foreach ($mandates as $key => $label):
                            ?>
                            <div class="checkbox-card" onclick="toggleCheckbox(this, 'mandate')" style="padding: 0.5rem;">
                                <input type="checkbox" name="<?= $key ?>" value="1">
                                <span class="small"><?= htmlspecialchars($label) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-4 p-3 border rounded-3 bg-white">
                    <h6 class="mb-3" style="color: var(--primary);">
                        <i class="fas fa-shield-alt me-2"></i><?= t('compliance_declarations') ?>
                    </h6>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm_authorized" id="confirm_authorized" value="1" required>
                        <label class="form-check-label" for="confirm_authorized">
                            <strong><?= t('confirm_authorized_text') ?></strong>
                        </label>
                        <div class="invalid-feedback" id="confirm_authorized-error"></div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm_aml_kyc" id="confirm_aml_kyc" value="1" required>
                        <label class="form-check-label" for="confirm_aml_kyc">
                            <strong><?= t('confirm_aml_kyc_text') ?></strong>
                        </label>
                        <div class="invalid-feedback" id="confirm_aml_kyc-error"></div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="acknowledge_no_exclusivity" id="acknowledge_no_exclusivity" value="1" required>
                        <label class="form-check-label" for="acknowledge_no_exclusivity">
                            <strong><?= t('acknowledge_no_exclusivity_text') ?></strong>
                        </label>
                        <div class="invalid-feedback" id="acknowledge_no_exclusivity-error"></div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="understand_formal_mandate_required" id="understand_formal_mandate_required" value="1" required>
                        <label class="form-check-label" for="understand_formal_mandate_required">
                            <strong><?= t('understand_formal_mandate_text') ?></strong>
                        </label>
                        <div class="invalid-feedback" id="understand_formal_mandate_required-error"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-prev" onclick="goToStep(4)">
                        <i class="fas fa-arrow-left"></i> <?= t('back_btn') ?>
                    </button>
                    <button type="submit" class="btn btn-nav btn-submit" id="submitBtn">
                        <span class="btn-text">
                            <i class="fas fa-paper-plane me-2"></i><?= t('submit_application') ?>
                        </span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <div class="status-link">
        <p class="text-muted">
            <?= t('already_registered') ?>
            <a href="<?= base_url('brokers/status') ?>">
                <?= t('check_status') ?> <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </p>
    </div>
</div>

<!-- Modal Conditions -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= t('terms_title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6><?= t('terms_acceptance_title') ?></h6>
                <p><?= t('terms_acceptance_text') ?></p>
                <h6><?= t('terms_accuracy_title') ?></h6>
                <p><?= t('terms_accuracy_text') ?></p>
                <h6><?= t('terms_data_use_title') ?></h6>
                <p><?= t('terms_data_use_text') ?></p>
                <h6><?= t('terms_confidentiality_title') ?></h6>
                <p><?= t('terms_confidentiality_text') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?= t('understand_btn') ?></button>
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
                    <i class="fas fa-check-circle me-2"></i><?= t('application_sent') ?>
                </h5>
            </div>
            <div class="modal-body">
                <div class="success-animation">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3><?= t('thank_you') ?> <span id="successName">!</span></h3>
                <p class="text-muted"><?= t('success_message_text') ?></p>
                <button type="button" class="btn btn-primary mt-3" onclick="closeSuccessModal()" style="background: var(--primary); border: none; padding: 0.75rem 2rem; border-radius: 12px;">
                    <?= t('close_btn') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ==================== TRADUCTIONS JAVASCRIPT ====================
    const translations = {
        required_field: '<?= t('required_field_error') ?>',
        min_chars: '<?= t('min_chars_error') ?>',
        max_chars: '<?= t('max_chars_error') ?>',
        invalid_format: '<?= t('invalid_format_error') ?>',
        invalid_email: '<?= t('invalid_email_error') ?>',
        invalid_url: '<?= t('invalid_url_error') ?>',
        select_country: '<?= t('select_country_error') ?>',
        select_capacity: '<?= t('capacity_error') ?>',
        validation_error: '<?= t('validation_error_message') ?>',
        success: '<?= t('success_label') ?>',
        error: '<?= t('error_label') ?>',
        network_error: '<?= t('network_error') ?>'
    };

    function t(key) {
        return translations[key] || key;
    }

    // ==================== VARIABLES GLOBALES ====================
    let currentStep = 1;
    const totalSteps = 5;
    let map, marker;
    const countries = <?= json_encode($pays) ?>;
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

        const customIcon = L.divIcon({
            className: 'custom-marker',
            html: '<i class="fas fa-map-marker-alt" style="color: #0B4F2E; font-size: 30px;"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        marker = L.marker([headquarters.lat, headquarters.lng], { icon: customIcon })
            .addTo(map)
            .bindPopup(`<b>African Green Farmers</b><br><?= t('headquarters_address') ?>`)
            .openPopup();

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
            dropdown.innerHTML = countries.map(country => `
                <div class="country-option"
                     data-id="${country.id}"
                     data-name="${country.pays || country.name}"
                     onclick="selectCountry('${target}', ${country.id}, '${country.pays || country.name}')">
                    <i class="fas fa-map-marker-alt me-2" style="color: var(--info);"></i>
                    ${country.pays || country.name}
                </div>
            `).join('');

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

            search.addEventListener('focus', function() {
                if (this.value.length > 0) dropdown.classList.add('show');
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.country-search-container')) dropdown.classList.remove('show');
            });
        });
    }

    window.selectCountry = function(type, id, name) {
        document.getElementById(`${type}_search`).value = name;
        document.getElementById(`${type}_id`).value = id;
        document.getElementById(`${type}_name`).textContent = name;
        document.getElementById(`${type}_selected`).style.display = 'inline-flex';
        document.getElementById(`${type}_dropdown`).classList.remove('show');
        document.getElementById(`${type}_search`).classList.remove('is-invalid');
        document.getElementById(`${type}-error`).style.display = 'none';
    };

    window.clearCountry = function(type) {
        document.getElementById(`${type}_search`).value = '';
        document.getElementById(`${type}_id`).value = '';
        document.getElementById(`${type}_selected`).style.display = 'none';
        document.getElementById(`${type}_search`).focus();
    };

    // ==================== COMPTEURS DE CARACTÈRES ====================
    function initCharCounters() {
        const fields = ['full_name', 'firm_name', 'jurisdiction_of_incorporation', 'registration_number', 'mobile_phone', 'whatsapp', 'corporate_website', 'regulatory_authority', 'capacity_other', 'typical_ticket_size', 'geographic_coverage'];
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
        if (group === 'capacity') checkCapacityValidation();
    };

    function checkCapacityValidation() {
        const capacityFields = ['capacity_investment_broker', 'capacity_placement_agent', 'capacity_corporate_finance_advisor', 'capacity_fund_manager', 'capacity_family_office_rep', 'capacity_esg_advisor', 'capacity_independent_introducer'];
        let hasCapacity = false;
        for (let field of capacityFields) {
            if (document.querySelector(`input[name="${field}"]`)?.checked) {
                hasCapacity = true;
                break;
            }
        }
        if (hasCapacity) document.querySelector('.capacity-error').style.display = 'none';
    }

    // ==================== VALIDATION ====================
    const validationRules = {
        full_name: { required: true, min: 3, max: 150 },
        firm_name: { required: true, min: 2, max: 200 },
        email: { required: true, type: 'email', max: 150 },
        mobile_phone: { pattern: /^[0-9+\-\s]+$/, max: 50 },
        whatsapp: { pattern: /^[0-9+\-\s]+$/, max: 50 },
        corporate_website: { type: 'url', max: 200 },
        registration_number: { max: 100 },
        regulatory_authority: { max: 150 },
        jurisdiction_of_incorporation: { max: 150 },
        typical_ticket_size: { max: 150 }
    };

    function validateField(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return true;
        const value = field.value.trim();
        const rules = validationRules[fieldId];
        field.classList.remove('is-valid', 'is-invalid');
        if (!rules) return true;
        if (rules.required && !value) {
            showFieldError(field, fieldId + '-error', t('required_field'));
            return false;
        }
        if (value) {
            if (rules.min && value.length < rules.min) {
                showFieldError(field, fieldId + '-error', t('min_chars').replace('{min}', rules.min));
                return false;
            }
            if (rules.max && value.length > rules.max) {
                showFieldError(field, fieldId + '-error', t('max_chars').replace('{max}', rules.max));
                return false;
            }
            if (rules.pattern && !rules.pattern.test(value)) {
                showFieldError(field, fieldId + '-error', t('invalid_format'));
                return false;
            }
            if (rules.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    showFieldError(field, fieldId + '-error', t('invalid_email'));
                    return false;
                }
            }
            if (rules.type === 'url') {
                try { new URL(value); } catch {
                    showFieldError(field, fieldId + '-error', t('invalid_url'));
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
        if (errorEl) { errorEl.textContent = message; errorEl.style.display = 'block'; }
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
                field.addEventListener('input', () => { if (field.classList.contains('is-invalid')) validateField(fieldId); });
            }
        });
    }

    // ==================== VALIDATION DES ÉTAPES ====================
    window.validateStep = function(step, nextStep) {
        let isValid = true;
        switch(step) {
            case 1: isValid = validateStep1(); break;
            case 2: isValid = validateStep2(); break;
            case 3: isValid = validateStep3(); break;
            case 4: isValid = validateStep4(); break;
            case 5: isValid = validateStep5(); break;
        }
        if (isValid) {
            goToStep(nextStep);
        } else {
            document.getElementById(`step-${step}`).classList.add('shake');
            setTimeout(() => document.getElementById(`step-${step}`).classList.remove('shake'), 500);
            showToast('error', t('validation_error'), t('validation_error_message'));
        }
    };

    function validateStep1() {
        let isValid = true;
        if (!validateField('full_name')) isValid = false;
        if (!validateField('firm_name')) isValid = false;
        if (!validateField('email')) isValid = false;
        if (!validateField('mobile_phone')) isValid = false;
        if (!validateField('whatsapp')) isValid = false;
        if (!validateField('corporate_website')) isValid = false;
        return isValid;
    }

    function validateStep2() {
        let isValid = true;
        if (!document.getElementById('pays_id').value) {
            document.getElementById('pays_search').classList.add('is-invalid');
            document.getElementById('pays-error').textContent = t('select_country');
            document.getElementById('pays-error').style.display = 'block';
            isValid = false;
        }
        return isValid;
    }

    function validateStep3() { return true; }

    function validateStep4() {
        let isValid = true;
        const capacityFields = ['capacity_investment_broker', 'capacity_placement_agent', 'capacity_corporate_finance_advisor', 'capacity_fund_manager', 'capacity_family_office_rep', 'capacity_esg_advisor', 'capacity_independent_introducer'];
        let hasCapacity = false;
        for (let field of capacityFields) {
            if (document.querySelector(`input[name="${field}"]`)?.checked) { hasCapacity = true; break; }
        }
        if (!hasCapacity) {
            document.querySelector('.capacity-error').style.display = 'block';
            isValid = false;
        } else {
            document.querySelector('.capacity-error').style.display = 'none';
        }
        return isValid;
    }

    function validateStep5() {
        let isValid = true;
        const checks = ['confirm_authorized', 'confirm_aml_kyc', 'acknowledge_no_exclusivity', 'understand_formal_mandate_required'];
        for (let check of checks) {
            const el = document.getElementById(check);
            if (!el.checked) {
                el.classList.add('is-invalid');
                document.getElementById(check + '-error').style.display = 'block';
                isValid = false;
            }
        }
        return isValid;
    }

    // ==================== NAVIGATION ====================
    window.goToStep = function(step) {
        if (step < 1 || step > totalSteps) return;
        document.querySelectorAll('.step').forEach(el => el.classList.remove('active', 'completed'));
        for (let i = 1; i < step; i++) document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
        document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
        document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
        document.getElementById(`step-${step}`).classList.add('active');
        currentStep = step;
        if (step === 5) generateSummary();
        document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth' });
    };

    // ==================== GÉNÉRATION DU RÉSUMÉ ====================
    function generateSummary() {
        const summary = document.getElementById('summary');
        const fullName = document.getElementById('full_name').value || '<?= t('not_provided') ?>';
        const email = document.getElementById('email').value || '<?= t('not_provided') ?>';
        const firmName = document.getElementById('firm_name').value || '<?= t('not_provided') ?>';
        const paysName = document.getElementById('pays_name').textContent || '<?= t('not_selected') ?>';
        const capacities = [];
        if (document.querySelector('input[name="capacity_investment_broker"]')?.checked) capacities.push('<?= t('cap_investment_broker') ?>');
        if (document.querySelector('input[name="capacity_placement_agent"]')?.checked) capacities.push('<?= t('cap_placement_agent') ?>');
        if (document.querySelector('input[name="capacity_corporate_finance_advisor"]')?.checked) capacities.push('<?= t('cap_corporate_finance') ?>');
        if (document.querySelector('input[name="capacity_fund_manager"]')?.checked) capacities.push('<?= t('cap_fund_manager') ?>');
        if (document.querySelector('input[name="capacity_family_office_rep"]')?.checked) capacities.push('<?= t('cap_family_office') ?>');
        if (document.querySelector('input[name="capacity_esg_advisor"]')?.checked) capacities.push('<?= t('cap_esg_advisor') ?>');
        if (document.querySelector('input[name="capacity_independent_introducer"]')?.checked) capacities.push('<?= t('cap_independent_introducer') ?>');
        const capacityOther = document.getElementById('capacity_other').value;
        if (capacityOther) capacities.push('<?= t('other_label') ?>: ' + capacityOther);
        const regulatoryStatus = document.getElementById('regulatory_status').value || '<?= t('not_specified') ?>';
        const engagementModel = document.getElementById('engagement_model').value || '<?= t('not_specified') ?>';
        let html = `
            <div class="summary-section"><h6><i class="fas fa-user-circle"></i> <?= t('identity_summary') ?></h6>
            <div class="summary-item"><span class="summary-label"><?= t('full_name') ?></span><span class="summary-value">${escapeHtml(fullName)}</span></div>
            <div class="summary-item"><span class="summary-label"><?= t('firm_name') ?></span><span class="summary-value">${escapeHtml(firmName)}</span></div>
            <div class="summary-item"><span class="summary-label"><?= t('email') ?></span><span class="summary-value">${escapeHtml(email)}</span></div></div>
            <div class="summary-section"><h6><i class="fas fa-map-marked-alt"></i> <?= t('location_summary') ?></h6>
            <div class="summary-item"><span class="summary-label"><?= t('country_label') ?></span><span class="summary-value">${escapeHtml(paysName)}</span></div></div>
            <div class="summary-section"><h6><i class="fas fa-shield-alt"></i> <?= t('regulation_summary') ?></h6>
            <div class="summary-item"><span class="summary-label"><?= t('regulatory_status_label') ?></span><span class="summary-value">${escapeHtml(regulatoryStatus)}</span></div></div>`;
        if (capacities.length > 0) html += `<div class="summary-section"><h6><i class="fas fa-briefcase"></i> <?= t('capacities_summary') ?></h6><div>${capacities.map(cap => `<span class="badge-custom">${escapeHtml(cap)}</span>`).join('')}</div></div>`;
        html += `<div class="summary-section"><h6><i class="fas fa-handshake"></i> <?= t('engagement_summary') ?></h6>
            <div class="summary-item"><span class="summary-label"><?= t('engagement_model_label') ?></span><span class="summary-value">${escapeHtml(engagementModel)}</span></div></div>`;
        summary.innerHTML = html;
    }

    function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }

    // ==================== NOTIFICATIONS TOAST ====================
    function showToast(type, title, message, duration = 5000) {
        let container = document.getElementById('toastContainer');
        if (!container) { container = document.createElement('div'); container.id = 'toastContainer'; container.className = 'toast-container'; document.body.appendChild(container); }
        const toast = document.createElement('div'); toast.className = `toast-custom ${type}`;
        const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        const colors = { success: '#27ae60', error: '#E74C3C', warning: '#FF6B35', info: '#3498DB' };
        toast.innerHTML = `<div class="toast-icon" style="background: ${colors[type]}20; color: ${colors[type]};"><i class="fas ${icons[type] || icons.info}"></i></div>
            <div class="toast-content"><h4>${escapeHtml(title)}</h4><p>${escapeHtml(message)}</p></div>
            <button class="toast-close" onclick="removeToast(this.parentElement)"><i class="fas fa-times"></i></button>`;
        container.appendChild(toast);
        const autoRemove = setTimeout(() => removeToast(toast), duration);
        toast.addEventListener('mouseenter', () => clearTimeout(autoRemove));
        toast.addEventListener('mouseleave', () => setTimeout(() => removeToast(toast), 1000));
    }

    function removeToast(toast) { if (!toast || !toast.parentElement) return; toast.classList.add('hiding'); setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300); }

    // ==================== SOUMISSION (CORRIGÉE) ====================
    document.getElementById('brokerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!validateStep5()) { 
            showToast('error', t('validation_error'), t('validation_error_message')); 
            return; 
        }
        
        const submitBtn = document.getElementById('submitBtn'); 
        submitBtn.classList.add('loading'); 
        submitBtn.disabled = true;
        
        const formData = new FormData(this); 
        const data = Object.fromEntries(formData.entries());
        
        // UTILISER LA ROUTE EXISTANTE : /{lang}/Brokers-form
        const submitUrl = '<?= base_url('Brokers-form') ?>';
        
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
                document.getElementById('successName').textContent = data.full_name.split(' ')[0] + ' !';
                const modal = new bootstrap.Modal(document.getElementById('successModal')); 
                modal.show();
                this.reset(); 
                resetForm(); 
                showToast('success', t('success_label'), result.message);
                setTimeout(() => { 
                    goToStep(1); 
                    modal.hide(); 
                }, 3000);
            } else {
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => { 
                        const el = document.getElementById(field); 
                        if (el) { 
                            el.classList.add('is-invalid'); 
                            const feedback = document.getElementById(field + '-error'); 
                            if (feedback) { 
                                feedback.textContent = result.errors[field]; 
                                feedback.style.display = 'block'; 
                            } 
                        } 
                    });
                }
                showToast('error', t('error_label'), result.message || '<?= t('error_occurred') ?>');
            }
        } catch (error) { 
            console.error('Error:', error); 
            showToast('error', t('error_label'), t('network_error')); 
        } finally { 
            submitBtn.classList.remove('loading'); 
            submitBtn.disabled = false; 
        }
    });

    function resetForm() {
        document.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        clearCountry('pays');
        document.querySelectorAll('.char-counter').forEach(el => { 
            const id = el.id.replace('-count', ''); 
            const field = document.getElementById(id); 
            if (field && field.maxLength) el.textContent = `0/${field.maxLength}`; 
        });
    }

    function closeSuccessModal() { 
        const modalEl = document.getElementById('successModal'); 
        const modal = bootstrap.Modal.getInstance(modalEl); 
        modal.hide(); 
    }
</script>

<?php include VIEWPATH . 'includes/frontend/Footer.php'; ?>